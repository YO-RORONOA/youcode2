@extends('layouts.app')

@section('title', 'Résultats d\'assignation')
@section('header', 'Résultats de l\'assignation automatique')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Résumé</h3>
        <div class="space-y-2">
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Candidats assignés:</span>
                <span class="font-semibold">{{ count($results['assigned']) }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Candidats non assignés:</span>
                <span class="font-semibold">{{ count($results['not_assigned']) }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Erreurs:</span>
                <span class="font-semibold">{{ count($results['errors']) }}</span>
            </div>
        </div>
    </div>
    
    <div class="md:col-span-2 bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Tests par type</h3>
        <div class="grid grid-cols-3 gap-4">
            <div class="bg-blue-50 rounded-lg p-4">
                <h4 class="font-medium text-blue-700 mb-1">Tests CME</h4>
                <p class="text-2xl font-bold text-blue-900">
                    {{ count(array_filter($results['assigned'], function($item) { return $item['type'] === 'cme'; })) }}
                </p>
            </div>
            <div class="bg-green-50 rounded-lg p-4">
                <h4 class="font-medium text-green-700 mb-1">Tests Techniques</h4>
                <p class="text-2xl font-bold text-green-900">
                    {{ count(array_filter($results['assigned'], function($item) { return $item['type'] === 'technical'; })) }}
                </p>
            </div>
            <div class="bg-purple-50 rounded-lg p-4">
                <h4 class="font-medium text-purple-700 mb-1">Tests Admin</h4>
                <p class="text-2xl font-bold text-purple-900">
                    {{ count(array_filter($results['assigned'], function($item) { return $item['type'] === 'administrative'; })) }}
                </p>
            </div>
        </div>
    </div>
</div>

@if(count($results['assigned']) > 0)
<div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
    <div class="bg-green-100 px-6 py-4">
        <h2 class="text-lg font-semibold text-green-800">Candidats assignés avec succès</h2>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Candidat
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Type de test
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Date & Heure
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Lieu
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($results['assigned'] as $assignment)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $assignment['candidate']->user->name }}</div>
                            <div class="text-sm text-gray-500">{{ $assignment['candidate']->user->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($assignment['type'] === 'cme') bg-blue-100 text-blue-800
                                @elseif($assignment['type'] === 'technical') bg-green-100 text-green-800
                                @elseif($assignment['type'] === 'administrative') bg-purple-100 text-purple-800
                                @endif">
                                {{ ucfirst($assignment['type']) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $assignment['test']->date->format('d/m/Y') }}</div>
                            <div class="text-sm text-gray-500">{{ $assignment['test']->date->format('H:i') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $assignment['test']->location }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('admin.tests.assignment.test', $assignment['test']->id) }}" class="text-indigo-600 hover:text-indigo-900">Voir détails</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@if(count($results['not_assigned']) > 0)
<div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
    <div class="bg-yellow-100 px-6 py-4">
        <h2 class="text-lg font-semibold text-yellow-800">Candidats non assignés</h2>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Candidat
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Raison
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($results['not_assigned'] as $nonAssignment)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $nonAssignment['candidate']->user->name }}</div>
                            <div class="text-sm text-gray-500">{{ $nonAssignment['candidate']->user->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $nonAssignment['reason'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('admin.candidates.view', $nonAssignment['candidate']->id) }}" class="text-indigo-600 hover:text-indigo-900">Voir profil</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@if(count($results['errors']) > 0)
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="bg-red-100 px-6 py-4">
        <h2 class="text-lg font-semibold text-red-800">Erreurs</h2>
    </div>
    
    <div class="p-6">
        <ul class="list-disc list-inside space-y-2 text-red-600">
            @foreach($results['errors'] as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

<div class="mt-6 flex justify-between">
    <a href="{{ route('admin.tests.assignment.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
        Retour à l'assignation
    </a>
    
    <a href="{{ route('admin.tests.sessions.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
        Voir les sessions de test
    </a>
</div>
@endsection