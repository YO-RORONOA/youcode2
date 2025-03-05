<?php

namespace App\Services;

use App\Models\Candidate;
use App\Models\Staff;
use App\Models\TestSession;
use App\Models\TestGroup;
use App\Models\PresentielTest;
use App\Models\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TestAssignmentService
{
    /**
     * Assign candidates to tests and staff members.
     *
     * @param Collection $candidates
     * @return array
     */
    public function assignCandidatesToTests(Collection $candidates)
    {
        $results = [
            'assigned' => [],
            'not_assigned' => [],
            'errors' => []
        ];
        
        // Wrappe tout dans une transaction
        DB::beginTransaction();
        
        try {
            // 1. Assigner les candidats aux tests CME (en groupes)
            $this->assignCMETests($candidates, $results);
            
            // 2. Assigner les tests techniques
            $this->assignTechnicalTests($candidates, $results);
            
            // 3. Assigner les tests administratifs
            $this->assignAdministrativeTests($candidates, $results);
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $results['errors'][] = $e->getMessage();
        }
        
        return $results;
    }
    
    /**
     * Assign CME tests in groups.
     */
    private function assignCMETests(Collection $candidates, array &$results)
    {
        // Trouver ou créer des sessions de test
        $sessions = $this->findOrCreateTestSessions();
        
        // Trouver les staffs CME disponibles
        $cmeStaff = Staff::whereHas('user.roles', function ($query) {
            $query->where('name', 'CME');
        })->get();
        
        // S'assurer qu'il y a suffisamment de staff CME
        if ($cmeStaff->count() < 1) {
            $results['errors'][] = "Pas assez de staff CME disponible";
            return;
        }
        
        // Assigner les candidats aux groupes
        foreach ($candidates as $candidate) {
            // Vérifier si le candidat est déjà assigné à un test CME
            $existingTest = PresentielTest::where('candidate_id', $candidate->id)
                ->where('test_type', 'cme')
                ->first();
                
            if ($existingTest) {
                continue; // Candidat déjà assigné
            }
            
            // Trouver un groupe non complet
            $group = null;
            foreach ($sessions as $session) {
                $availableGroups = $session->groups()
                    ->get()
                    ->filter(function ($g) {
                        return !$g->isAtCapacity();
                    });
                
                if ($availableGroups->count() > 0) {
                    $group = $availableGroups->first();
                    break;
                } else if (!$session->isAtCapacity()) {
                    // Créer un nouveau groupe si la session n'est pas complète
                    $group = TestGroup::create([
                        'name' => 'Groupe ' . ($session->groups()->count() + 1),
                        'session_id' => $session->id,
                        'capacity' => 4
                    ]);
                    break;
                }
            }
            
            if (!$group) {
                $results['not_assigned'][] = [
                    'candidate' => $candidate,
                    'reason' => 'Pas de groupe disponible pour le test CME'
                ];
                continue;
            }
            
            // Assigner le staff
            $staffMember = $cmeStaff->random();
            
            // Créer le test présentiel
            $test = PresentielTest::create([
                'candidate_id' => $candidate->id,
                'staff_id' => $staffMember->id,
                'group_id' => $group->id,
                'date' => $group->session->date,
                'location' => $group->session->location,
                'test_type' => 'cme',
                'status' => 'scheduled'
            ]);
            
            // Envoyer une notification
            $this->sendTestNotification($candidate, $staffMember, $test);
            
            $results['assigned'][] = [
                'candidate' => $candidate,
                'test' => $test,
                'type' => 'cme'
            ];
        }
    }
    
    /**
     * Assign technical tests.
     */
    private function assignTechnicalTests(Collection $candidates, array &$results)
    {
        // Trouver les staffs techniques disponibles
        $technicalStaff = Staff::whereHas('user.roles', function ($query) {
            $query->where('name', 'Coach');
        })->get();
        
        // S'assurer qu'il y a suffisamment de staff technique
        if ($technicalStaff->count() < 1) {
            $results['errors'][] = "Pas assez de staff technique disponible";
            return;
        }
        
        foreach ($candidates as $candidate) {
            // Vérifier si le candidat est déjà assigné à un test technique
            $existingTest = PresentielTest::where('candidate_id', $candidate->id)
                ->where('test_type', 'technical')
                ->first();
                
            if ($existingTest) {
                continue; // Candidat déjà assigné
            }
            
            // Trouver le staff avec le moins de tests assignés
            $staffMember = $technicalStaff->sortBy(function ($staff) {
                return $staff->presentielTests()
                    ->where('test_type', 'technical')
                    ->where('date', '>=', now())
                    ->count();
            })->first();
            
            // Trouver une plage horaire disponible
            $dateTime = $this->findAvailableTimeSlot($staffMember, 20); // 20 minutes pour un test technique
            
            if (!$dateTime) {
                $results['not_assigned'][] = [
                    'candidate' => $candidate,
                    'reason' => 'Pas de créneau disponible pour le test technique'
                ];
                continue;
            }
            
            // Créer le test présentiel
            $test = PresentielTest::create([
                'candidate_id' => $candidate->id,
                'staff_id' => $staffMember->id,
                'date' => $dateTime,
                'location' => 'Salle Technique',
                'test_type' => 'technical',
                'status' => 'scheduled'
            ]);
            
            // Envoyer une notification
            $this->sendTestNotification($candidate, $staffMember, $test);
            
            $results['assigned'][] = [
                'candidate' => $candidate,
                'test' => $test,
                'type' => 'technical'
            ];
        }
    }
    
    /**
     * Assign administrative tests.
     */
    private function assignAdministrativeTests(Collection $candidates, array &$results)
    {
        // Trouver les staffs administratifs disponibles
        $adminStaff = Staff::whereHas('user.roles', function ($query) {
            $query->where('name', 'administrative');
        })->get();
        
        // S'assurer qu'il y a suffisamment de staff administratif
        if ($adminStaff->count() < 1) {
            $results['errors'][] = "Pas assez de staff administratif disponible";
            return;
        }
        
        foreach ($candidates as $candidate) {
            // Vérifier si le candidat est déjà assigné à un test administratif
            $existingTest = PresentielTest::where('candidate_id', $candidate->id)
                ->where('test_type', 'administrative')
                ->first();
                
            if ($existingTest) {
                continue; // Candidat déjà assigné
            }
            
            // Trouver le staff avec le moins de tests assignés
            $staffMember = $adminStaff->sortBy(function ($staff) {
                return $staff->presentielTests()
                    ->where('test_type', 'administrative')
                    ->where('date', '>=', now())
                    ->count();
            })->first();
            
            // Trouver une plage horaire disponible
            $dateTime = $this->findAvailableTimeSlot($staffMember, 15); // 15 minutes pour un test administratif
            
            if (!$dateTime) {
                $results['not_assigned'][] = [
                    'candidate' => $candidate,
                    'reason' => 'Pas de créneau disponible pour le test administratif'
                ];
                continue;
            }
            
            // Créer le test présentiel
            $test = PresentielTest::create([
                'candidate_id' => $candidate->id,
                'staff_id' => $staffMember->id,
                'date' => $dateTime,
                'location' => 'Bureau Administratif',
                'test_type' => 'administrative',
                'status' => 'scheduled'
            ]);
            
            // Envoyer une notification
            $this->sendTestNotification($candidate, $staffMember, $test);
            
            $results['assigned'][] = [
                'candidate' => $candidate,
                'test' => $test,
                'type' => 'administrative'
            ];
        }
    }
    
    /**
     * Find or create test sessions for group tests (CME).
     */
    private function findOrCreateTestSessions()
    {
        $startDate = now()->addDays(1);
        $endDate = now()->addDays(14);
        
        // Chercher des sessions existantes
        $existingSessions = TestSession::whereBetween('date', [$startDate, $endDate])
            ->where('status', 'scheduled')
            ->get();
            
        if ($existingSessions->count() > 0) {
            return $existingSessions;
        }
        
        // Créer de nouvelles sessions
        $sessions = collect();
        
        // Créer 3 jours de sessions (par exemple)
        for ($i = 0; $i < 3; $i++) {
            $date = $startDate->copy()->addDays($i);
            
            // Ne pas planifier pendant les week-ends
            if ($date->isWeekend()) {
                continue;
            }
            
            // Session du matin
            $morningSession = TestSession::create([
                'date' => $date,
                'time_slot' => 'morning',
                'location' => 'Salle de conférence',
                'status' => 'scheduled'
            ]);
            
            $sessions->push($morningSession);
            
            // Session de l'après-midi
            $afternoonSession = TestSession::create([
                'date' => $date,
                'time_slot' => 'afternoon',
                'location' => 'Salle de conférence',
                'status' => 'scheduled'
            ]);
            
            $sessions->push($afternoonSession);
        }
        
        return $sessions;
    }
    