@extends('layouts.app')

@section('title', 'Détails du test')
@section('header', 'Détails du test ' . ucfirst($test->test_type))

@section('content')
<div class="mb-6 bg-white rounded-lg shadow-md overflow-hidden">
    <div class="bg-indigo-100 px-6 py-4">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">
                    Test {{ ucfirst($test->test_type) }} - {{ $test->candidate->user->name }}
                </h2>
                <p class="text-sm text-gray-600">
                    {{ $test->date->format('d/m/Y à H:i') }} - {{ $test->location }}
                </p>
            </div>
            <div class="mt-2 md:mt-0">
                <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full
                    @if($test->status === 'scheduled') bg-yellow-100 text-yellow-800
                    @elseif($test->status === 'completed') bg-green-100 text-green-800
                    @elseif($test->status === 'cancelled') bg-red-100 text-red-800
                    @endif">
                    {{ ucfirst($test->status) }}
                </span>
            </div>
        </div>
    </div>
    
    <div class="px-6 py-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-md font-medium text-gray-700 mb-2">Informations du candidat</h3>
                <ul class="space-y-2 text-sm">
                    <li>
                        <span class="text-gray-600">Nom:</span>
                        <span class="font-medium">{{ $test->candidate->user->name }}</span>
                    </li>
                    <li>
                        <span class="text-gray-600">Email:</span>
                        <span class="font-medium">{{ $test->candidate->user->email }}</span>
                    </li>
                    <li>
                        <span class="text-gray-600">Téléphone:</span>
                        <span class="font-medium">{{ $test->candidate->phone ?? 'Non spécifié' }}</span>
                    </li>
                    <li>
                        <span class="text-gray-600">Date de naissance:</span>
                        <span class="font-medium">
                            {{ $test->candidate->birth_date ? $test->candidate->birth_date->format('d/m/Y') : 'Non spécifiée' }}
                        </span>
                    </li>
                </ul>
            </div>
            
            <div>
                <h3 class="text-md font-medium text-gray-700 mb-2">Détails du test</h3>
                <ul class="space-y-2 text-sm">
                    <li>
                        <span class="text-gray-600">Type:</span>
                        <span class="font-medium">{{ ucfirst($test->test_type) }}</span>
                    </li>
                    <li>
                        <span class="text-gray-600">Date et heure:</span>
                        <span class="font-medium">{{ $test->date->format('d/m/Y à H:i') }}</span>
                    </li>
                    <li>
                        <span class="text-gray-600">Lieu:</span>
                        <span class="font-medium">{{ $test->location }}</span>
                    </li>
                    <li>
                        <span class="text-gray-600">Durée:</span>
                        <span class="font-medium">{{ $test->duration }} minutes</span>
                    </li>
                    @if($test->group)
                    <li>
                        <span class="text-gray-600">Groupe:</span>
                        <span class="font-medium">{{ $test->group->name }}</span>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>

@if($test->test_type === 'cme' && $groupCandidates)
<div class="mb-6 bg-white rounded-lg shadow-md overflow-hidden">
    <div class="px-6 py-4">
        <h3 class="text-md font-medium text-gray-700 mb-4">Membres du groupe</h3>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nom
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Email
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Statut
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($groupCandidates as $groupTest)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $groupTest->candidate->user->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $groupTest->candidate->user->email }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @if($groupTest->status === 'scheduled') bg-yellow-100 text-yellow-800
                                    @elseif($groupTest->status === 'completed') bg-green-100 text-green-800
                                    @elseif($groupTest->status === 'cancelled') bg-red-100 text-red-800
                                    @endif">
                                    {{ ucfirst($groupTest->status) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<div class="mb-6 bg-white rounded-lg shadow-md overflow-hidden">
    <div class="px-6 py-4">
        <h3 class="text-md font-medium text-gray-700 mb-4">Commentaires et évaluation</h3>
        
        @if($test->comments->isNotEmpty())
            <div class="mb-4 space-y-4">
                @foreach($test->comments as $comment)
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-medium">{{ $comment->staff->user->name }}</h4>
                                <p class="text-xs text-gray-500">{{ $comment->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($comment->rating === 'pass') bg-green-100 text-green-800
                                @elseif($comment->rating === 'fail') bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800 @endif">
                                {{ $comment->rating === 'pass' ? 'Réussi' : ($comment->rating === 'fail' ? 'Échoué' : 'En attente') }}
                            </span>
                        </div>
                        <div class="mt-2 text-sm text-gray-700">
                            {{ $comment->comment }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
        
        <form action="{{ route('staff.tests.comment', $test->id) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="comment" class="block text-sm font-medium text-gray-700">Votre commentaire</label>
                <textarea id="comment" name="comment" rows="4" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>{{ $test->comments->where('staff_id', auth()->user()->staff->id)->first()->comment ?? '' }}</textarea>
            </div>
            
            <div class="mb-4">
                <label for="rating" class="block text-sm font-medium text-gray-700">Évaluation</label>
                <select id="rating" name="rating" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="pending" {{ ($test->comments->where('staff_id', auth()->user()->staff->id)->first()->rating ?? '') === 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="pass" {{ ($test->comments->where('staff_id', auth()->user()->staff->id)->first()->rating ?? '') === 'pass' ? 'selected' : '' }}>Réussi</option>
                    <option value="fail" {{ ($test->comments->where('staff_id', auth()->user()->staff->id)->first()->rating ?? '') === 'fail' ? 'selected' : '' }}>Échoué</option>
                </select>
            </div>
            
            <div class="mb-4">
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input id="mark_completed" name="mark_completed" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" {{ $test->status === 'completed' ? 'checked' : '' }}>
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="mark_completed" class="font-medium text-gray-700">Marquer le test comme terminé</label>
                    </div>
                </div>
            </div>
            
            <div>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>
@endsection