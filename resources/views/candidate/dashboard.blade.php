<!-- Dans candidate/dashboard.blade.php, ajouter après les sections existantes -->

@if($presentielTests->count() > 0)
<div class="mt-8">
    <h3 class="text-lg font-semibold mb-4">Tests présentiels programmés</h3>
    
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
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
                            Statut
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($presentielTests as $test)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @if($test->test_type === 'cme') bg-blue-100 text-blue-800
                                    @elseif($test->test_type === 'technical') bg-green-100 text-green-800
                                    @elseif($test->test_type === 'administrative') bg-purple-100 text-purple-800
                                    @endif">
                                    @if($test->test_type === 'cme')
                                        CME
                                    @elseif($test->test_type === 'technical')
                                        Technique
                                    @elseif($test->test_type === 'administrative')
                                        Administratif
                                    @else
                                        {{ ucfirst($test->test_type) }}
                                    @endif
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
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
                                    @if($test->status === 'scheduled')
                                        Programmé
                                    @elseif($test->status === 'completed')
                                        Terminé
                                    @elseif($test->status === 'cancelled')
                                        Annulé
                                    @else
                                        {{ ucfirst($test->status) }}
                                    @endif
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