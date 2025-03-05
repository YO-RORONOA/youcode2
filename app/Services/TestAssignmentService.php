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
    
    /**
     * Find an available time slot for a staff member.
     */
    private function findAvailableTimeSlot(Staff $staff, int $durationMinutes)
    {
        $startDate = now()->addDays(1);
        $endDate = now()->addDays(14);
        
        // Vérifier les disponibilités du staff
        $availabilities = $staff->availabilities()
            ->whereBetween('date', [$startDate, $endDate])
            ->where('is_available', true)
            ->get();
            
        if ($availabilities->count() === 0) {
            // Si aucune disponibilité n'est enregistrée, supposer que le staff est disponible
            // pendant les heures de bureau habituelles (9h-17h)
            for ($i = 0; $i < 10; $i++) {
                $date = $startDate->copy()->addDays($i);
                
                // Ne pas planifier pendant les week-ends
                if ($date->isWeekend()) {
                    continue;
                }
                
                // Heures de travail: 9h-12h, 14h-17h
                $workHours = [
                    ['start' => 9, 'end' => 12],
                    ['start' => 14, 'end' => 17]
                ];
                
                foreach ($workHours as $period) {
                    // Vérifier chaque créneau d'une heure
                    for ($hour = $period['start']; $hour < $period['end']; $hour++) {
                        // Pour chaque heure, vérifier les créneaux de 15 minutes
                        for ($minute = 0; $minute < 60; $minute += 15) {
                            $proposedDateTime = $date->copy()->setHour($hour)->setMinute($minute);
                            
                            // Vérifier si ce créneau est disponible
                            if ($this->isTimeSlotAvailable($staff, $proposedDateTime, $durationMinutes)) {
                                return $proposedDateTime;
                            }
                        }
                    }
                }
            }
        } else {
            // Utiliser les disponibilités enregistrées
            foreach ($availabilities as $availability) {
                $date = $availability->date;
                
                if ($availability->time_slot === 'morning' || $availability->time_slot === 'full_day') {
                    // Heures du matin: 9h-12h
                    for ($hour = 9; $hour < 12; $hour++) {
                        for ($minute = 0; $minute < 60; $minute += 15) {
                            $proposedDateTime = $date->copy()->setHour($hour)->setMinute($minute);
                            
                            if ($this->isTimeSlotAvailable($staff, $proposedDateTime, $durationMinutes)) {
                                return $proposedDateTime;
                            }
                        }
                    }
                }
                
                if ($availability->time_slot === 'afternoon' || $availability->time_slot === 'full_day') {
                    // Heures de l'après-midi: 14h-17h
                    for ($hour = 14; $hour < 17; $hour++) {
                        for ($minute = 0; $minute < 60; $minute += 15) {
                            $proposedDateTime = $date->copy()->setHour($hour)->setMinute($minute);
                            
                            if ($this->isTimeSlotAvailable($staff, $proposedDateTime, $durationMinutes)) {
                                return $proposedDateTime;
                            }
                        }
                    }
                }
            }
        }
        
        return null; // Aucun créneau disponible
    }
    
    /**
     * Check if a time slot is available for a staff member.
     */
    private function isTimeSlotAvailable(Staff $staff, $dateTime, int $durationMinutes)
    {
        // Vérifier si le staff a déjà un test planifié qui chevauche ce créneau
        $startDateTime = $dateTime->copy();
        $endDateTime = $dateTime->copy()->addMinutes($durationMinutes);
        
        $overlappingTests = PresentielTest::where('staff_id', $staff->id)
            ->where(function ($query) use ($startDateTime, $endDateTime) {
                $query->where(function ($q) use ($startDateTime, $endDateTime) {
                    // Test commence pendant notre créneau
                    $q->where('date', '>=', $startDateTime)
                      ->where('date', '<', $endDateTime);
                })->orWhere(function ($q) use ($startDateTime, $endDateTime) {
                    // Test se termine pendant notre créneau
                    $q->where('date', '<=', $startDateTime)
                      ->where(DB::raw("DATE_ADD(date, INTERVAL test_duration MINUTE)"), '>', $startDateTime);
                });
            })
            ->count();
            
        return $overlappingTests === 0;
    }
    
    /**
     * Send a notification about the test assignment.
     */
    private function sendTestNotification(Candidate $candidate, Staff $staffMember, PresentielTest $test)
    {
        // Notifier le candidat
        $candidate->user->notifications()->create([
            'type' => 'test_scheduled',
            'content' => 'Un test ' . $this->getTestTypeName($test->test_type) . ' a été programmé pour vous le ' . 
                         $test->date->format('d/m/Y à H:i') . ' à ' . $test->location,
            'data' => [
                'test_id' => $test->id,
                'test_type' => $test->test_type,
                'date' => $test->date->format('Y-m-d H:i:s'),
                'location' => $test->location
            ],
            'is_read' => false
        ]);
        
        // Notifier le staff
        $staffMember->user->notifications()->create([
            'type' => 'test_assigned',
            'content' => 'Vous avez été assigné à un test ' . $this->getTestTypeName($test->test_type) . 
                         ' avec ' . $candidate->user->name . ' le ' . 
                         $test->date->format('d/m/Y à H:i') . ' à ' . $test->location,
            'data' => [
                'test_id' => $test->id,
                'test_type' => $test->test_type,
                'candidate_id' => $candidate->id,
                'candidate_name' => $candidate->user->name,
                'date' => $test->date->format('Y-m-d H:i:s'),
                'location' => $test->location
            ],
            'is_read' => false
        ]);
    }
    
    /**
     * Get a human-readable name for the test type.
     */
    private function getTestTypeName($testType)
    {
        switch ($testType) {
            case 'cme':
                return 'CME';
            case 'technical':
                return 'technique';
            case 'administrative':
                return 'administratif';
            default:
                return $testType;
        }
    }
}