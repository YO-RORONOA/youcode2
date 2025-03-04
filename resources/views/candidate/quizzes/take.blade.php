@extends('layouts.app')

@section('title', 'Passer le Quiz')
@section('header', $attempt->quiz->title)

@section('content')
<div class="mb-6 bg-white p-6 rounded-lg shadow-md">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold">{{ $attempt->quiz->title }}</h2>
            <p class="text-gray-600">{{ $attempt->quiz->description }}</p>
        </div>
        <div class="text-right">
            <div class="text-lg font-bold" id="countdown">--:--</div>
            <p class="text-sm text-gray-500">Temps restant</p>
        </div>
    </div>
</div>

<!-- Stocker les données dans des éléments HTML -->
<div id="quiz-data" 
    data-start-time="{{ $attempt->start_time->toIso8601String() }}"
    data-time-limit="{{ $attempt->quiz->time_limit * 60 }}"
    data-available-url="{{ route('candidate.quizzes.available') }}"
    style="display:none;"></div>

<form id="quiz-form" method="POST" action="{{ route('candidate.quizzes.submit', $attempt->id) }}" class="space-y-8">
    @csrf
    
    @foreach($attempt->quiz->questions as $index => $question)
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-semibold mb-4">Question {{ $index + 1 }}: {{ $question->content }}</h3>
        
        <div class="space-y-3">
            @foreach($question->options as $option)
            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input id="option_{{ $option->id }}" name="question_{{ $question->id }}" type="radio" value="{{ $option->id }}" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                </div>
                <div class="ml-3 text-sm">
                    <label for="option_{{ $option->id }}" class="font-medium text-gray-700">{{ $option->content }}</label>
                </div>
            </div>
            @endforeach
        </div>
        
        @if($question->points > 1)
        <div class="mt-2 text-sm text-gray-500">
            Points: {{ $question->points }}
        </div>
        @endif
    </div>
    @endforeach
    
    <div class="flex justify-between">
        <button type="button" id="quit-button" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Quitter
        </button>
        <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Soumettre
        </button>
    </div>
</form>

<!-- Script JavaScript sans variables PHP intégrées -->
<script type="text/javascript">
    // Attendre que le DOM soit chargé
    document.addEventListener("DOMContentLoaded", function() {
        // Récupérer les données depuis l'élément HTML
        var dataElement = document.getElementById('quiz-data');
        var startTimeStr = dataElement.getAttribute('data-start-time');
        var timeLimitSeconds = parseInt(dataElement.getAttribute('data-time-limit'));
        var availableUrl = dataElement.getAttribute('data-available-url');
        
        // Configurer le bouton de sortie
        document.getElementById('quit-button').addEventListener('click', function() {
            if(confirm('Êtes-vous sûr de vouloir quitter ce quiz? Vos réponses ne seront pas enregistrées.')) {
                window.location.href = availableUrl;
            }
        });
        
        // Démarrer le compte à rebours
        startCountdown(startTimeStr, timeLimitSeconds);
    });
    
    // Fonction de compte à rebours
    function startCountdown(startTimeStr, timeLimitSeconds) {
        var startTime = new Date(startTimeStr);
        var endTime = new Date(startTime.getTime() + (timeLimitSeconds * 1000));
        
        function updateCountdown() {
            var now = new Date();
            var diff = endTime - now;
            
            if (diff <= 0) {
                document.getElementById('countdown').textContent = "00:00";
                document.getElementById('quiz-form').submit();
                return;
            }
            
            var minutes = Math.floor(diff / 1000 / 60);
            var seconds = Math.floor((diff / 1000) % 60);
            
            // Formatage des nombres
            var minutesStr = (minutes < 10) ? "0" + minutes : minutes.toString();
            var secondsStr = (seconds < 10) ? "0" + seconds : seconds.toString();
            
            document.getElementById('countdown').textContent = minutesStr + ":" + secondsStr;
        }
        
        // Mettre à jour toutes les secondes
        updateCountdown();
        setInterval(updateCountdown, 1000);
    }
</script>
@endsection