<?php

namespace Database\Seeders;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Database\Seeder;

class QuizSeeder extends Seeder
{
    public function run()
    {
        $quiz = Quiz::create([
            'title' => 'Quiz d\'évaluation des compétences en développement web',
            'description' => 'Ce quiz évalue vos connaissances de base en HTML, CSS et JavaScript.',
            'time_limit' => 30, // 30 minutes
            'passing_score' => 70,
            'is_active' => true,
        ]);

        // Ajouter des questions
        $questions = [
            [
                'content' => 'Que signifie HTML?',
                'points' => 10,
                'options' => [
                    ['content' => 'Hyper Text Markup Language', 'is_correct' => true],
                    ['content' => 'High Tech Modern Language', 'is_correct' => false],
                    ['content' => 'Hyper Transfer Markup Language', 'is_correct' => false],
                    ['content' => 'Home Tool Markup Language', 'is_correct' => false],
                ],
            ],
            [
                'content' => 'Quelle balise HTML est utilisée pour définir un paragraphe?',
                'points' => 10,
                'options' => [
                    ['content' => '<paragraph>', 'is_correct' => false],
                    ['content' => '<p>', 'is_correct' => true],
                    ['content' => '<para>', 'is_correct' => false],
                    ['content' => '<text>', 'is_correct' => false],
                ],
            ],
            [
                'content' => 'Comment déclarer une variable en JavaScript?',
                'points' => 10,
                'options' => [
                    ['content' => 'var nomVariable;', 'is_correct' => true],
                    ['content' => 'variable nomVariable;', 'is_correct' => false],
                    ['content' => 'v nomVariable;', 'is_correct' => false],
                    ['content' => '#nomVariable;', 'is_correct' => false],
                ],
            ],
            // ...Ajoutez d'autres questions
            ];
            
            foreach ($questions as $questionData) {
                $question = Question::create([
                    'quiz_id' => $quiz->id,
                    'content' => $questionData['content'],
                    'points' => $questionData['points'],
                ]);
            
                foreach ($questionData['options'] as $optionData) {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'content' => $optionData['content'],
                        'is_correct' => $optionData['is_correct'],
                    ]);
                }
            }
        }
    }