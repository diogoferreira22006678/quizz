<?php

namespace Database\Seeders;

use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuizDisneySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $owner = User::query()->firstOrCreate(
                ['email' => 'quiz.admin@example.com'],
                [
                    'name' => 'Quiz Admin',
                    'password' => 'password',
                ]
            );

            $quiz = Quiz::query()->updateOrCreate(
                ['access_code' => 'DISN2026'],
                [
                    'user_id' => $owner->id,
                    'title' => 'Disney Mania 30',
                    'description' => '30 perguntas sobre filmes, personagens, musicas e curiosidades Disney e Pixar.',
                    'status' => 'published',
                    'is_public' => true,
                ]
            );

            $quiz->questions()->delete();

            collect($this->questions())
                ->values()
                ->each(function (array $question, int $index) use ($quiz): void {
                    $quiz->questions()->create([
                        'position' => $index + 1,
                        'type' => 'multiple_choice',
                        'prompt' => $question['prompt'],
                        'options' => $question['options'],
                        'correct_answer' => $question['correct_answer'],
                        'media_path' => null,
                        'time_limit_seconds' => $question['time_limit_seconds'] ?? 18,
                        'points' => $question['points'] ?? 180,
                    ]);
                });
        });
    }

    /**
     * @param  array<int, string>  $options
     * @return array<string, mixed>
     */
    private function mcq(string $prompt, array $options, string $correctAnswer, int $timeLimit = 18, int $points = 180): array
    {
        return [
            'prompt' => $prompt,
            'options' => $options,
            'correct_answer' => $correctAnswer,
            'time_limit_seconds' => $timeLimit,
            'points' => $points,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function questions(): array
    {
        return [
            $this->mcq('Qual e o nome do leao protagonista de O Rei Leao?', ['Mufasa', 'Simba', 'Scar', 'Nala'], 'Simba', 12, 160),
            $this->mcq('Em Frozen, quem canta Let It Go?', ['Anna', 'Elsa', 'Olaf', 'Kristoff'], 'Elsa', 12, 160),
            $this->mcq('Qual e o nome da marioneta cowboy de Toy Story?', ['Buzz Lightyear', 'Woody', 'Jessie', 'Slinky'], 'Woody', 12, 160),
            $this->mcq('Em A Pequena Sereia, como se chama a protagonista?', ['Ariel', 'Ursula', 'Melody', 'Vanessa'], 'Ariel', 12, 160),
            $this->mcq('Em Aladdin, como se chama o papagaio do Jafar?', ['Abu', 'Iago', 'Rajah', 'Zazu'], 'Iago', 16, 180),
            $this->mcq('Qual princesa adormece ao tocar numa roca?', ['Branca de Neve', 'Cinderela', 'Aurora', 'Bela'], 'Aurora', 14, 170),
            $this->mcq('Em Encanto, qual e o nome da familia principal?', ['Familia Rivera', 'Familia Madrigal', 'Familia Tremaine', 'Familia Arendelle'], 'Familia Madrigal', 16, 190),
            $this->mcq('Qual filme Disney tem a musica A Whole New World?', ['Hercules', 'Aladdin', 'Mulan', 'Pocahontas'], 'Aladdin', 14, 170),
            $this->mcq('Em Procurando Nemo, quem e o pai do Nemo?', ['Gill', 'Marlin', 'Bloat', 'Bruce'], 'Marlin', 12, 160),
            $this->mcq('Qual e o nome da cidade dos monstros em Monstros S.A.?', ['Monsterburg', 'Monstropolis', 'Scare City', 'Boo Town'], 'Monstropolis', 16, 190),
            $this->mcq('Em Divertida Mente (Inside Out), qual emocao e azul?', ['Alegria', 'Raiva', 'Tristeza', 'Medo'], 'Tristeza', 12, 160),
            $this->mcq('Qual personagem diz Ao infinito e mais alem?', ['Woody', 'Rex', 'Buzz Lightyear', 'Hamm'], 'Buzz Lightyear', 12, 160),
            $this->mcq('Em Moana, qual e o nome do semideus?', ['Maui', 'Tala', 'Tamatoa', 'Tui'], 'Maui', 14, 170),
            $this->mcq('Qual vilao quer roubar os filhotes em 101 Dalmatas?', ['Lady Tremaine', 'Cruella de Vil', 'Madame Medusa', 'Yzma'], 'Cruella de Vil', 14, 170),
            $this->mcq('Em Ratatouille, qual e o nome do rato cozinheiro?', ['Remy', 'Emile', 'Gusteau', 'Linguini'], 'Remy', 12, 160),
            $this->mcq('Qual e o nome do robo protagonista de WALL-E?', ['EVE', 'WALL-E', 'AUTO', 'MO'], 'WALL-E', 10, 150),
            $this->mcq('Em Coco, qual e o nome do menino protagonista?', ['Miguel', 'Hector', 'Ernesto', 'Julio'], 'Miguel', 12, 160),
            $this->mcq('Qual filme da Pixar tem carros como personagens principais?', ['Turbo', 'Carros', 'Avioes', 'Robos'], 'Carros', 10, 150),
            $this->mcq('Em Mulan, qual e o nome do dragao pequeno?', ['Mushu', 'Khan', 'Cri-Kee', 'Shan Yu'], 'Mushu', 12, 160),
            $this->mcq('Qual princesa vive numa torre com cabelo muito longo?', ['Tiana', 'Merida', 'Rapunzel', 'Jasmine'], 'Rapunzel', 12, 160),
            $this->mcq('Em Peter Pan, qual e o nome da fada?', ['Wendy', 'Sininho', 'Lirio Tigre', 'Jane'], 'Sininho', 12, 160),
            $this->mcq('Qual e o nome do elefante que voa com as orelhas?', ['Babar', 'Horton', 'Dumbo', 'Jumbo'], 'Dumbo', 12, 160),
            $this->mcq('Em A Bela e a Fera, como se chama a protagonista?', ['Bela', 'Aurora', 'Ariel', 'Mulan'], 'Bela', 10, 150),
            $this->mcq('Qual e o nome do cervo protagonista de Bambi?', ['Bambi', 'Feline', 'Tambor', 'Ronno'], 'Bambi', 10, 150),
            $this->mcq('Em Lilo e Stitch, qual e o numero de experimento do Stitch?', ['624', '626', '629', '601'], '626', 18, 210),
            $this->mcq('Em Up, qual e o nome do menino escoteiro?', ['Carl', 'Russell', 'Dug', 'Kevin'], 'Russell', 12, 160),
            $this->mcq('Qual filme tem as irmas Anna e Elsa?', ['Valente', 'Frozen', 'Enrolados', 'Wish'], 'Frozen', 10, 150),
            $this->mcq('Em Os Incriveis, qual e o nome da familia de super-herois?', ['Parr', 'Powers', 'Smith', 'Prime'], 'Parr', 14, 170),
            $this->mcq('Qual personagem e conhecido como Pato Donald?', ['Donald Duck', 'Scrooge McDuck', 'Launchpad', 'Goofy'], 'Donald Duck', 10, 150),
            $this->mcq('Qual e o nome do camaleao de Enrolados?', ['Pascal', 'Maximus', 'Flynn', 'Bruni'], 'Pascal', 12, 160),
        ];
    }
}
