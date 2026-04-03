<?php

namespace Database\Seeders;

use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuizModernCultureSeeder extends Seeder
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
                ['access_code' => 'ARTM2026'],
                [
                    'user_id' => $owner->id,
                    'title' => 'Arte Moderna, Cinema e Musica',
                    'description' => '30 perguntas sobre arte moderna, cinema, musica e cinema infantil Disney/Pixar.',
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
                        'time_limit_seconds' => $question['time_limit_seconds'] ?? 20,
                        'points' => $question['points'] ?? 180,
                    ]);
                });
        });
    }

    /**
     * @param  array<int, string>  $options
     * @return array<string, mixed>
     */
    private function mcq(string $prompt, array $options, string $correctAnswer, int $timeLimit = 20, int $points = 180): array
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
            // Arte moderna
            $this->mcq('Quem pintou "Guernica"?', ['Pablo Picasso', 'Salvador Dali', 'Joan Miro', 'Claude Monet'], 'Pablo Picasso', 18, 220),
            $this->mcq('A obra "A Persistencia da Memoria" (relogios derretidos) e de quem?', ['Rene Magritte', 'Salvador Dali', 'Andy Warhol', 'Paul Klee'], 'Salvador Dali', 20, 230),
            $this->mcq('Qual movimento artistico esta associado a Picasso e Braque?', ['Futurismo', 'Cubismo', 'Expressionismo', 'Minimalismo'], 'Cubismo', 16, 190),
            $this->mcq('Quem criou a obra "Campbell\'s Soup Cans"?', ['Andy Warhol', 'Roy Lichtenstein', 'Jackson Pollock', 'Keith Haring'], 'Andy Warhol', 16, 190),
            $this->mcq('Frida Kahlo era natural de que pais?', ['Espanha', 'Mexico', 'Argentina', 'Chile'], 'Mexico', 14, 170),
            $this->mcq('"Fountain" (1917), um urinol assinado R. Mutt, e de quem?', ['Marcel Duchamp', 'Jean Arp', 'Henri Matisse', 'Paul Cezanne'], 'Marcel Duchamp', 22, 250),
            $this->mcq('Qual artista e conhecido por pintar grandes latas de tinta salpicada (dripping)?', ['Mark Rothko', 'Jackson Pollock', 'Edward Hopper', 'Wassily Kandinsky'], 'Jackson Pollock', 18, 210),
            $this->mcq('O MoMA (Museu de Arte Moderna) fica em que cidade?', ['Londres', 'Paris', 'Nova Iorque', 'Berlim'], 'Nova Iorque', 15, 180),
            $this->mcq('Wassily Kandinsky e frequentemente associado a que linguagem visual?', ['Arte abstrata', 'Realismo socialista', 'Arte barroca', 'Neoclassicismo'], 'Arte abstrata', 17, 200),
            $this->mcq('Yayoi Kusama e conhecida especialmente por que elemento recorrente?', ['Pontos (bolinhas)', 'Cubos de madeira', 'Retratos renascentistas', 'Mosaicos azuis'], 'Pontos (bolinhas)', 16, 190),

            // Cinema
            $this->mcq('Quem realizou "Pulp Fiction"?', ['Martin Scorsese', 'Christopher Nolan', 'Quentin Tarantino', 'Ridley Scott'], 'Quentin Tarantino', 14, 180),
            $this->mcq('Qual filme venceu o Oscar de Melhor Filme em 2020?', ['1917', 'Joker', 'Parasite', 'Ford v Ferrari'], 'Parasite', 14, 180),
            $this->mcq('Quem realizou "Titanic"?', ['James Cameron', 'Steven Spielberg', 'Peter Jackson', 'David Fincher'], 'James Cameron', 12, 160),
            $this->mcq('Em "The Matrix" (1999), quem interpreta Neo?', ['Brad Pitt', 'Keanu Reeves', 'Tom Cruise', 'Matt Damon'], 'Keanu Reeves', 13, 170),
            $this->mcq('Qual trilogia inclui os filmes "A Irmandade do Anel", "As Duas Torres" e "O Regresso do Rei"?', ['Star Wars', 'Harry Potter', 'O Senhor dos Aneis', 'Jurassic Park'], 'O Senhor dos Aneis', 12, 160),
            $this->mcq('Que filme de Christopher Nolan explora sonhos em camadas?', ['Interstellar', 'Memento', 'Inception', 'Dunkirk'], 'Inception', 15, 190),
            $this->mcq('Qual ator interpreta o Joker em "The Dark Knight" (2008)?', ['Jared Leto', 'Joaquin Phoenix', 'Heath Ledger', 'Jack Nicholson'], 'Heath Ledger', 16, 200),
            $this->mcq('Qual e o nome da principal industria de cinema da India?', ['Cinecitta', 'Bollywood', 'Nollywood', 'Pinewood'], 'Bollywood', 12, 160),
            $this->mcq('Que realizadora dirigiu "Barbie" (2023)?', ['Patty Jenkins', 'Greta Gerwig', 'Sofia Coppola', 'Kathryn Bigelow'], 'Greta Gerwig', 14, 180),
            $this->mcq('Qual filme portugues foi realizado por Manoel de Oliveira?', ['Aniki-Bobo', 'Tabu', 'Capitaes de Abril', 'Sangue do Meu Sangue'], 'Aniki-Bobo', 20, 230),

            // Musica
            $this->mcq('Que banda canta "Bohemian Rhapsody"?', ['The Beatles', 'Queen', 'The Rolling Stones', 'U2'], 'Queen', 12, 160),
            $this->mcq('Qual artista e conhecido como "Rei do Pop"?', ['Prince', 'Elvis Presley', 'Michael Jackson', 'George Michael'], 'Michael Jackson', 10, 150),
            $this->mcq('"Like a Prayer" e um album/classico de que cantora?', ['Madonna', 'Cher', 'Celine Dion', 'Whitney Houston'], 'Madonna', 12, 160),
            $this->mcq('De que cidade inglesa sao originarios os Beatles?', ['Manchester', 'Liverpool', 'Londres', 'Birmingham'], 'Liverpool', 11, 150),
            $this->mcq('Que cantora lancou o album "25"?', ['Adele', 'Sia', 'Dua Lipa', 'Billie Eilish'], 'Adele', 12, 160),

            // Cinema infantil Disney/Pixar
            $this->mcq('Em "O Rei Leao", como se chama o pai de Simba?', ['Scar', 'Mufasa', 'Zazu', 'Rafiki'], 'Mufasa', 10, 140),
            $this->mcq('Que princesa Disney canta "Let It Go"?', ['Moana', 'Anna', 'Elsa', 'Rapunzel'], 'Elsa', 10, 140),
            $this->mcq('Em "Toy Story", qual e o nome do cowboy?', ['Buzz', 'Woody', 'Slinky', 'Rex'], 'Woody', 10, 140),
            $this->mcq('Qual filme da Disney inclui a musica "We Don\'t Talk About Bruno"?', ['Coco', 'Encanto', 'Viva', 'Luca'], 'Encanto', 11, 150),
            $this->mcq('Em "Procurando Nemo", qual e o nome do peixe amigo de Nemo com perda de memoria recente?', ['Bubbles', 'Gill', 'Dory', 'Marlin'], 'Dory', 10, 140),
        ];
    }
}
