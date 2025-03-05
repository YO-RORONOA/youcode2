@extends('layouts.app')

@section('title', 'Gestion des disponibilités')
@section('header', 'Gestion de mes disponibilités')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="md:col-span-2">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-indigo-100 px-6 py-4">
                <h2 class="text-lg font-semibold text-gray-800">Mes disponibilités</h2>
            </div>
            
            <div class="p-6">
                @if($availabilities->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Créneau
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
                                @foreach($availabilities as $availability)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $availability->date->format('d/m/Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($availability->time_slot === 'morning')
                                                Matin (9h-12h)
                                            @elseif($availability->time_slot === 'afternoon')
                                                Après-midi (14h-17h)
                                            @else
                                                Journée complète
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $availability->is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $availability->is_available ? 'Disponible' : 'Indisponible' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <form action="{{ route('staff.tests.availability.store') }}" method="POST" class="inline-block">
                                                @csrf
                                                <input type="hidden" name="date" value="{{ $availability->date->format('Y-m-d') }}">
                                                <input type="hidden" name="time_slot" value="{{ $availability->time_slot }}">
                                                <input type="hidden" name="is_available" value="{{ $availability->is_available ? '0' : '1' }}">
                                                <button type="submit" class="text-indigo-600 hover:text-indigo-900">
                                                    {{ $availability->is_available ? 'Marquer indisponible' : 'Marquer disponible' }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <p class="text-gray-500">Aucune disponibilité enregistrée</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div>
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-indigo-100 px-6 py-4">
                <h2 class="text-lg font-semibold text-gray-800">Ajouter une disponibilité</h2>
            </div>
            
            <div class="p-6">
                <form action="{{ route('staff.tests.availability.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                        <input type="date" name="date" id="date" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" min="{{ date('Y-m-d') }}" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="time_slot" class="block text-sm font-medium text-gray-700">Créneau horaire</label>
                        <select id="time_slot" name="time_slot" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="morning">Matin (9h-12h)</option>
                            <option value="afternoon">Après-midi (14h-17h)</option>
                            <option value="full_day">Journée complète</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="is_available" name="is_available" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" checked>
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="is_available" class="font-medium text-gray-700">Disponible</label>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <button type="submit" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md overflow-hidden mt-6">
            <div class="p-6">
                <h3 class="text-md font-medium text-gray-700 mb-4">Informations</h3>
                <div class="text-sm text-gray-600 space-y-2">
                    <p>Vos disponibilités permettent d'organiser les tests présentiels avec les candidats.</p>
                    <p>Si vous ne renseignez pas de disponibilités, le système supposera que vous êtes disponible pendant les heures de bureau standard.</p>
                    <p>Veuillez maintenir vos disponibilités à jour pour éviter les conflits d'emploi du temps.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection