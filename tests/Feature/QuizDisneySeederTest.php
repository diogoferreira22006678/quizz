<?php

namespace Tests\Feature;

use App\Models\Quiz;
use Database\Seeders\QuizDisneySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizDisneySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_disney_seeder_creates_quiz_with_30_questions(): void
    {
        $this->seed(QuizDisneySeeder::class);

        /** @var Quiz $quiz */
        $quiz = Quiz::query()->where('access_code', 'DISN2026')->firstOrFail();

        $this->assertSame('Disney Mania 30', $quiz->title);
        $this->assertSame(30, $quiz->questions()->count());

        $this->assertDatabaseHas('quiz_questions', [
            'quiz_id' => $quiz->id,
            'position' => 30,
            'correct_answer' => 'Pascal',
        ]);
    }
}
