@extends('layouts.app')

@section('title', 'Résultats du Quiz')
@section('header', 'Résultats du Quiz')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <h2 class="text-xl font-semibold mb-2">{{ $attempt->quiz->title }}</h2>
    <div class="flex flex-wrap items-center text-sm text-gray-600 mb-4">
        <div class="mr-6 mb-2">
            <span class="font-medium">Date:</span> {{ $attempt->start_time->format('d/m/Y') }}
        </div>
        <div class="mr-6 mb-2">
            <span class="font-medium">Heure:</span> {{ $attempt->start_time->format('H:i') }} - {{ $attempt->end_time ? $attempt->end_time->format('H:i') : 'En cours' }}
        </div>
        <div class="mr-6 mb-2">
            <span class="font-medium">Durée:</span> 
            @if($attempt->end_time)
                {{ $attempt->start_time->diffInMinutes($attempt->end_time) }} minutes
            @else
                En cours
            @endif
        </div>
    </div>
    
    <div class="flex flex-wrap items-center justify-between">
        <div class="bg-gray-100 rounded-lg px-4 py-3 mb-2">
            <div class="text-sm text-gray-600">Votre score</div>
            <div class="text-2xl font-bold">
                {{ $attempt->score ?? 0 }} / {{ $attempt->quiz->questions->sum('points') }}
                <span class="text-lg font-normal text-gray-600 ml-1">({{ $attempt->score_percent ?? 0 }}%)</span>
            </div>
        </div>
        
        <div class="text-center mb-2">
            <div class="text-sm text-gray-600 mb-1">Statut</div>
            <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                @if($attempt->status === 'passed') bg-green-100 text-green-800
                @elseif($attempt->status === 'failed') bg-red-100 text-red-800
                @else bg-yellow-100 text-yellow-800 @endif">
                @if($attempt->status === 'passed')
                    Réussi
                @elseif($attempt->status === 'failed')
                    Non réussi
                @else
                    {{ ucfirst($attempt->status) }}
                @endif
            </div>
        </div>
        
        <div class="bg-gray-100 rounded-lg px-4 py-3 mb-2">
            <div class="text-sm text-gray-600">Score de passage</div>
            <div class="text-2xl font-bold">{{ $attempt->quiz->passing_score }}%</div>
        </div>
    </div>
</div>

<div class="mt-8">
    <h3 class="text-lg font-semibold mb-4">Détail des réponses</h3>
    
    @foreach($attempt->answers as $index => $answer)
    <div class="bg-white rounded-lg shadow-md p-6 mb-4">
        <div class="flex justify-between items-start">
            <h4 class="text-md font-semibold mb-3">Question {{ $index + 1 }}:</h4>
            <div class="ml-2">
                @if($answer->is_correct)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        Correct
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        Incorrect
                    </span>
                @endif
            </div>
        </div>
        
        <p class="text-gray-800 mb-4">{{ $answer->question->content }}</p>
        
        <div class="space-y-2">
            @foreach($answer->question->options as $option)
                <div class="flex items-start">
                    <div class="flex-shrink-0 h-5 w-5 mt-1">
                        @if($option->id === $answer->question_option_id)
                            <svg class="h-5 w-5 {{ $option->is_correct ? 'text-green-500' : 'text-red-500' }}" fill="currentColor" viewBox="0 0 20 20">
                                @if($option->is_correct)
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                @else
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                @endif
                            </svg>
                        @elseif($option->is_correct)
                            <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        @endif
                    </div>
                    <div class="ml-3 text-sm">
                        <span class="{{ $option->id === $answer->question_option_id ? 'font-semibold' : '' }}">
                            {{ $option->content }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endforeach
</div>

<div class="mt-8 flex justify-between">
    <a href="{{ route('candidate.quizzes.available') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
        Retour aux quiz disponibles
    </a>
    
    <a href="{{ route('candidate.dashboard') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
        Retour au tableau de bord
    </a>
</div>
@endsection