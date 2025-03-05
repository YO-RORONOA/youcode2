@extends('layouts.app')

@section('title', 'Mes Tests')
@section('header', 'Gestion de mes tests présentiels')

@section('content')
<div class="space-y-6">
    <!-- Tests à venir -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-indigo-100 px-6 py-4">
            <h2 class="text-lg font-semibold text-gray-800">Tests à venir</h2>
        </div>
        
        <div class="p-6">
            @if($groupedUpcoming->isNotEmpty())
                @foreach($groupedUpcoming as $type => $tests)
                    <div class="mb-6 last:mb-0">
                        <h3 class="text-md font-medium text-gray-700 mb-3">Tests {{ ucfirst($type) }}</h3>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Candidat
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date & Heure
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Lieu
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Statut
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($tests as $test)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $test->candidate->user->name }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $test->candidate->user->email }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    {{ $test->date->format('d/m/Y') }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $test->date->format('H:i') }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $test->location }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    @if($test->status === 'scheduled') bg-yellow-100 text-yellow-800
                                                    @elseif($test->status === 'completed') bg-green-100 text-green-800
                                                    @elseif($test->status === 'cancelled') bg-red-100 text-red-800
                                                    @endif">
                                                    {{ ucfirst($test->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('staff.tests.show', $test->id) }}" class="text-indigo-600 hover:text-indigo-900">Voir détails</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center py-4">
                    <p class="text-gray-500">Aucun test à venir</p>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Tests passés -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-gray-100 px-6 py-4">
            <h2 class="text-lg font-semibold text-gray-800">Tests passés</h2>
        </div>
        
        <div class="p-6">
            @if($groupedPast->isNotEmpty())
                @foreach($groupedPast as $type => $tests)
                    <div class="mb-6 last:mb-0">
                        <h3 class="text-md font-medium text-gray-700 mb-3">Tests {{ ucfirst($type) }}</h3>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Candidat
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date & Heure
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Lieu
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Statut
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($tests as $test)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $test->candidate->user->name }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $test->candidate->user->email }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    {{ $test->date->format('d/m/Y') }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $test->date->format('H:i') }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $test->location }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    @if($test->status === 'scheduled') bg-yellow-100 text-yellow-800
                                                    @elseif($test->status === 'completed') bg-green-100 text-green-800
                                                    @elseif($test->status === 'cancelled') bg-red-100 text-red-800
                                                    @endif">
                                                    {{ ucfirst($test->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('staff.tests.show', $test->id) }}" class="text-indigo-600 hover:text-indigo-900">Voir détails</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center py-4">
                    <p class="text-gray-500">Aucun test passé</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection