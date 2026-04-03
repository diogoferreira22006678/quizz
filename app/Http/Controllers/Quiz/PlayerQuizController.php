<?php

namespace App\Http\Controllers\Quiz;

use App\Events\QuizSessionUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Quiz\JoinQuizRequest;
use App\Http\Requests\Quiz\SubmitAnswerRequest;
use App\Models\QuizAnswer;
use App\Models\QuizPlayer;
use App\Models\QuizQuestion;
use App\Models\QuizSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlayerQuizController extends Controller
{
    private const PLAYER_SESSION_MAP_KEY = 'quiz-player-map';

    private const MAX_TRACKED_PLAYER_SESSIONS = 8;

    public function joinPage(): Response
    {
        return Inertia::render('quizzes/player/join');
    }

    public function join(JoinQuizRequest $request): RedirectResponse
    {
        $session = QuizSession::query()
            ->where('code', strtoupper($request->string('code')->toString()))
            ->where('state', '!=', 'finished')
            ->firstOrFail();

        $player = QuizPlayer::create([
            'quiz_session_id' => $session->id,
            'user_id' => $request->user()?->id,
            'nickname' => $request->string('nickname')->toString(),
            'score' => 0,
            'joined_at' => now(),
            'last_seen_at' => now(),
        ]);

        $this->rememberPlayerForSession($session->id, $player->id);

        return to_route('quizzes.player.play', $session);
    }

    public function play(QuizSession $session): Response
    {
        $session->load(['quiz', 'currentQuestion']);
        $totalQuestions = $session->quiz->questions()->count();

        $playerId = $this->playerIdForSession($session->id);

        $player = QuizPlayer::query()
            ->where('id', $playerId)
            ->where('quiz_session_id', $session->id)
            ->first();

        abort_if($player === null, 403);

        $existingAnswer = QuizAnswer::query()
            ->where('quiz_session_id', $session->id)
            ->where('quiz_question_id', $session->current_question_id)
            ->where('quiz_player_id', $player->id)
            ->first();

        return Inertia::render('quizzes/player/play', [
            'session' => $session,
            'question' => $session->currentQuestion,
            'player' => $player,
            'existingAnswer' => $existingAnswer,
            'totalQuestions' => $totalQuestions,
        ]);
    }

    public function submitAnswer(SubmitAnswerRequest $request, QuizSession $session): RedirectResponse
    {
        $session->load(['quiz', 'currentQuestion']);
        abort_unless($session->state === 'question_live', 422);

        $playerId = $this->playerIdForSession($session->id);

        $player = QuizPlayer::query()
            ->where('id', $playerId)
            ->where('quiz_session_id', $session->id)
            ->firstOrFail();

        abort_unless($request->integer('quiz_question_id') === $session->current_question_id, 422);

        /** @var QuizQuestion $question */
        $question = $session->currentQuestion;

        $existingAnswer = QuizAnswer::query()
            ->where('quiz_session_id', $session->id)
            ->where('quiz_question_id', $question->id)
            ->where('quiz_player_id', $player->id)
            ->first();

        if ($existingAnswer !== null) {
            return back()->with('status', 'Já respondeste a esta pergunta.');
        }

        if ($this->hasQuestionExpired($session, $question)) {
            return back()->with('status', 'Tempo esgotado para esta pergunta.');
        }

        $selectedChoice = $request->string('answer_choice')->toString() ?: null;
        $freeTextAnswer = $request->string('answer_text')->toString() ?: null;

        $isCorrect = $this->isCorrectAnswer($question, $selectedChoice, $freeTextAnswer);
        $pointsAwarded = $this->calculatePointsAwarded($session, $question, $isCorrect);

        QuizAnswer::query()->create([
            'quiz_session_id' => $session->id,
            'quiz_question_id' => $question->id,
            'quiz_player_id' => $player->id,
            'answer_choice' => $selectedChoice,
            'answer_text' => $freeTextAnswer,
            'is_correct' => $isCorrect,
            'points_awarded' => $pointsAwarded,
            'answered_at' => now(),
        ]);

        $player->update([
            'score' => (int) $player->answers()->sum('points_awarded'),
            'last_seen_at' => now(),
        ]);

        $session->refresh()->load('currentQuestion');
        broadcast(new QuizSessionUpdated($session));

        return back()->with('status', $isCorrect ? 'Resposta correta!' : 'Resposta enviada.');
    }

    private function playerSessionKey(int $sessionId): string
    {
        return "quiz-player-{$sessionId}";
    }

    private function rememberPlayerForSession(int $sessionId, int $playerId): void
    {
        $playerMap = session(self::PLAYER_SESSION_MAP_KEY, []);

        if (! is_array($playerMap)) {
            $playerMap = [];
        }

        $playerMap[(string) $sessionId] = $playerId;

        if (count($playerMap) > self::MAX_TRACKED_PLAYER_SESSIONS) {
            $playerMap = array_slice($playerMap, -self::MAX_TRACKED_PLAYER_SESSIONS, null, true);
        }

        session()->put(self::PLAYER_SESSION_MAP_KEY, $playerMap);
        session()->forget($this->playerSessionKey($sessionId));
    }

    private function playerIdForSession(int $sessionId): ?int
    {
        $playerMap = session(self::PLAYER_SESSION_MAP_KEY, []);

        if (is_array($playerMap) && array_key_exists((string) $sessionId, $playerMap)) {
            return (int) $playerMap[(string) $sessionId];
        }

        $legacyPlayerId = session($this->playerSessionKey($sessionId));

        if (is_numeric($legacyPlayerId)) {
            $resolvedPlayerId = (int) $legacyPlayerId;
            $this->rememberPlayerForSession($sessionId, $resolvedPlayerId);

            return $resolvedPlayerId;
        }

        return null;
    }

    private function isCorrectAnswer(QuizQuestion $question, ?string $selectedChoice, ?string $freeTextAnswer): bool
    {
        if ($question->correct_answer === null || $question->correct_answer === '') {
            return false;
        }

        $expected = mb_strtolower(trim($question->correct_answer));

        if ($question->type === 'multiple_choice') {
            return $selectedChoice !== null && mb_strtolower(trim($selectedChoice)) === $expected;
        }

        return $freeTextAnswer !== null && mb_strtolower(trim($freeTextAnswer)) === $expected;
    }

    private function calculatePointsAwarded(QuizSession $session, QuizQuestion $question, bool $isCorrect): int
    {
        if (! $isCorrect) {
            return 0;
        }

        $timeLimit = $question->time_limit_seconds ?? 20;

        if ($session->started_at === null || $timeLimit <= 0) {
            return $question->points;
        }

        $elapsedSeconds = max(0, $session->started_at->diffInSeconds(now()));

        if ($elapsedSeconds >= $timeLimit) {
            return 0;
        }

        $remainingSeconds = max(0, $timeLimit - $elapsedSeconds);
        $remainingRatio = $remainingSeconds / $timeLimit;

        return max(1, (int) round($question->points * $remainingRatio));
    }

    private function hasQuestionExpired(QuizSession $session, QuizQuestion $question): bool
    {
        $timeLimit = $question->time_limit_seconds;

        if ($session->started_at === null || $timeLimit === null || $timeLimit <= 0) {
            return false;
        }

        return $session->started_at->diffInSeconds(now()) >= $timeLimit;
    }
}
