<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Document;
use App\Models\Quiz;
use App\Models\Staff;
use App\Models\PresentielTest;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'totalCandidates' => Candidate::count(),
            'pendingDocuments' => Document::where('is_verified', false)->count(),
            'quizzesPassed' => Candidate::where('status', 'quiz_passed')->count(),
            'upcomingTests' => PresentielTest::where('status', 'scheduled')
                ->where('date', '>', now())
                ->count(),
        ];
        
        return view('admin.dashboard', compact('stats'));
    }
    
    public function candidates()
    {
        $candidates = Candidate::with('user', 'documents')->get();
        
        return view('admin.candidates.index', compact('candidates'));
    }
    
    public function viewCandidate($id)
    {
        $candidate = Candidate::with([
            'user',
            'documents',
            'quizAttempts.quiz',
            'presentielTests.staff.user'
        ])->findOrFail($id);
        
        return view('admin.candidates.view', compact('candidate'));
    }
    
    public function documents()
    {
        $documents = Document::with('candidate.user')
            ->where('is_verified', false)
            ->get();
        
        return view('admin.documents.index', compact('documents'));
    }
    
    public function verifyDocument(Request $request, $id)
    {
        $request->validate([
            'verification_status' => 'required|boolean',
            'notes' => 'nullable|string',
        ]);
        
        $document = Document::findOrFail($id);
        
        $document->update([
            'is_verified' => $request->verification_status,
            'verification_notes' => $request->notes,
        ]);
        
        // Vérifier si tous les documents du candidat sont vérifiés
        $candidate = $document->candidate;
        $allVerified = !$candidate->documents()->where('is_verified', false)->exists();
        
        if ($allVerified) {
            $candidate->update(['status' => 'documents_verified']);
        }
        
        return redirect()->back()->with('success', 'Document vérifié avec succès');
    }
    
    public function quizzes()
    {
        $quizzes = Quiz::withCount('questions')->get();
        
        return view('admin.quizzes.index', compact('quizzes'));
    }
    
    public function createQuiz()
    {
        return view('admin.quizzes.create');
    }
    
    public function storeQuiz(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_limit' => 'required|integer|min:1',
            'passing_score' => 'required|integer|min:1',
        ]);
        
        $quiz = Quiz::create([
            'title' => $request->title,
            'description' => $request->description,
            'time_limit' => $request->time_limit,
            'passing_score' => $request->passing_score,
            'is_active' => $request->has('is_active'),
        ]);
        
        return redirect()->route('admin.quizzes.edit', $quiz->id)
            ->with('success', 'Quiz créé avec succès. Vous pouvez maintenant ajouter des questions.');
    }
    
    public function editQuiz($id)
    {
        $quiz = Quiz::with('questions.options')->findOrFail($id);
        
        return view('admin.quizzes.edit', compact('quiz'));
    }
    
    public function updateQuiz(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_limit' => 'required|integer|min:1',
            'passing_score' => 'required|integer|min:1',
        ]);
        
        $quiz = Quiz::findOrFail($id);
        
        $quiz->update([
            'title' => $request->title,
            'description' => $request->description,
            'time_limit' => $request->time_limit,
            'passing_score' => $request->passing_score,
            'is_active' => $request->has('is_active'),
        ]);
        
        return redirect()->route('admin.quizzes.index')
            ->with('success', 'Quiz mis à jour avec succès');
    }
    
    public function presentielTests()
    {
        $tests = PresentielTest::with(['candidate.user', 'staff.user'])
            ->orderBy('date')
            ->get();
        
        return view('admin.tests.index', compact('tests'));
    }
    
    public function scheduleTest()
    {
        $candidates = Candidate::whereHas('quizAttempts', function($query) {
            $query->where('status', 'passed');
        })->get();
        
        $staff = Staff::with('user')->get();
        
        return view('admin.tests.schedule', compact('candidates', 'staff'));
    }
    
    public function storeTest(Request $request)
    {
        $request->validate([
            'candidate_id' => 'required|exists:candidates,id',
            'staff_id' => 'required|exists:staff,id',
            'date' => 'required|date|after:today',
            'location' => 'required|string',
            'notes' => 'nullable|string',
        ]);
        
        // Vérifier que le staff est disponible à cette date
        $staff = Staff::find($request->staff_id);
        $testDate = new \DateTime($request->date);
        
        if (!$staff->checkAvailability($testDate)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Le staff sélectionné n\'est pas disponible à cette date');
        }
        
        $test = PresentielTest::create([
            'candidate_id' => $request->candidate_id,
            'staff_id' => $request->staff_id,
            'date' => $request->date,
            'location' => $request->location,
            'notes' => $request->notes,
            'status' => 'scheduled',
        ]);
        
        // Envoyer une notification au candidat
        $candidate = Candidate::find($request->candidate_id);
        $user = $candidate->user;
        
        $user->notifications()->create([
            'type' => 'test_scheduled',
            'content' => 'Un test présentiel a été programmé pour vous le ' . $test->date->format('d/m/Y à H:i'),
            'data' => [
                'test_id' => $test->id,
                'date' => $test->date->format('Y-m-d H:i:s'),
                'location' => $test->location,
            ],
        ]);
        
        return redirect()->route('admin.tests.index')
            ->with('success', 'Test présentiel programmé avec succès');
    }
    
    public function users()
    {
        $users = User::with('roles')->get();
        
        return view('admin.users.index', compact('users'));
    }
}
