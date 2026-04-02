<?php

namespace App\Http\Controllers\Quiz;

use App\Http\Controllers\Controller;
use App\Models\QuizSession;
use Inertia\Inertia;
use Inertia\Response;

class DisplayQuizController extends Controller
{
    public function show(QuizSession $session): Response
    {
        $session->load([
            'quiz.questions' => fn ($query) => $query->orderBy('position'),
            'currentQuestion',
            'players' => fn ($query) => $query->orderByDesc('score')->limit(20),
        ]);

        $answersCount = $session->current_question_id === null
            ? 0
            : $session->answers()
                ->where('quiz_question_id', $session->current_question_id)
                ->count();

        return Inertia::render('quizzes/display/show', [
            'session' => $session,
            'question' => $session->currentQuestion,
            'leaderboard' => $session->players,
            'answersCount' => $answersCount,
        ]);
    }
}
