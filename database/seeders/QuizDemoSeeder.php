<?php

namespace Database\Seeders;

use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizPlayer;
use App\Models\QuizQuestion;
use App\Models\QuizSession;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuizDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $owner = User::query()->first();

            $admin = User::query()->firstOrCreate(
                ['email' => 'quiz.admin@example.com'],
                [
                    'name' => 'Quiz Admin',
                    'password' => 'password',
                ]
            );

            $owner ??= $admin;

            $quiz = Quiz::query()->updateOrCreate(
                ['access_code' => 'DEMO2026'],
                [
                    'user_id' => $owner->id,
                    'title' => 'Demo Night Quiz',
                    'description' => 'Quiz de demonstração com múltiplos tipos de perguntas.',
                    'status' => 'published',
                    'is_public' => true,
                ]
            );

            $quiz->update([
                'user_id' => $owner->id,
            ]);

            $quiz->questions()->delete();

            $questions = collect([
                [
                    'position' => 1,
                    'type' => 'multiple_choice',
                    'prompt' => 'Qual é a capital de Portugal?',
                    'options' => ['Lisboa', 'Porto', 'Braga', 'Faro'],
                    'correct_answer' => 'Lisboa',
                    'time_limit_seconds' => 20,
                    'points' => 100,
                ],
                [
                    'position' => 2,
                    'type' => 'open_text',
                    'prompt' => 'Em que ano começou o Laravel?',
                    'options' => null,
                    'correct_answer' => '2011',
                    'time_limit_seconds' => 25,
                    'points' => 150,
                ],
                [
                    'position' => 3,
                    'type' => 'blur_image',
                    'prompt' => 'Que monumento está na imagem?',
                    'options' => null,
                    'correct_answer' => 'Torre Eiffel',
                    'media_path' => null,
                    'time_limit_seconds' => 30,
                    'points' => 200,
                ],
                [
                    'position' => 4,
                    'type' => 'audio',
                    'prompt' => 'Que instrumento está a tocar?',
                    'options' => null,
                    'correct_answer' => 'Piano',
                    'media_path' => null,
                    'time_limit_seconds' => 30,
                    'points' => 200,
                ],
            ])->map(fn (array $question): QuizQuestion => $quiz->questions()->create($question));

            $firstQuestion = $questions->first();

            $session = QuizSession::query()->updateOrCreate(
                ['code' => 'LIVE2026'],
                [
                    'quiz_id' => $quiz->id,
                    'state' => 'question_live',
                    'current_question_id' => $firstQuestion?->id,
                    'current_question_position' => $firstQuestion?->position ?? 1,
                    'started_at' => now(),
                    'ended_at' => null,
                ]
            );

            QuizAnswer::query()->where('quiz_session_id', $session->id)->delete();
            QuizPlayer::query()->where('quiz_session_id', $session->id)->delete();

            $players = collect([
                ['nickname' => 'Ana'],
                ['nickname' => 'Bruno'],
                ['nickname' => 'Carla'],
            ])->map(function (array $playerData) use ($session): QuizPlayer {
                return QuizPlayer::query()->create([
                    'quiz_session_id' => $session->id,
                    'nickname' => $playerData['nickname'],
                    'score' => 0,
                    'joined_at' => now(),
                    'last_seen_at' => now(),
                ]);
            });

            $questionId = $firstQuestion?->id;

            if ($questionId !== null) {
                QuizAnswer::query()->create([
                    'quiz_session_id' => $session->id,
                    'quiz_question_id' => $questionId,
                    'quiz_player_id' => $players[0]->id,
                    'answer_choice' => 'Lisboa',
                    'is_correct' => true,
                    'points_awarded' => 100,
                    'answered_at' => now(),
                ]);

                QuizAnswer::query()->create([
                    'quiz_session_id' => $session->id,
                    'quiz_question_id' => $questionId,
                    'quiz_player_id' => $players[1]->id,
                    'answer_choice' => 'Porto',
                    'is_correct' => false,
                    'points_awarded' => 0,
                    'answered_at' => now(),
                ]);
            }

            $players->each(function (QuizPlayer $player): void {
                $player->update([
                    'score' => (int) $player->answers()->sum('points_awarded'),
                ]);
            });
        });
    }
}
