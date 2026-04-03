<?php

namespace Tests\Feature;

use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizPlayer;
use App\Models\QuizQuestion;
use App\Models\QuizSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class QuizFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_admin_can_create_quiz_with_questions(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('quizzes.admin.store'), [
            'title' => 'General Knowledge',
            'description' => 'Live quiz night',
            'status' => 'draft',
            'is_public' => false,
            'questions' => [
                [
                    'type' => 'multiple_choice',
                    'prompt' => 'What is 2 + 2?',
                    'options' => ['1', '2', '3', '4'],
                    'correct_answer' => '4',
                    'time_limit_seconds' => 20,
                    'points' => 100,
                ],
            ],
        ]);

        $quiz = Quiz::query()->first();

        $response->assertRedirect(route('quizzes.admin.edit', $quiz));
        $this->assertDatabaseHas('quizzes', [
            'id' => $quiz->id,
            'title' => 'General Knowledge',
            'user_id' => $admin->id,
        ]);

        $this->assertDatabaseHas('quiz_questions', [
            'quiz_id' => $quiz->id,
            'prompt' => 'What is 2 + 2?',
            'correct_answer' => '4',
        ]);
    }

    public function test_player_answer_updates_score_when_correct(): void
    {
        $quiz = Quiz::query()->create([
            'title' => 'Quick Quiz',
            'description' => null,
            'access_code' => 'ABCDEFGH',
            'status' => 'published',
            'is_public' => true,
        ]);

        $question = QuizQuestion::query()->create([
            'quiz_id' => $quiz->id,
            'position' => 1,
            'type' => 'multiple_choice',
            'prompt' => 'Capital of Portugal?',
            'options' => ['Porto', 'Lisboa'],
            'correct_answer' => 'Lisboa',
            'points' => 150,
        ]);

        $session = QuizSession::query()->create([
            'quiz_id' => $quiz->id,
            'code' => 'PLAY2026',
            'state' => 'question_live',
            'current_question_id' => $question->id,
            'current_question_position' => 1,
            'started_at' => now(),
        ]);

        $player = QuizPlayer::query()->create([
            'quiz_session_id' => $session->id,
            'nickname' => 'Diogo',
            'score' => 0,
            'joined_at' => now(),
        ]);

        $response = $this
            ->withSession(["quiz-player-{$session->id}" => $player->id])
            ->post(route('quizzes.player.answer', $session), [
                'quiz_question_id' => $question->id,
                'answer_choice' => 'Lisboa',
                'answer_text' => null,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('quiz_answers', [
            'quiz_session_id' => $session->id,
            'quiz_question_id' => $question->id,
            'quiz_player_id' => $player->id,
            'is_correct' => 1,
            'points_awarded' => 150,
        ]);

        $this->assertDatabaseHas('quiz_players', [
            'id' => $player->id,
            'score' => 150,
        ]);
    }

    public function test_player_can_not_answer_same_question_twice(): void
    {
        $quiz = Quiz::query()->create([
            'title' => 'Quick Quiz',
            'description' => null,
            'access_code' => 'ABCDEFGH',
            'status' => 'published',
            'is_public' => true,
        ]);

        $question = QuizQuestion::query()->create([
            'quiz_id' => $quiz->id,
            'position' => 1,
            'type' => 'multiple_choice',
            'prompt' => 'Capital of Portugal?',
            'options' => ['Porto', 'Lisboa'],
            'correct_answer' => 'Lisboa',
            'points' => 150,
        ]);

        $session = QuizSession::query()->create([
            'quiz_id' => $quiz->id,
            'code' => 'PLAY2026',
            'state' => 'question_live',
            'current_question_id' => $question->id,
            'current_question_position' => 1,
            'started_at' => now(),
        ]);

        $player = QuizPlayer::query()->create([
            'quiz_session_id' => $session->id,
            'nickname' => 'Diogo',
            'score' => 0,
            'joined_at' => now(),
        ]);

        $this
            ->withSession(["quiz-player-{$session->id}" => $player->id])
            ->post(route('quizzes.player.answer', $session), [
                'quiz_question_id' => $question->id,
                'answer_choice' => 'Lisboa',
            ])
            ->assertRedirect();

        $this
            ->withSession(["quiz-player-{$session->id}" => $player->id])
            ->post(route('quizzes.player.answer', $session), [
                'quiz_question_id' => $question->id,
                'answer_choice' => 'Porto',
            ])
            ->assertRedirect();

        $this->assertSame(
            1,
            QuizAnswer::query()
                ->where('quiz_session_id', $session->id)
                ->where('quiz_question_id', $question->id)
                ->where('quiz_player_id', $player->id)
                ->count()
        );

        $this->assertDatabaseHas('quiz_players', [
            'id' => $player->id,
            'score' => 150,
        ]);
    }

    public function test_player_play_page_includes_timer_payload_for_countdown(): void
    {
        $quiz = Quiz::query()->create([
            'title' => 'Timed Quiz',
            'description' => null,
            'access_code' => 'ABCDEFGH',
            'status' => 'published',
            'is_public' => true,
        ]);

        $question = QuizQuestion::query()->create([
            'quiz_id' => $quiz->id,
            'position' => 1,
            'type' => 'multiple_choice',
            'prompt' => 'Capital of Portugal?',
            'options' => ['Porto', 'Lisboa'],
            'correct_answer' => 'Lisboa',
            'time_limit_seconds' => 30,
            'points' => 150,
        ]);

        $session = QuizSession::query()->create([
            'quiz_id' => $quiz->id,
            'code' => 'PLAY2026',
            'state' => 'question_live',
            'current_question_id' => $question->id,
            'current_question_position' => 1,
            'started_at' => now(),
        ]);

        $player = QuizPlayer::query()->create([
            'quiz_session_id' => $session->id,
            'nickname' => 'Diogo',
            'score' => 0,
            'joined_at' => now(),
        ]);

        $this
            ->withSession(["quiz-player-{$session->id}" => $player->id])
            ->get(route('quizzes.player.play', $session))
            ->assertInertia(fn (Assert $page) => $page
                ->component('quizzes/player/play')
                ->where('session.id', $session->id)
                ->where('session.started_at', $session->started_at?->toJSON())
                ->where('question.id', $question->id)
                ->where('question.time_limit_seconds', 30)
            );
    }

    public function test_player_join_stores_session_mapping_in_compact_cookie_safe_structure(): void
    {
        $quiz = Quiz::query()->create([
            'title' => 'Quick Quiz',
            'description' => null,
            'access_code' => 'ABCDEFGH',
            'status' => 'published',
            'is_public' => true,
        ]);

        $session = QuizSession::query()->create([
            'quiz_id' => $quiz->id,
            'code' => 'PLAY2026',
            'state' => 'lobby',
            'current_question_id' => null,
            'current_question_position' => 0,
            'started_at' => null,
        ]);

        $response = $this->post(route('quizzes.player.join'), [
            'code' => 'PLAY2026',
            'nickname' => 'Diogo',
        ]);

        $response
            ->assertRedirect(route('quizzes.player.play', $session))
            ->assertSessionHas('quiz-player-map', function (mixed $value) use ($session): bool {
                if (! is_array($value)) {
                    return false;
                }

                return array_key_exists((string) $session->id, $value) && is_int($value[(string) $session->id]);
            });
    }
}
