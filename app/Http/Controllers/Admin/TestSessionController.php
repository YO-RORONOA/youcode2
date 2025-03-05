<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TestSession;
use App\Models\TestGroup;
use App\Models\PresentielTest;
use App\Models\Candidate;
use App\Models\Staff;
use Illuminate\Http\Request;

class TestSessionController extends Controller
{
    /**
     * Display a listing of the test sessions.
     */
    public function index()
    {
        $sessions = TestSession::with('groups')
            ->orderBy('date')
            ->get();
            
        return view('admin.tests.sessions.index', compact('sessions'));
    }
    
    /**
     * Show the form for creating a new test session.
     */
    public function create()
    {
        return view('admin.tests.sessions.create');
    }
    
    /**
     * Store a newly created test session.
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date|after:today',
            'time_slot' => 'required|in:morning,afternoon',
            'location' => 'required|string',
        ]);
        
        $session = TestSession::create([
            'date' => $request->date,
            'time_slot' => $request->time_slot,
            'location' => $request->location,
            'status' => 'scheduled',
        ]);
        
        return redirect()->route('admin.tests.sessions.show', $session->id)
            ->with('success', 'Session de test créée avec succès');
    }
    
    /**
     * Display the specified test session.
     */
    public function show($id)
    {
        $session = TestSession::with(['groups.tests.candidate.user', 'groups.tests.staff.user'])
            ->findOrFail($id);
            
        return view('admin.tests.sessions.show', compact('session'));
    }
    
    /**
     * Show the form for editing the specified test session.
     */
    public function edit($id)
    {
        $session = TestSession::findOrFail($id);
        
        return view('admin.tests.sessions.edit', compact('session'));
    }
    
    /**
     * Update the specified test session.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date',
            'time_slot' => 'required|in:morning,afternoon',
            'location' => 'required|string',
            'status' => 'required|in:scheduled,completed,cancelled',
        ]);
        
        $session = TestSession::findOrFail($id);
        
        $session->update([
            'date' => $request->date,
            'time_slot' => $request->time_slot,
            'location' => $request->location,
            'status' => $request->status,
        ]);
        
        return redirect()->route('admin.tests.sessions.show', $session->id)
            ->with('success', 'Session de test mise à jour avec succès');
    }
    
    /**
     * Remove the specified test session.
     */
    public function destroy($id)
    {
        $session = TestSession::findOrFail($id);
        
        // Vérifier s'il y a des tests associés
        $hasTests = PresentielTest::whereHas('group', function ($query) use ($id) {
            $query->where('session_id', $id);
        })->exists();
        
        if ($hasTests) {
            return redirect()->route('admin.tests.sessions.index')
                ->with('error', 'Impossible de supprimer cette session car elle contient des tests');
        }
        
        $session->delete();
        
        return redirect()->route('admin.tests.sessions.index')
            ->with('success', 'Session de test supprimée avec succès');
    }
}