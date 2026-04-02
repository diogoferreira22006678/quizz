<?php

namespace App\Http\Controllers\Quiz;

use App\Events\QuizSessionUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Quiz\AdvanceQuestionRequest;
use App\Http\Requests\Quiz\StartSessionRequest;
use App\Http\Requests\Quiz\StoreQuizRequest;
use App\Http\Requests\Quiz\UpdateQuizRequest;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizSession;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdminQuizController extends Controller
{
    public function index(Request $request): Response
    {
        $quizzes = Quiz::query()
            ->where('user_id', $request->user()->id)
            ->with(['latestSession', 'latestSession.currentQuestion'])
            ->withCount(['questions', 'sessions'])
            ->latest()
            ->get();

        return Inertia::render('quizzes/admin/index', [
            'quizzes' => $quizzes,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('quizzes/admin/create');
    }

    public function store(StoreQuizRequest $request): RedirectResponse
    {
        $quiz = DB::transaction(function () use ($request): Quiz {
            $quiz = Quiz::create([
                'user_id' => $request->user()->id,
                'title' => $request->string('title')->toString(),
                'description' => $request->string('description')->toString() ?: null,
                'access_code' => $this->generateQuizAccessCode(),
                'status' => $request->string('status')->toString(),
                'is_public' => $request->boolean('is_public'),
            ]);

            collect($request->validated('questions'))
                ->values()
                ->each(function (array $question, int $index) use ($quiz, $request): void {
                    $quiz->questions()->create($this->mapQuestionPayload($request, $question, $index));
                });

            return $quiz;
        });

        return to_route('quizzes.admin.edit', $quiz)->with('status', 'Quiz criado com sucesso.');
    }

    public function edit(Request $request, Quiz $quiz): Response
    {
        $this->ensureOwner($request, $quiz);

        $quiz->load([
            'questions' => fn ($query) => $query->orderBy('position'),
            'latestSession',
            'latestSession.currentQuestion',
        ]);

        return Inertia::render('quizzes/admin/edit', [
            'quiz' => $quiz,
        ]);
    }

    public function update(UpdateQuizRequest $request, Quiz $quiz): RedirectResponse
    {
        $this->ensureOwner($request, $quiz);

        DB::transaction(function () use ($request, $quiz): void {
            $quiz->update([
                'title' => $request->string('title')->toString(),
                'description' => $request->string('description')->toString() ?: null,
                'status' => $request->string('status')->toString(),
                'is_public' => $request->boolean('is_public'),
            ]);

            $questionPayloads = collect($request->validated('questions'))->values();

            $keepQuestionIds = $questionPayloads
                ->pluck('id')
                ->filter(fn ($id): bool => $id !== null)
                ->map(fn ($id): int => (int) $id)
                ->all();

            $quiz->questions()
                ->whereNotIn('id', $keepQuestionIds)
                ->get()
                ->each(function (QuizQuestion $question): void {
                    if ($question->media_path !== null) {
                        Storage::disk('public')->delete($question->media_path);
                    }

                    $question->delete();
                });

            $questionPayloads->each(function (array $question, int $index) use ($quiz, $request): void {
                if (! empty($question['id'])) {
                    $quizQuestion = $quiz->questions()->findOrFail((int) $question['id']);
                    $payload = $this->mapQuestionPayload($request, $question, $index, $quizQuestion->media_path);
                    $quizQuestion->update($payload);

                    return;
                }

                $payload = $this->mapQuestionPayload($request, $question, $index);
                $quiz->questions()->create($payload);
            });
        });

        return to_route('quizzes.admin.edit', $quiz)->with('status', 'Quiz atualizado com sucesso.');
    }

    public function destroy(Request $request, Quiz $quiz): RedirectResponse
    {
        $this->ensureOwner($request, $quiz);

        $quiz->delete();

        return to_route('quizzes.admin.index')->with('status', 'Quiz removido com sucesso.');
    }

    public function startSession(StartSessionRequest $request, Quiz $quiz): RedirectResponse
    {
        $this->ensureOwner($request, $quiz);

        $firstQuestion = $quiz->questions()->orderBy('position')->first();
        $startImmediately = $request->boolean('start_immediately', false);

        $session = QuizSession::create([
            'quiz_id' => $quiz->id,
            'code' => $this->generateSessionCode(),
            'state' => $startImmediately && $firstQuestion !== null ? 'question_live' : 'lobby',
            'current_question_id' => $startImmediately ? $firstQuestion?->id : null,
            'current_question_position' => $startImmediately ? ($firstQuestion?->position ?? 0) : 0,
            'started_at' => $startImmediately ? now() : null,
        ]);

        $session->load('currentQuestion');
        broadcast(new QuizSessionUpdated($session));

        return to_route('quizzes.admin.sessions.show', $session)->with('status', "Sessão iniciada com código {$session->code}.");
    }

    public function showSession(Request $request, QuizSession $session): Response
    {
        $session->load([
            'quiz',
            'currentQuestion',
            'players' => fn ($query) => $query->orderByDesc('score')->limit(20),
        ]);

        $this->ensureOwner($request, $session->quiz);

        $answersCount = $session->current_question_id === null
            ? 0
            : $session->answers()
                ->where('quiz_question_id', $session->current_question_id)
                ->count();

        return Inertia::render('quizzes/admin/live', [
            'session' => $session,
            'quiz' => $session->quiz,
            'question' => $session->currentQuestion,
            'answersCount' => $answersCount,
            'leaderboard' => $session->players,
        ]);
    }

    public function advance(AdvanceQuestionRequest $request, QuizSession $session): RedirectResponse
    {
        $session->load(['quiz.questions' => fn ($query) => $query->orderBy('position')]);
        $this->ensureOwner($request, $session->quiz);

        $questions = $session->quiz->questions->values();
        $action = $request->string('action')->toString();

        if ($action === 'reveal_answers') {
            $session->update(['state' => 'answers_revealed']);
        }

        if ($action === 'next_question') {
            $nextQuestion = $questions->first(fn (QuizQuestion $question): bool => $question->position > $session->current_question_position);

            if ($nextQuestion === null) {
                $session->update([
                    'state' => 'finished',
                    'ended_at' => now(),
                ]);
            } else {
                $session->update([
                    'state' => 'question_live',
                    'current_question_id' => $nextQuestion->id,
                    'current_question_position' => $nextQuestion->position,
                    'started_at' => now(),
                ]);
            }
        }

        if ($action === 'finish') {
            $session->update([
                'state' => 'finished',
                'ended_at' => now(),
            ]);
        }

        $session->refresh()->load('currentQuestion');
        broadcast(new QuizSessionUpdated($session));

        return to_route('quizzes.admin.sessions.show', $session)->with('status', 'Estado da sessão atualizado.');
    }

    private function ensureOwner(Request $request, Quiz $quiz): void
    {
        abort_unless($quiz->user_id === $request->user()->id, 403);
    }

    /**
     * @param  array<string, mixed>  $question
     * @return array<string, mixed>
     */
    private function mapQuestionPayload(Request $request, array $question, int $index, ?string $existingMediaPath = null): array
    {
        $mediaPath = $existingMediaPath;
        /** @var UploadedFile|null $uploadedMedia */
        $uploadedMedia = data_get($request->allFiles(), "questions.{$index}.media_file");

        if ($uploadedMedia !== null) {
            $mediaPath = $uploadedMedia->store('quiz-media', 'public');

            if ($existingMediaPath !== null) {
                Storage::disk('public')->delete($existingMediaPath);
            }
        }

        return [
            'position' => $index + 1,
            'type' => $question['type'],
            'prompt' => $question['prompt'],
            'options' => ! empty($question['options']) ? array_values(array_filter($question['options'])) : null,
            'correct_answer' => $question['correct_answer'] ?? null,
            'media_path' => $mediaPath,
            'time_limit_seconds' => $question['time_limit_seconds'] ?? null,
            'points' => $question['points'] ?? 100,
        ];
    }

    private function generateQuizAccessCode(): string
    {
        do {
            $code = Str::upper(Str::random(8));
        } while (Quiz::query()->where('access_code', $code)->exists());

        return $code;
    }

    private function generateSessionCode(): string
    {
        do {
            $code = Str::upper(Str::random(8));
        } while (QuizSession::query()->where('code', $code)->exists());

        return $code;
    }
}
