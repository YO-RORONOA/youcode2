@extends('layouts.app')

@section('title', 'Tableau de bord')
@section('header', 'Tableau de bord du candidat')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-4">Statut de votre candidature</h3>
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center mr-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Statut actuel</p>
                    <p class="font-semibold">{{ ucfirst($candidate->status) }}</p>
                </div>
            </div>
            <div class="space-y-3">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full {{ $candidate->documents->count() > 0 ? 'bg-green-100' : 'bg-gray-100' }} flex items-center justify-center mr-3">
                        <svg class="w-4 h-4 {{ $candidate->documents->count() > 0 ? 'text-green-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <span>Documents soumis</span>
                </div>
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full {{ $candidate->documents->where('is_verified', true)->count() == $candidate->documents->count() ? 'bg-green-100' : 'bg-gray-100' }} flex items-center justify-center mr-3">
                        <svg class="w-4 h-4 {{ $candidate->documents->where('is_verified', true)->count() == $candidate->documents->count() ? 'text-green-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <span>Documents vérifiés</span>
                </div>
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full {{ $candidate->hasPassedQuiz() ? 'bg-green-100' : 'bg-gray-100' }} flex items-center justify-center mr-3">
                        <svg class="w-4 h-4 {{ $candidate->hasPassedQuiz() ? 'text-green-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <span>Quiz passé</span>
                </div>
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full {{ $candidate->presentielTests->count() > 0 ? 'bg-green-100' : 'bg-gray-100' }} flex items-center justify-center mr-3">
                        <svg class="w-4 h-4 {{ $candidate->presentielTests->count() > 0 ? 'text-green-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <span>Test présentiel planifié</span>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-4">Actions rapides</h3>
            <div class="grid grid-cols-2 gap-4">
                <a href="{{ route('candidate.documents') }}" class="block p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                    <svg class="w-8 h-8 text-blue-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="block text-sm font-medium">Gérer mes documents</span>
                </a>
                <a href="{{ route('candidate.quizzes.available') }}" class="block p-4 bg-green-50 rounded-lg hover:bg-green-100 transition">
                    <svg class="w-8 h-8 text-green-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <span class="block text-sm font-medium">Passer un quiz</span>
                </a>
                <a href="{{ route('candidate.tests') }}" class="block p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition">
                    <svg class="w-8 h-8 text-purple-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="block text-sm font-medium">Mes tests présentiels</span>
                </a>
                <a href="{{ route('candidate.profile.edit') }}" class="block p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition">
                    <svg class="w-8 h-8 text-yellow-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span class="block text-sm font-medium">Modifier mon profil</span>
                </a>
            </div>
        </div>
    </div>

    @if ($presentielTests->count() > 0)
        <div class="mt-8">
            <h3 class="text-lg font-semibold mb-4">Tests présentiels programmés</h3>
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul class="divide-y divide-gray-200">
                    @foreach ($presentielTests as $test)
                        <li>
                            <div class="px-4 py-4 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-indigo-600 truncate">
                                                Test présentiel - {{ $test->date->format('d/m/Y à H:i') }}
                                            </p>
                                            <p class="mt-1 text-sm text-gray-500">
                                                Lieu: {{ $test->location }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="ml-2 flex-shrink-0 flex">
                                        <p class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($test->status == 'scheduled') bg-yellow-100 text-yellow-800
                                            @elseif($test->status == 'completed') bg-green-100 text-green-800
                                            @elseif($test->status == 'cancelled') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ ucfirst($test->status) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
@endsection