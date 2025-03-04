<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Document;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CandidateController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $candidate = $user->candidate;
        
        // if (!$candidate) {
        //     return redirect()->route('candidate.profile.edit')
        //         ->with('warning', 'Veuillez compléter votre profil.');
        // }
        
        $documents = $candidate->documents;
        $quizAttempts = $candidate->quizAttempts;
        $presentielTests = $candidate->presentielTests;
        
        return view('candidate.dashboard', compact(
            'candidate', 
            'documents', 
            'quizAttempts', 
            'presentielTests'
        ));
    }
    
    public function editProfile()
    {
        $user = Auth::user();
        $candidate = $user->candidate;
        
        return view('candidate.profile.edit', compact('candidate', 'user'));
    }
    
    public function updateProfile(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'birth_date' => 'required|date',
        'phone' => 'required|string|max:20',
        'address' => 'required|string',
    ]);
    
    $user = Auth::user();
    
    // Mise à jour de l'utilisateur
    User::where('id', $user->id)->update([
        'name' => $request->name
    ]);
    
    // Création ou mise à jour du profil candidat
    if (!$user->candidate) {
        Candidate::create([
            'user_id' => $user->id,
            'birth_date' => $request->birth_date,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);
    } else {
        Candidate::where('user_id', $user->id)->update([
            'birth_date' => $request->birth_date,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);
    }
    
    return redirect()->route('candidate.dashboard')
        ->with('success', 'Profil mis à jour avec succès');
}
    
    public function documents()
    {
        $candidate = Auth::user()->candidate;
        $documents = $candidate ? $candidate->documents : collect();
        
        return view('candidate.documents.index', compact('documents'));
    }
    
    public function uploadDocument(Request $request)
    {
        $request->validate([
            'type' => 'required|in:id_card,cv,diploma,other',
            'file' => 'required|file|mimes:jpeg,png,pdf|max:2048',
        ]);
        
        $candidate = Auth::user()->candidate;
        
        if (!$candidate) {
            return redirect()->route('candidate.profile.edit')
                ->with('error', 'Veuillez compléter votre profil d\'abord');
        }
        
        // Vérifier si ce type de document existe déjà
        $existingDoc = $candidate->documents()->where('type', $request->type)->first();
        if ($existingDoc) {
            // Supprimer l'ancien fichier
            Storage::disk('public')->delete($existingDoc->file_path);
            $existingDoc->delete();
        }
        
        // Enregistrer le nouveau fichier
        $path = $request->file('file')->store('documents/' . $candidate->id, 'public');
        
        Document::create([
            'candidate_id' => $candidate->id,
            'type' => $request->type,
            'file_path' => $path,
            'is_verified' => false,
        ]);
        
        return redirect()->route('candidate.documents')
            ->with('success', 'Document téléchargé avec succès');
    }
    
    public function availableQuizzes()
    {
        $candidate = Auth::user()->candidate;
        
        if (!$candidate || !$candidate->hasSubmittedAllDocuments()) {
            return redirect()->route('candidate.documents')
                ->with('warning', 'Vous devez soumettre tous les documents requis avant de passer un quiz');
        }
        
        // Récupérer les quiz déjà passés
        $attemptedQuizIds = $candidate->quizAttempts->pluck('quiz_id')->toArray();
        
        // Récupérer les quiz disponibles
        $availableQuizzes = Quiz::where('is_active', true)
            ->whereNotIn('id', $attemptedQuizIds)
            ->get();
        
        return view('candidate.quizzes.available', compact('availableQuizzes'));
    }
    
    public function startQuiz($quizId)
    {
        $candidate = Auth::user()->candidate;
        $quiz = Quiz::findOrFail($quizId);
        
        // Vérifier si le candidat a déjà passé ce quiz
        if ($candidate->quizAttempts()->where('quiz_id', $quizId)->exists()) {
            return redirect()->route('candidate.quizzes.available')
                ->with('error', 'Vous avez déjà passé ce quiz');
        }
        
        // Créer une nouvelle tentative
        $attempt = QuizAttempt::create([
            'candidate_id' => $candidate->id,
            'quiz_id' => $quizId,
            'start_time' => now(),
            'status' => 'in_progress',
        ]);
        
        return redirect()->route('candidate.quizzes.take', $attempt->id);
    }
    
    public function takeQuiz($attemptId)
{
    $attempt = QuizAttempt::with(['quiz.questions.options'])->findOrFail($attemptId);
    $candidate = Auth::user()->candidate;
    
    // Vérifier que l'utilisateur est bien le candidat associé à cette tentative
    if ($attempt->candidate_id !== $candidate->id) {
        abort(403, 'Accès non autorisé');
    }
    
    // Vérifier si le quiz n'est pas déjà terminé
    if ($attempt->status !== 'in_progress') {
        return redirect()->route('candidate.quizzes.result', $attemptId);
    }
    
    // Vérifier si le temps est écoulé
    if ($attempt->remaining_time <= 0) {
        // Terminer automatiquement le quiz
        $this->finishQuiz($attempt);
        return redirect()->route('candidate.quizzes.result', $attemptId)
            ->with('warning', 'Le temps alloué pour ce quiz est écoulé');
    }
    
    return view('candidate.quizzes.take', compact('attempt'));
}

public function submitQuiz(Request $request, $attemptId)
{
    $attempt = QuizAttempt::findOrFail($attemptId);
    $candidate = Auth::user()->candidate;
    
    // Vérifications de sécurité
    if ($attempt->candidate_id !== $candidate->id || $attempt->status !== 'in_progress') {
        abort(403, 'Accès non autorisé');
    }
    
    // Vérifier si le temps est écoulé
    if ($attempt->remaining_time <= 0) {
        return $this->finishQuiz($attempt);
    }
    
    // Traiter les réponses
    $quiz = $attempt->quiz;
    $score = 0;
    
    foreach ($quiz->questions as $question) {
        $selectedOptionId = $request->input('question_' . $question->id);
        $isCorrect = false;
        
        if ($selectedOptionId) {
            $selectedOption = $question->options()->find($selectedOptionId);
            $isCorrect = $selectedOption && $selectedOption->is_correct;
            
            if ($isCorrect) {
                $score += $question->points;
            }
        }
        
        // Enregistrer la réponse
        $attempt->answers()->create([
            'question_id' => $question->id,
            'question_option_id' => $selectedOptionId,
            'is_correct' => $isCorrect,
        ]);
    }
    
    return $this->finishQuiz($attempt, $score);
}

private function finishQuiz($attempt, $score = null)
{
    // Si le score n'est pas fourni, calculer à partir des réponses enregistrées
    if ($score === null) {
        $score = $attempt->answers()->where('is_correct', true)
            ->join('questions', 'answers.question_id', '=', 'questions.id')
            ->sum('questions.points');
    }
    
    $quiz = $attempt->quiz;
    $status = $score >= $quiz->passing_score ? 'passed' : 'failed';
    
    $attempt->update([
        'end_time' => now(),
        'score' => $score,
        'status' => $status,
    ]);
    
    // Si le candidat a réussi, mettre à jour son statut
    if ($status === 'passed') {
        $attempt->candidate->update(['status' => 'quiz_passed']);
    }
    
    return redirect()->route('candidate.quizzes.result', $attempt->id);
}

public function quizResult($attemptId)
{
    $attempt = QuizAttempt::with(['quiz', 'answers.question.options', 'answers.selectedOption'])
        ->findOrFail($attemptId);
    $candidate = Auth::user()->candidate;
    
    if ($attempt->candidate_id !== $candidate->id) {
        abort(403, 'Accès non autorisé');
    }
    
    return view('candidate.quizzes.result', compact('attempt'));
}

public function viewTests()
{
    $candidate = Auth::user()->candidate;
    $presentielTests = $candidate->presentielTests;
    
    return view('candidate.tests.index', compact('presentielTests'));
}
}