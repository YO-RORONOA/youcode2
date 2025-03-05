<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TestGroup;
use App\Models\TestSession;
use App\Models\PresentielTest;
use App\Models\Candidate;
use App\Models\Staff;
use Illuminate\Http\Request;

class TestGroupController extends Controller
{
    /**
     * Display a listing of the test groups for a given session.
     */
    public function index($sessionId)
    {
        $session = TestSession::with('groups.tests.candidate.user')
            ->findOrFail($sessionId);
            
        return view('admin.tests.groups.index', compact('session'));
    }
    
    /**
     * Show the form for creating a new test group.
     */
    public function create($sessionId)
    {
        $session = TestSession::findOrFail($sessionId);
        
        return view('admin.tests.groups.create', compact('session'));
    }
    
    /**
     * Store a newly created test group.
     */
    public function store(Request $request, $sessionId)
    {
        $request->validate([
            'name' => 'required|string',
            'capacity' => 'required|integer|min:1|max:10',
        ]);
        
        $session = TestSession::findOrFail($sessionId);
        
        $group = TestGroup::create([
            'name' => $request->name,
            'session_id' => $session->id,
            'capacity' => $request->capacity,
        ]);
        
        return redirect()->route('admin.tests.groups.show', [$sessionId, $group->id])
            ->with('success', 'Groupe de test créé avec succès');
    }
    
    /**
     * Display the specified test group.
     */
    public function show($sessionId, $id)
    {
        $session = TestSession::findOrFail($sessionId);
        $group = TestGroup::with(['tests.candidate.user', 'tests.staff.user'])
            ->where('session_id', $sessionId)
            ->findOrFail($id);
            
        // Obtenir les candidats qui ne sont pas encore assignés à un groupe
        $unassignedCandidates = Candidate::whereDoesntHave('presentielTests', function ($query) use ($sessionId) {
            $query->where('test_type', 'cme')
                ->whereHas('group', function ($q) use ($sessionId) {
                    $q->where('session_id', $sessionId);
                });
        })->get();
        
        // Obtenir les staffs CME disponibles
        $cmeStaff = Staff::whereHas('user.roles', function ($query) {
            $query->where('name', 'CME');
        })->get();
        
        return view('admin.tests.groups.show', compact('session', 'group', 'unassignedCandidates', 'cmeStaff'));
    }
    
    /**
     * Show the form for editing the specified test group.
     */
    public function edit($sessionId, $id)
    {
        $session = TestSession::findOrFail($sessionId);
        $group = TestGroup::where('session_id', $sessionId)
            ->findOrFail($id);
            
        return view('admin.tests.groups.edit', compact('session', 'group'));
    }
    
    /**
     * Update the specified test group.
     */
    public function update(Request $request, $sessionId, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'capacity' => 'required|integer|min:1|max:10',
        ]);
        
        $group = TestGroup::where('session_id', $sessionId)
            ->findOrFail($id);
            
        $group->update([
            'name' => $request->name,
            'capacity' => $request->capacity,
        ]);
        
        return redirect()->route('admin.tests.groups.show', [$sessionId, $id])
            ->with('success', 'Groupe de test mis à jour avec succès');
    }
    
    /**
     * Remove the specified test group.
     */
    public function destroy($sessionId, $id)
    {
        $group = TestGroup::where('session_id', $sessionId)
            ->findOrFail($id);
            
        // Vérifier s'il y a des tests associés
        if ($group->tests()->count() > 0) {
            return redirect()->route('admin.tests.groups.show', [$sessionId, $id])
                ->with('error', 'Impossible de supprimer ce groupe car il contient des tests');
        }
        
        $group->delete();
        
        return redirect()->route('admin.tests.sessions.show', $sessionId)
            ->with('success', 'Groupe de test supprimé avec succès');
    }
    
    /**
     * Add a candidate to the group.
     */
    public function addCandidate(Request $request, $sessionId, $id)
    {
        $request->validate([
            'candidate_id' => 'required|exists:candidates,id',
            'staff_id' => 'required|exists:staff,id',
        ]);
        
        $group = TestGroup::where('session_id', $sessionId)
            ->findOrFail($id);
            
        // Vérifier la capacité du groupe
        if ($group->tests()->count() >= $group->capacity) {
            return redirect()->route('admin.tests.groups.show', [$sessionId, $id])
                ->with('error', 'Ce groupe a atteint sa capacité maximale');
        }
        
        // Vérifier si le candidat est déjà assigné à un test CME
        $existingTest = PresentielTest::where('candidate_id', $request->candidate_id)
            ->where('test_type', 'cme')
            ->exists();
            
        if ($existingTest) {
            return redirect()->route('admin.tests.groups.show', [$sessionId, $id])
                ->with('error', 'Ce candidat est déjà assigné à un test CME');
        }
        
        // Créer le test
        $session = TestSession::findOrFail($sessionId);
        
        PresentielTest::create([
            'candidate_id' => $request->candidate_id,
            'staff_id' => $request->staff_id,
            'group_id' => $group->id,
            'date' => $session->date,
            'location' => $session->location,
            'test_type' => 'cme',
            'status' => 'scheduled',
        ]);
        
        return redirect()->route('admin.tests.groups.show', [$sessionId, $id])
            ->with('success', 'Candidat ajouté au groupe avec succès');
    }
    
    /**
     * Remove a candidate from the group.
     */
    public function removeCandidate($sessionId, $id, $testId)
    {
        $test = PresentielTest::where('group_id', $id)
            ->findOrFail($testId);
            
        $test->delete();
        
        return redirect()->route('admin.tests.groups.show', [$sessionId, $id])
            ->with('success', 'Candidat retiré du groupe avec succès');
    }
}