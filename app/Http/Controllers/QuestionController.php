<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuestionOption;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function store(Request $request, $quizId)
    {
        $request->validate([
            'content' => 'required|string',
            'points' => 'required|integer|min:1',
            'options' => 'required|array|min:2',
            'options.*.content' => 'required|string',
            'correct_option' => 'required|integer|min:0',
        ]);
        
        $quiz = Quiz::findOrFail($quizId);
        
        $question = Question::create([
            'quiz_id' => $quiz->id,
            'content' => $request->content,
            'points' => $request->points,
        ]);
        
        foreach ($request->options as $index => $optionData) {
            QuestionOption::create([
                'question_id' => $question->id,
                'content' => $optionData['content'],
                'is_correct' => $index == $request->correct_option,
            ]);
        }
        
        return redirect()->route('admin.quizzes.edit', $quizId)
            ->with('success', 'Question ajoutée avec succès');
    }
    
    public function edit($id)
    {
        $question = Question::with('options', 'quiz')->findOrFail($id);
        
        return view('admin.questions.edit', compact('question'));
    }
    
    public function update(Request $request, $id)
    {
        $request->validate([
            'content' => 'required|string',
            'points' => 'required|integer|min:1',
            'options' => 'required|array|min:2',
            'options.*.content' => 'required|string',
            'correct_option' => 'required|integer|min:0',
        ]);
        
        $question = Question::findOrFail($id);
        
        $question->update([
            'content' => $request->content,
            'points' => $request->points,
        ]);
        
        // Supprimer les options existantes
        $question->options()->delete();
        
        // Créer les nouvelles options
        foreach ($request->options as $index => $optionData) {
            QuestionOption::create([
                'question_id' => $question->id,
                'content' => $optionData['content'],
                'is_correct' => $index == $request->correct_option,
            ]);
        }
        
        return redirect()->route('admin.quizzes.edit', $question->quiz_id)
            ->with('success', 'Question mise à jour avec succès');
    }
    
    public function destroy($id)
    {
        $question = Question::findOrFail($id);
        $quizId = $question->quiz_id;
        
        $question->delete();
        
        return redirect()->route('admin.quizzes.edit', $quizId)
            ->with('success', 'Question supprimée avec succès');
    }
}
