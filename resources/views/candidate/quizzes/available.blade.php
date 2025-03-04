@extends('layouts.app')

@section('title', 'Quiz disponibles')
@section('header', 'Quiz disponibles')

@section('content')
<div class="mb-6">
    <p class="text-gray-600">
        Les quiz suivants sont disponibles pour évaluer vos compétences. Une fois que vous avez commencé un quiz, vous devez le terminer dans le temps imparti.
    </p>
</div>

@if($availableQuizzes->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($availableQuizzes as $quiz)
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6">
                    <h3 class="text-lg font-semibold">{{ $quiz->title }}</h3>
                    <p class="text-gray-600 mt-2">{{ $quiz->description }}</p>
                    
                    <div class="mt-4 space-y-2">
                        <div class="flex items-center text-sm">
                            <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Durée: {{ $quiz->time_limit }} minutes</span>
                        </div>
                        
                        <div class="flex items-center text-sm">
                            <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Score de passage: {{ $quiz->passing_score }}</span>
                        </div>
                        
                        <div class="flex items-center text-sm">
                            <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>Questions: {{ $quiz->questions->count() }}</span>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <form action="{{ route('candidate.quizzes.start', $quiz->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Commencer le quiz
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            <h3 class="mt-2 text-lg font-medium text-gray-900">Aucun quiz disponible</h3>
            <p class="mt-1 text-sm text-gray-500">
                Vous avez déjà passé tous les quiz disponibles ou aucun quiz n'est actuellement disponible.
            </p>
        </div>
    </div>
@endif
@endsection