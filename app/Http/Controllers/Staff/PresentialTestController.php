<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\PresentielTest;
use App\Models\TestComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PresentialTestController extends Controller
{
    /**
     * Display a listing of the staff's tests.
     */
    public function index()
    {
        $staff = Auth::user()->staff;
        
        // Get upcoming tests
        $upcomingTests = PresentielTest::where('staff_id', $staff->id)
            ->where('date', '>=', now())
            ->where('status', 'scheduled')
            ->with('candidate.user')
            ->orderBy('date')
            ->get();
            
        // Get past tests
        $pastTests = PresentielTest::where('staff_id', $staff->id)
            ->where(function ($query) {
                $query->where('date', '<', now())
                    ->orWhere('status', '!=', 'scheduled');
            })
            ->with('candidate.user')
            ->orderBy('date', 'desc')
            ->get();
            
        // Group tests by type
        $groupedUpcoming = $upcomingTests->groupBy('test_type');
        $groupedPast = $pastTests->groupBy('test_type');
        
        return view('staff.tests.index', compact('groupedUpcoming', 'groupedPast'));
    }
    
    /**
     * Show the test details and form for adding comments.
     */
    public function show($id)
    {
        $staff = Auth::user()->staff;
        
        $test = PresentielTest::where('staff_id', $staff->id)
            ->with(['candidate.user', 'comments', 'group.session'])
            ->findOrFail($id);
            
        // For CME tests, get all candidates in the group
        $groupCandidates = null;
        if ($test->test_type === 'cme' && $test->group_id) {
            $groupCandidates = PresentielTest::where('group_id', $test->group_id)
                ->with('candidate.user')
                ->get();
        }
        
        return view('staff.tests.show', compact('test', 'groupCandidates'));
    }
    
    /**
     * Add a comment to the test.
     */
    public function addComment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string',
            'rating' => 'required|in:pass,fail,pending',
        ]);
        
        $staff = Auth::user()->staff;
        
        $test = PresentielTest::where('staff_id', $staff->id)
            ->findOrFail($id);
            
        // Check if a comment already exists
        $existingComment = TestComment::where('presentiel_test_id', $id)
            ->where('staff_id', $staff->id)
            ->first();
            
        if ($existingComment) {
            // Update existing comment
            $existingComment->update([
                'comment' => $request->comment,
                'rating' => $request->rating,
            ]);
            
            $message = 'Commentaire mis à jour avec succès';
        } else {
            // Create new comment
            TestComment::create([
                'presentiel_test_id' => $id,
                'staff_id' => $staff->id,
                'comment' => $request->comment,
                'rating' => $request->rating,
            ]);
            
            $message = 'Commentaire ajouté avec succès';
        }
        
        // Update test status if completed
        if ($request->has('mark_completed') && $request->mark_completed) {
            $test->update(['status' => 'completed']);
        }
        
        return redirect()->route('staff.tests.show', $id)
            ->with('success', $message);
    }
    
    /**
     * Show availability management page.
     */
    public function availability()
    {
        $staff = Auth::user()->staff;
        
        $availabilities = $staff->availabilities()
            ->where('date', '>=', now())
            ->orderBy('date')
            ->get();
            
        return view('staff.tests.availability', compact('availabilities'));
    }
    
    /**
     * Store a new availability.
     */
    public function storeAvailability(Request $request)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'time_slot' => 'required|in:morning,afternoon,full_day',
            'is_available' => 'sometimes|boolean',
        ]);
        
        $staff = Auth::user()->staff;
        
        // Check if availability already exists
        $existingAvailability = $staff->availabilities()
            ->where('date', $request->date)
            ->first();
            
        if ($existingAvailability) {
            // Update existing availability
            $existingAvailability->update([
                'time_slot' => $request->time_slot,
                'is_available' => $request->has('is_available'),
            ]);
            
            $message = 'Disponibilité mise à jour avec succès';
        } else {
            // Create new availability
            $staff->availabilities()->create([
                'date' => $request->date,
                'time_slot' => $request->time_slot,
                'is_available' => $request->has('is_available', true),
            ]);
            
            $message = 'Disponibilité ajoutée avec succès';
        }
        
        return redirect()->route('staff.tests.availability')
            ->with('success', $message);
    }
}