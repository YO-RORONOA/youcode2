@extends('layouts.app')

@section('title', 'Accueil')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl font-extrabold text-gray-900 sm:text-5xl sm:tracking-tight lg:text-6xl">YouCode</h1>
            <p class="mt-3 max-w-md mx-auto text-base text-gray-500 sm:text-lg md:mt-5 md:text-xl md:max-w-3xl">
                Platforme de gestion des candidatures pour YouCode, l'école des métiers du numérique.
            </p>
        </div>

        <div class="mt-10">
            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="bg-blue-500 p-4">
                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-900">Inscription</h3>
                        <p class="mt-2 text-gray-600">Créez votre compte pour commencer le processus de candidature.</p>
                        <div class="mt-4">
                            <a href="{{ route('register') }}" class="text-blue-500 hover:text-blue-700">S'inscrire &rarr;</a>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="bg-green-500 p-4">
                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-900">Soumission de documents</h3>
                        <p class="mt-2 text-gray-600">Téléchargez les documents nécessaires à votre candidature.</p>
                        <div class="mt-4">
                            @auth
                                <a href="{{ route('candidate.documents') }}" class="text-green-500 hover:text-green-700">Gérer mes documents &rarr;</a>
                            @else
                                <a href="{{ route('login') }}" class="text-green-500 hover:text-green-700">Se connecter &rarr;</a>
                            @endauth
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="bg-purple-500 p-4">
                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-900">Quiz en ligne</h3>
                        <p class="mt-2 text-gray-600">Passez le quiz en ligne pour évaluer vos compétences.</p>
                        <div class="mt-4">
                            @auth
                                <a href="{{ route('candidate.quizzes.available') }}" class="text-purple-500 hover:text-purple-700">Voir les quiz &rarr;</a>
                            @else
                                <a href="{{ route('login') }}" class="text-purple-500 hover:text-purple-700">Se connecter &rarr;</a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection