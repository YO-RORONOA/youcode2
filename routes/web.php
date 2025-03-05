<?php
// routes/web.php
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\QuestionController;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

// Routes publiques
Route::get('/', [HomeController::class, 'index'])->name('home');

// Routes d'authentification (fournies par Laravel Breeze)
require __DIR__.'/auth.php';

Route::get('/dashboard', function () {
    return View('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::delete('/profile', [CandidateController::class, 'destroy'])->name('profile.destroy');

// Routes pour les candidats
Route::middleware(['auth', 'verified'])->prefix('candidate')->name('candidate.')->group(function () {
    Route::get('/dashboard', [CandidateController::class, 'dashboard'])->name('dashboard');
    
    // Profil
    Route::get('/profile/edit', [CandidateController::class, 'editProfile'])->name('profile.edit');
    Route::put('/profile', [CandidateController::class, 'updateProfile'])->name('profile.update');
    
    // Documents
    Route::get('/documents', [CandidateController::class, 'documents'])->name('documents');
    Route::post('/documents', [CandidateController::class, 'uploadDocument'])->name('documents.upload');
    
    // Quiz
    Route::get('/quizzes', [CandidateController::class, 'availableQuizzes'])->name('quizzes.available');
    Route::post('/quizzes/{id}/start', [CandidateController::class, 'startQuiz'])->name('quizzes.start');
    Route::get('/quizzes/attempt/{id}', [CandidateController::class, 'takeQuiz'])->name('quizzes.take');
    Route::post('/quizzes/attempt/{id}', [CandidateController::class, 'submitQuiz'])->name('quizzes.submit');
    Route::get('/quizzes/result/{id}', [CandidateController::class, 'quizResult'])->name('quizzes.result');
    
    // Tests présentiels
    Route::get('/tests', [CandidateController::class, 'viewTests'])->name('tests');
});

// Routes pour l'administration
Route::middleware(['auth', 'role:Admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Gestion des candidats
    Route::get('/candidates', [AdminController::class, 'candidates'])->name('candidates');
    Route::get('/candidates/{id}', [AdminController::class, 'viewCandidate'])->name('candidates.view');
    
    // Vérification des documents
    Route::get('/documents', [AdminController::class, 'documents'])->name('documents');
    Route::post('/documents/{id}/verify', [AdminController::class, 'verifyDocument'])->name('documents.verify');
    
    // Gestion des quiz
    Route::get('/quizzes', [AdminController::class, 'quizzes'])->name('quizzes.index');
    Route::get('/quizzes/create', [AdminController::class, 'createQuiz'])->name('quizzes.create');
    Route::post('/quizzes', [AdminController::class, 'storeQuiz'])->name('quizzes.store');
    Route::get('/quizzes/{id}/edit', [AdminController::class, 'editQuiz'])->name('quizzes.edit');
    Route::put('/quizzes/{id}', [AdminController::class, 'updateQuiz'])->name('quizzes.update');
    
    // Gestion des questions
    Route::post('/quizzes/{id}/questions', [QuestionController::class, 'store'])->name('questions.store');
    Route::get('/questions/{id}/edit', [QuestionController::class, 'edit'])->name('questions.edit');
    Route::put('/questions/{id}', [QuestionController::class, 'update'])->name('questions.update');
    Route::delete('/questions/{id}', [QuestionController::class, 'destroy'])->name('questions.destroy');
    
    // Gestion des tests présentiels

    
    // Gestion des utilisateurs
    Route::get('/users', [AdminController::class, 'users'])->name('users');
});

