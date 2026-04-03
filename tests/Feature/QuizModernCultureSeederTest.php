<?php

namespace Tests\Feature;

use App\Models\Quiz;
use Database\Seeders\QuizModernCultureSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizModernCultureSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_modern_culture_seeder_creates_quiz_with_30_questions(): void
    {
        $this->seed(QuizModernCultureSeeder::class);

        /** @var Quiz $quiz */
        $quiz = Quiz::query()->where('access_code', 'ARTM2026')->firstOrFail();

        $this->assertSame('Arte Moderna, Cinema e Musica', $quiz->title);
        $this->assertSame(30, $quiz->questions()->count());

        $this->assertDatabaseHas('quiz_questions', [
            'quiz_id' => $quiz->id,
            'position' => 30,
            'correct_answer' => 'Dory',
        ]);
    }
}
