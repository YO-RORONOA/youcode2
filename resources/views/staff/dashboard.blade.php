@extends('layouts.app')

@section('title', 'Tableau de bord Staff')
@section('header', 'Tableau de bord ' . Auth::user()->roles->first()->name)

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-4">Tests présentiels à venir</h3>
        @if($upcomingTests->count() > 0)
            <div class="space-y-4">
                @foreach($upcomingTests as $test)
                    <div class="border-l-4 border-blue-500 pl-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-medium">{{ $test->candidate->user->name }}</p>
                                <p class="text-sm text-gray-600">{{ $test->date->format('d/m/Y à H:i') }}</p>
                                <p class="text-sm text-gray-600">Lieu: {{ $test->location }}</p>
                            </div>
                            <a href="{{ route('staff.tests.view', $test->id) }}" class="px-3 py-1 bg-blue-100 text-blue-700 rounded-md text-sm">Détails</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500">Aucun test présentiel planifié.</p>
        @endif
    </div>

    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-4">Actions rapides</h3>
        <div class="grid grid-cols-2 gap-4">
            <a href="{{ route('staff.tests.index') }}" class="block p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                <svg class="w-8 h-8 text-blue-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span class="block text-sm font-medium">Mes tests</span>
            </a>
            <a href="{{ route('staff.availabilities') }}" class="block p-4 bg-green-50 rounded-lg hover:bg-green-100 transition">
                <svg class="w-8 h-8 text-green-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span class="block text-sm font-medium">Mes disponibilités</span>
            </a>
        </div>
    </div>
</div>

@if($availabilities->count() > 0)
<div class="mt-8">
    <h3 class="text-lg font-semibold mb-4">Mes prochaines disponibilités</h3>
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <ul class="divide-y divide-gray-200">
            @foreach($availabilities as $availability)
                <li>
                    <div class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-indigo-600">
                                    {{ $availability->date->format('d/m/Y') }}
                                </p>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ $availability->start_time->format('H:i') }} - {{ $availability->end_time->format('H:i') }}
                                </p>
                            </div>
                            <div class="ml-2 flex-shrink-0 flex">
                                <p class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $availability->is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $availability->is_available ? 'Disponible' : 'Indisponible' }}
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