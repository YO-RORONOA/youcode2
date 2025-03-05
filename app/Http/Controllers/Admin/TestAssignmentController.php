<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\PresentielTest;
use App\Services\TestAssignmentService;
use Illuminate\Http\Request;

class TestAssignmentController extends Controller
{
    protected $assignmentService;
    
    public function __construct(TestAssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }
    
    /**
     * Show the assignment dashboard.
     */
    public function index()
    {
        // Candidates ready for assignment (passed quiz, no tests assigned yet)
        $eligibleCandidates = Candidate::where('status', 'quiz_passed')
            ->whereDoesntHave('presentielTests')
            ->with('user')
            ->get();
            
        // Candidates with some tests assigned
        $partiallyAssignedCandidates = Candidate::where('status', 'quiz_passed')
            ->whereHas('presentielTests')
            ->with(['user', 'presentielTests'])
            ->get()
            ->filter(function ($candidate) {
                $testTypes = $candidate->presentielTests->pluck('test_type')->toArray();
                return count(array_unique($testTypes)) < 3; // Less than all 3 test types
            });
            
        // Candidates with all tests assigned
        $fullyAssignedCandidates = Candidate::where('status', 'quiz_passed')
            ->whereHas('presentielTests')
            ->with(['user', 'presentielTests'])
            ->get()
            ->filter(function ($candidate) {
                $testTypes = $candidate->presentielTests->pluck('test_type')->toArray();
                return count(array_unique($testTypes)) >= 3; // All 3 test types
            });
            
        return view('admin.tests.assignment.index', compact(
            'eligibleCandidates',
            'partiallyAssignedCandidates',
            'fullyAssignedCandidates'
        ));
    }
    
    /**
     * Run the automatic assignment algorithm.
     */
    public function runAutoAssignment(Request $request)
    {
        // Validate the request
        $request->validate([
            'candidate_ids' => 'required|array',
            'candidate_ids.*' => 'exists:candidates,id',
        ]);
        
        // Get the selected candidates
        $candidates = Candidate::whereIn('id', $request->candidate_ids)
            ->with('user')
            ->get();
            
        // Run the assignment algorithm
        $results = $this->assignmentService->assignCandidatesToTests($candidates);
        
        // Store results in session for display
        session()->flash('assignment_results', $results);
        
        return redirect()->route('admin.tests.assignment.results');
    }
    
    /**
     * Show assignment results.
     */
    public function showResults()
    {
        if (!session()->has('assignment_results')) {
            return redirect()->route('admin.tests.assignment.index')
                ->with('error', 'Aucun résultat d\'assignation disponible');
        }
        
        $results = session('assignment_results');
        
        return view('admin.tests.assignment.results', compact('results'));
    }
    
    /**
     * Show the individual test details.
     */
    public function showTest($id)
    {
        $test = PresentielTest::with(['candidate.user', 'staff.user', 'group.session', 'comments'])
            ->findOrFail($id);
            
        return view('admin.tests.assignment.test', compact('test'));
    }
    
    /**
     * Update a specific test.
     */
    public function updateTest(Request $request, $id)
    {
        $request->validate([
            'staff_id' => 'sometimes|required|exists:staff,id',
            'date' => 'sometimes|required|date',
            'location' => 'sometimes|required|string',
            'status' => 'sometimes|required|in:scheduled,completed,cancelled',
        ]);
        
        $test = PresentielTest::findOrFail($id);
        
        $test->update($request->only([
            'staff_id',
            'date',
            'location',
            'status',
        ]));
        
        return redirect()->route('admin.tests.assignment.test', $id)
            ->with('success', 'Test mis à jour avec succès');
    }
}