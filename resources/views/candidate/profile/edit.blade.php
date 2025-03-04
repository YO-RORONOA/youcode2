@extends('layouts.app')

@section('title', 'Éditer mon profil')
@section('header', 'Compléter mon profil')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <form method="POST" action="{{ route('candidate.profile.update') }}">
                @csrf
                @method('PUT')
                
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700">Nom complet</label>
                    <input type="text" name="name" id="name" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ old('name', Auth::user()->name) }}" required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="mb-4">
                    <label for="birth_date" class="block text-sm font-medium text-gray-700">Date de naissance</label>
                    <input type="date" name="birth_date" id="birth_date" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ old('birth_date', $candidate ? $candidate->birth_date : '') }}" required>
                    @error('birth_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="mb-4">
                    <label for="phone" class="block text-sm font-medium text-gray-700">Numéro de téléphone</label>
                    <input type="text" name="phone" id="phone" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ old('phone', $candidate ? $candidate->phone : '') }}" required>
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="mb-4">
                    <label for="address" class="block text-sm font-medium text-gray-700">Adresse complète</label>
                    <textarea name="address" id="address" rows="3" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>{{ old('address', $candidate ? $candidate->address : '') }}</textarea>
                    @error('address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="flex items-center justify-end mt-6">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Enregistrer mon profil
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="mt-6 bg-gray-50 p-4 rounded-md">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-gray-800">Information importante</h3>
                <div class="mt-2 text-sm text-gray-600">
                    <p>
                        Vous devez compléter votre profil avant de pouvoir soumettre des documents ou passer un quiz.
                        Assurez-vous que toutes les informations fournies sont exactes.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection