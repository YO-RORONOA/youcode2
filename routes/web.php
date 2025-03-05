<?php
// routes/web.php

use App\Http\Controllers\Admin\TestAssignmentController;
use App\Http\Controllers\Admin\TestGroupController;
use App\Http\Controllers\Admin\TestSessionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\Staff\PresentialTestController;
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
    Route::get('/tests', [AdminController::class, 'presentielTests'])->name('tests.index');
    Route::get('/tests/schedule', [AdminController::class, 'scheduleTest'])->name('tests.schedule');
    Route::post('/tests', [AdminController::class, 'storeTest'])->name('tests.store');
    
    // Gestion des utilisateurs
    Route::get('/users', [AdminController::class, 'users'])->name('users');
});

// Routes pour le staff (CME, Coach)
Route::middleware(['auth', 'role:CME,Coach'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', [StaffController::class, 'dashboard'])->name('dashboard');
    
    // Gestion des tests
    Route::get('/tests', [StaffController::class, 'tests'])->name('tests.index');
    Route::get('/tests/{id}', [StaffController::class, 'viewTest'])->name('tests.view');
    Route::put('/tests/{id}/status', [StaffController::class, 'updateTestStatus'])->name('tests.update');
    
    // Gestion des disponibilités
    Route::get('/availabilities', [StaffController::class, 'availabilities'])->name('availabilities');
    Route::post('/availabilities', [StaffController::class, 'storeAvailability'])->name('availabilities.store');
    Route::delete('/availabilities/{id}', [StaffController::class, 'deleteAvailability'])->name('availabilities.delete');
});


// Routes pour l'administration des tests
Route::middleware(['auth', 'role:Admin'])->prefix('admin')->name('admin.')->group(function () {
    // ... routes existantes ...
    
    // Routes pour les sessions de test
    Route::prefix('tests')->name('tests.')->group(function () {
        // Sessions
        Route::resource('sessions', TestSessionController::class);
        
        // Groupes (imbriqués avec sessions)
        Route::prefix('sessions/{session}/groups')->name('groups.')->group(function () {
            Route::get('/', [TestGroupController::class, 'index'])->name('index');
            Route::get('/create', [TestGroupController::class, 'create'])->name('create');
            Route::post('/', [TestGroupController::class, 'store'])->name('store');
            Route::get('/{group}', [TestGroupController::class, 'show'])->name('show');
            Route::get('/{group}/edit', [TestGroupController::class, 'edit'])->name('edit');
            Route::put('/{group}', [TestGroupController::class, 'update'])->name('update');
            Route::delete('/{group}', [TestGroupController::class, 'destroy'])->name('destroy');
            Route::post('/{group}/candidates', [TestGroupController::class, 'addCandidate'])->name('add-candidate');
            Route::delete('/{group}/candidates/{test}', [TestGroupController::class, 'removeCandidate'])->name('remove-candidate');
        });
        
        // Assignation automatique
        Route::get('/assignment', [TestAssignmentController::class, 'index'])->name('assignment.index');
        Route::post('/assignment/run', [TestAssignmentController::class, 'runAutoAssignment'])->name('assignment.run');
        Route::get('/assignment/results', [TestAssignmentController::class, 'showResults'])->name('assignment.results');
        Route::get('/assignment/test/{id}', [TestAssignmentController::class, 'showTest'])->name('assignment.test');
        Route::put('/assignment/test/{id}', [TestAssignmentController::class, 'updateTest'])->name('assignment.test.update');
    });
});

// Routes pour le staff (CME, Coach, Administratif)
Route::middleware(['auth', 'role:CME,Coach,administrative'])->prefix('staff')->name('staff.')->group(function () {
    // ... routes existantes ...
    
    // Tests présentiels
    Route::get('/tests', [PresentialTestController::class, 'index'])->name('tests.index');
    Route::get('/tests/{id}', [PresentialTestController::class, 'show'])->name('tests.show');
    Route::post('/tests/{id}/comment', [PresentialTestController::class, 'addComment'])->name('tests.comment');
    
    // Gestion des disponibilités
    Route::get('/availability', [PresentialTestController::class, 'availability'])->name('tests.availability');
    Route::post('/availability', [PresentialTestController::class, 'storeAvailability'])->name('tests.availability.store');
});