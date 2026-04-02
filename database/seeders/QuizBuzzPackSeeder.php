<?php

namespace Database\Seeders;

use App\Models\Quiz;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuizBuzzPackSeeder extends Seeder
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

            collect($this->quizDefinitions())->each(function (array $definition) use ($owner): void {
                $quiz = Quiz::query()->updateOrCreate(
                    ['access_code' => $definition['access_code']],
                    [
                        'user_id' => $owner->id,
                        'title' => $definition['title'],
                        'description' => $definition['description'],
                        'status' => 'published',
                        'is_public' => true,
                    ]
                );

                $quiz->questions()->delete();

                collect($definition['questions'])
                    ->values()
                    ->each(function (array $question, int $index) use ($quiz): void {
                        $quiz->questions()->create([
                            'position' => $index + 1,
                            'type' => $question['type'],
                            'prompt' => $question['prompt'],
                            'options' => $question['options'] ?? null,
                            'correct_answer' => $question['correct_answer'],
                            'media_path' => null,
                            'time_limit_seconds' => $question['time_limit_seconds'] ?? 20,
                            'points' => $question['points'] ?? 200,
                        ]);
                    });
            });
        });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function quizDefinitions(): Collection
    {
        return collect([
            [
                'access_code' => 'BZZA1001',
                'title' => 'Buzz Hard: Geografia Politica e Ciencia',
                'description' => 'Perguntas de alta dificuldade com foco em geopolitica e ciencia.',
                'questions' => $this->geoPoliticaCienciaQuestions(),
            ],
            [
                'access_code' => 'BZZA1002',
                'title' => 'Buzz Hard: Literatura Historia e Cultura',
                'description' => 'Literatura mundial, historia comparada e cultura classica.',
                'questions' => $this->literaturaHistoriaCulturaQuestions(),
            ],
            [
                'access_code' => 'BZZA1003',
                'title' => 'Buzz Hard: Desporto de Elite e Estatistica',
                'description' => 'Desporto dificil com historia olimpica e dados de performance.',
                'questions' => $this->desportoEstatisticaQuestions(),
            ],
            [
                'access_code' => 'BZZA1004',
                'title' => 'Buzz Hard: Politica Economia e Sociedade',
                'description' => 'Instituicoes, economia global e teoria politica avancada.',
                'questions' => $this->politicaEconomiaSociedadeQuestions(),
            ],
            [
                'access_code' => 'BZZA1005',
                'title' => 'Buzz Hard Final: Mix Boss',
                'description' => 'Round final com mistura de ciencia, literatura, geografia, desporto e politica.',
                'questions' => $this->mixBossQuestions(),
            ],
        ]);
    }

    /**
     * @param  array<int, string>  $options
     * @return array<string, mixed>
     */
    private function mcq(string $prompt, array $options, string $correctAnswer, int $timeLimit = 20, int $points = 220): array
    {
        return [
            'type' => 'multiple_choice',
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
    private function geoPoliticaCienciaQuestions(): array
    {
        return [
            $this->mcq('Qual pais tem a maior fronteira terrestre com a Franca (incluindo territorios ultramarinos)?', ['Espanha', 'Brasil', 'Belgica', 'Alemanha'], 'Brasil', 18, 240),
            $this->mcq('Qual e a constante de Planck aproximada em notacao cientifica?', ['6.63x10^-34', '9.81x10^-2', '3.00x10^8', '1.60x10^-19'], '6.63x10^-34', 22, 300),
            $this->mcq('Qual estreito separa a Asia da America do Norte?', ['Magalhaes', 'Bering', 'Gibraltar', 'Hormuz'], 'Bering', 20, 220),
            $this->mcq('O termo realpolitik e mais associado a qual lider historico?', ['Otto von Bismarck', 'Woodrow Wilson', 'Mikhail Gorbachev', 'Charles de Gaulle'], 'Otto von Bismarck', 20, 260),
            $this->mcq('Qual elemento quimico tem simbolo W?', ['Tungstenio', 'Titanio', 'Torio', 'Telurio'], 'Tungstenio', 16, 200),
            $this->mcq('Qual planeta do Sistema Solar tem o maior numero de luas conhecidas?', ['Jupiter', 'Saturno', 'Urano', 'Netuno'], 'Saturno', 18, 230),
            $this->mcq('Qual rio passa por Budapeste?', ['Reno', 'Danubio', 'Vistula', 'Sena'], 'Danubio', 16, 200),
            $this->mcq('Qual gas e mais abundante na atmosfera da Terra?', ['Oxigenio', 'Argonio', 'Nitrogenio', 'Dioxido de carbono'], 'Nitrogenio', 14, 180),
            $this->mcq('Qual camada da atmosfera contem a maior parte do ozono?', ['Troposfera', 'Estratosfera', 'Mesosfera', 'Termosfera'], 'Estratosfera', 20, 230),
            $this->mcq('Qual e a capital da Nova Zelandia?', ['Auckland', 'Christchurch', 'Wellington', 'Hamilton'], 'Wellington', 15, 190),
            $this->mcq('Qual e o mineral principal do granito?', ['Quartzo', 'Calcite', 'Gesso', 'Halite'], 'Quartzo', 18, 210),
            $this->mcq('Qual pais controla a Groenlandia como territorio autonomo?', ['Noruega', 'Canada', 'Dinamarca', 'Islandia'], 'Dinamarca', 17, 200),
            $this->mcq('Qual cidade e sede da OPEP?', ['Viena', 'Genebra', 'Bruxelas', 'Haia'], 'Viena', 18, 220),
            $this->mcq('Quantos eletrons pode ter no maximo a camada M (n=3)?', ['8', '18', '32', '2'], '18', 22, 280),
            $this->mcq('Qual unidade SI mede a resistencia eletrica?', ['Volt', 'Ohm', 'Tesla', 'Joule'], 'Ohm', 14, 180),
            $this->mcq('Qual e o maior oceano da Terra?', ['Atlantico', 'Indico', 'Artico', 'Pacifico'], 'Pacifico', 14, 170),
            $this->mcq('Qual pais africano tem o maior territorio?', ['Argelia', 'Sudao', 'RDC', 'Libia'], 'Argelia', 16, 200),
            $this->mcq('Qual e o nome do processo de divisao celular para formacao de gametas?', ['Mitose', 'Meiose', 'Brotamento', 'Fissao binaria'], 'Meiose', 19, 240),
            $this->mcq('Qual e o ponto de ebulicao da agua ao nivel do mar em graus Celsius?', ['90', '95', '100', '105'], '100', 12, 150),
            $this->mcq('Qual e o satelite natural da Terra?', ['Europa', 'Titan', 'Lua', 'Fobos'], 'Lua', 10, 140),
            $this->mcq('Em qual pais fica o monte Kilimanjaro?', ['Quenia', 'Tanzania', 'Etiopia', 'Uganda'], 'Tanzania', 16, 200),
            $this->mcq('Qual pais nao faz parte do G7?', ['Italia', 'Canada', 'Australia', 'Japao'], 'Australia', 17, 210),
            $this->mcq('Qual e a capital da Mongolia?', ['Astana', 'Ulaanbaatar', 'Bishkek', 'Tashkent'], 'Ulaanbaatar', 18, 220),
            $this->mcq('Qual elemento quimico e representado por Na?', ['Sodio', 'Nitrogenio', 'Neonio', 'Niquel'], 'Sodio', 12, 160),
            $this->mcq('Qual continente tem o maior deserto quente do mundo?', ['Asia', 'Africa', 'America do Sul', 'Australia'], 'Africa', 14, 180),
            $this->mcq('Qual e a principal fonte de energia do Sol?', ['Fissao nuclear', 'Combustao quimica', 'Fusao nuclear', 'Decaimento radioativo'], 'Fusao nuclear', 21, 270),
            $this->mcq('Qual pais tem a cidade de Reykjavik como capital?', ['Finlandia', 'Islandia', 'Noruega', 'Suecia'], 'Islandia', 13, 170),
            $this->mcq('Qual organizacao internacional tem sede em Nova Iorque?', ['NATO', 'ONU', 'UNESCO', 'OSCE'], 'ONU', 14, 180),
            $this->mcq('Qual e o nome do maior arquipelago do mundo por area?', ['Filipinas', 'Indonesia', 'Japao', 'Grecia'], 'Indonesia', 20, 240),
            $this->mcq('Qual e o valor aproximado da aceleracao da gravidade na Terra em m/s2?', ['9.8', '3.7', '1.6', '24.8'], '9.8', 16, 200),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function literaturaHistoriaCulturaQuestions(): array
    {
        return [
            $this->mcq('Quem escreveu "O Nome da Rosa"?', ['Italo Calvino', 'Umberto Eco', 'Primo Levi', 'Alberto Moravia'], 'Umberto Eco', 18, 220),
            $this->mcq('Em que ano caiu Constantinopla?', ['1492', '1453', '1517', '1415'], '1453', 16, 210),
            $this->mcq('Qual epopeia comeca com a colera de Aquiles?', ['Odiseia', 'Eneida', 'Iliada', 'Metamorfoses'], 'Iliada', 15, 230),
            $this->mcq('Qual dinastia governou a China quando Zheng He liderou grandes expedicoes maritimas?', ['Qing', 'Han', 'Ming', 'Yuan'], 'Ming', 22, 280),
            $this->mcq('Qual poeta portugues escreveu "Mensagem"?', ['Luis de Camoes', 'Fernando Pessoa', 'Cesario Verde', 'Eugenio de Andrade'], 'Fernando Pessoa', 14, 190),
            $this->mcq('Quem escreveu "Dom Quixote"?', ['Lope de Vega', 'Miguel de Cervantes', 'Garcilaso de la Vega', 'Calderon de la Barca'], 'Miguel de Cervantes', 16, 210),
            $this->mcq('Qual imperador romano adotou o cristianismo com o Edito de Milao?', ['Augusto', 'Constantino', 'Teodosio', 'Nero'], 'Constantino', 18, 230),
            $this->mcq('Em que cidade nasceu William Shakespeare?', ['Oxford', 'Londres', 'Stratford-upon-Avon', 'Bath'], 'Stratford-upon-Avon', 15, 190),
            $this->mcq('Quem pintou o teto da Capela Sistina?', ['Rafael', 'Donatello', 'Michelangelo', 'Titian'], 'Michelangelo', 14, 180),
            $this->mcq('Qual tratado encerrou formalmente a Primeira Guerra Mundial com a Alemanha?', ['Versalhes', 'Tordesilhas', 'Utrecht', 'Augsburgo'], 'Versalhes', 17, 220),
            $this->mcq('Quem escreveu "Cem Anos de Solidao"?', ['Mario Vargas Llosa', 'Pablo Neruda', 'Gabriel Garcia Marquez', 'Julio Cortazar'], 'Gabriel Garcia Marquez', 16, 210),
            $this->mcq('Qual farao e associado a descoberta de um tumulo quase intacto em 1922?', ['Ramses II', 'Akhenaton', 'Tutankhamon', 'Seti I'], 'Tutankhamon', 18, 230),
            $this->mcq('Qual movimento artistico inclui Salvador Dali?', ['Cubismo', 'Surrealismo', 'Fauvismo', 'Impressionismo'], 'Surrealismo', 15, 190),
            $this->mcq('Quem escreveu a peca "Hamlet"?', ['Christopher Marlowe', 'Ben Jonson', 'William Shakespeare', 'Thomas Kyd'], 'William Shakespeare', 14, 180),
            $this->mcq('Qual civilizacao construiu Machu Picchu?', ['Maya', 'Inca', 'Azteca', 'Olmeca'], 'Inca', 15, 200),
            $this->mcq('Quem e o autor de "Os Lusiadas"?', ['Fernando Pessoa', 'Eca de Queiros', 'Luis de Camoes', 'Bocage'], 'Luis de Camoes', 13, 170),
            $this->mcq('Em que seculo ocorreu a Revolucao Francesa?', ['XVII', 'XVIII', 'XIX', 'XVI'], 'XVIII', 14, 180),
            $this->mcq('Quem compoz a opera "A Flauta Magica"?', ['Bach', 'Mozart', 'Beethoven', 'Vivaldi'], 'Mozart', 16, 200),
            $this->mcq('Qual autor escreveu "1984"?', ['Aldous Huxley', 'George Orwell', 'Ray Bradbury', 'Arthur Koestler'], 'George Orwell', 15, 190),
            $this->mcq('Qual imperio tinha a cidade de Tenochtitlan como capital?', ['Inca', 'Asteca', 'Maya', 'Tolteca'], 'Asteca', 15, 200),
            $this->mcq('Quem escreveu "A Republica"?', ['Socrates', 'Aristoteles', 'Platao', 'Epicuro'], 'Platao', 16, 210),
            $this->mcq('Qual cidade foi dividida por um muro de 1961 a 1989?', ['Viena', 'Berlim', 'Praga', 'Varsovia'], 'Berlim', 14, 180),
            $this->mcq('Qual autora escreveu "Orgulho e Preconceito"?', ['Emily Bronte', 'Jane Austen', 'Virginia Woolf', 'Mary Shelley'], 'Jane Austen', 14, 180),
            $this->mcq('Qual navegador completou a primeira circum-navegacao, segundo a expedicao que liderou inicialmente?', ['Cristovao Colombo', 'Fernao de Magalhaes', 'Vasco da Gama', 'James Cook'], 'Fernao de Magalhaes', 20, 240),
            $this->mcq('Qual escritor russo e autor de "Crime e Castigo"?', ['Tolstoi', 'Dostoievski', 'Gogol', 'Tchekhov'], 'Dostoievski', 17, 220),
            $this->mcq('Qual estilo arquitetonico caracteriza as grandes catedrais medievais com arcos ogivais?', ['Romanico', 'Gotico', 'Barroco', 'Neoclassico'], 'Gotico', 16, 200),
            $this->mcq('Quem escreveu "A Divina Comedia"?', ['Petrarca', 'Boccaccio', 'Dante Alighieri', 'Torquato Tasso'], 'Dante Alighieri', 16, 210),
            $this->mcq('Qual periodo historico sucede imediatamente a Idade Media europeia?', ['Antiguidade', 'Renascimento/Idade Moderna', 'Iluminismo', 'Idade Contemporanea'], 'Renascimento/Idade Moderna', 17, 210),
            $this->mcq('Qual compositora ou compositor escreveu "As Quatro Estacoes"?', ['Corelli', 'Vivaldi', 'Handel', 'Scarlatti'], 'Vivaldi', 14, 180),
            $this->mcq('Qual obra de Eca de Queiros retrata criticamente a sociedade lisboeta do seculo XIX?', ['A Cidade e as Serras', 'Os Maias', 'O Primo Basilio', 'A Reliquia'], 'Os Maias', 18, 230),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function desportoEstatisticaQuestions(): array
    {
        return [
            $this->mcq('Que pais venceu o primeiro Mundial de Futebol em 1930?', ['Brasil', 'Italia', 'Uruguai', 'Argentina'], 'Uruguai', 15, 180),
            $this->mcq('Quantos metros tem a maratona olimpica?', ['40000', '42195', '42500', '42000'], '42195', 18, 220),
            $this->mcq('Quantos torneios Masters 1000 existem por temporada no ATP Tour?', ['7', '8', '9', '10'], '9', 16, 210),
            $this->mcq('Quem foi campeao de Formula 1 por Ferrari, Brabham e McLaren?', ['Fernando Alonso', 'Niki Lauda', 'Lewis Hamilton', 'Juan Manuel Fangio'], 'Niki Lauda', 22, 280),
            $this->mcq('Em que ano Michael Phelps conquistou 8 ouros numa unica Olimpiada?', ['2004', '2008', '2012', '2016'], '2008', 18, 240),
            $this->mcq('Qual tenista venceu mais titulos de Roland Garros no singulares masculino?', ['Federer', 'Nadal', 'Djokovic', 'Borg'], 'Nadal', 14, 190),
            $this->mcq('Qual selecao ganhou o Euro 2016?', ['Franca', 'Portugal', 'Alemanha', 'Espanha'], 'Portugal', 13, 170),
            $this->mcq('Quantos jogadores por equipa estao em campo no basquetebol?', ['4', '5', '6', '7'], '5', 11, 150),
            $this->mcq('Qual distancia oficial dos 100 jardas em metros aproximados?', ['91.44', '100.00', '88.00', '95.50'], '91.44', 16, 200),
            $this->mcq('Em natacao, qual estilo e nadado de costas?', ['Bruco', 'Costas', 'Mariposa', 'Livre'], 'Costas', 10, 140),
            $this->mcq('Qual pais sediou o Mundial FIFA de 2014?', ['Africa do Sul', 'Brasil', 'Russia', 'Alemanha'], 'Brasil', 12, 160),
            $this->mcq('Quem ganhou o recorde historico de 23 titulos de Grand Slam em singulares femininos?', ['Steffi Graf', 'Serena Williams', 'Martina Navratilova', 'Chris Evert'], 'Serena Williams', 18, 230),
            $this->mcq('Qual e o numero maximo de sets no singulares masculino de Wimbledon?', ['3', '4', '5', '7'], '5', 13, 170),
            $this->mcq('Qual pais venceu o Mundial de Rugby de 2019?', ['Inglaterra', 'Nova Zelandia', 'Africa do Sul', 'Australia'], 'Africa do Sul', 16, 210),
            $this->mcq('Qual e a altura oficial do aro de basquetebol em metros?', ['2.95', '3.05', '3.15', '2.85'], '3.05', 14, 180),
            $this->mcq('Quantos buracos tem uma volta regulamentar de golfe?', ['9', '12', '18', '27'], '18', 12, 160),
            $this->mcq('Qual ciclista portugues venceu a Volta a Espanha em 2024?', ['Joao Almeida', 'Rui Costa', 'Joao Rodrigues', 'Nenhum portugues'], 'Nenhum portugues', 20, 250),
            $this->mcq('Qual atleta jamaicano detem o recorde mundial dos 100m rasos?', ['Yohan Blake', 'Usain Bolt', 'Asafa Powell', 'Tyson Gay'], 'Usain Bolt', 11, 160),
            $this->mcq('No voleibol indoor, quantos jogadores por equipa estao em campo?', ['5', '6', '7', '8'], '6', 10, 140),
            $this->mcq('Qual clube venceu a UEFA Champions League 2021-22?', ['Liverpool', 'Real Madrid', 'Manchester City', 'Chelsea'], 'Real Madrid', 15, 190),
            $this->mcq('Qual e o valor de um touchdown no futebol americano (antes do extra point)?', ['3', '5', '6', '7'], '6', 13, 170),
            $this->mcq('Qual pais venceu mais Copas do Mundo FIFA masculinas?', ['Alemanha', 'Italia', 'Brasil', 'Argentina'], 'Brasil', 14, 190),
            $this->mcq('Quantos minutos tem uma partida de andebol (tempo regulamentar)?', ['50', '60', '70', '80'], '60', 12, 160),
            $this->mcq('Qual e o nome da principal liga profissional de basquetebol dos EUA?', ['NFL', 'MLB', 'NBA', 'NHL'], 'NBA', 10, 140),
            $this->mcq('Qual piloto tem mais titulos mundiais de Formula 1 (empatado no topo)?', ['Senna', 'Hamilton', 'Schumacher', 'Vettel'], 'Hamilton', 18, 220),
            $this->mcq('Nos Jogos Olimpicos, em que cidade foram realizados os jogos de 2000?', ['Atenas', 'Sydney', 'Atlanta', 'Pequim'], 'Sydney', 13, 170),
            $this->mcq('Qual e o pais de origem do judo?', ['Coreia do Sul', 'China', 'Japao', 'Tailandia'], 'Japao', 11, 150),
            $this->mcq('Qual selecao venceu o Mundial FIFA de 2018?', ['Franca', 'Croacia', 'Belgica', 'Alemanha'], 'Franca', 12, 160),
            $this->mcq('Qual prova do atletismo combina corrida, saltos e lancamentos masculinos em 10 disciplinas?', ['Heptatlo', 'Decatlo', 'Pentatlo', 'Triatlo'], 'Decatlo', 16, 210),
            $this->mcq('Qual clube portugues venceu a Liga dos Campeoes em 2004?', ['Benfica', 'Sporting', 'FC Porto', 'Braga'], 'FC Porto', 14, 190),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function politicaEconomiaSociedadeQuestions(): array
    {
        return [
            $this->mcq('Qual instituicao define as taxas diretoras na zona euro?', ['Banco de Portugal', 'Comissao Europeia', 'BCE', 'Conselho Europeu'], 'BCE', 14, 180),
            $this->mcq('Qual pais adotou uma constituicao moderna em 1787?', ['Franca', 'Reino Unido', 'Estados Unidos', 'Portugal'], 'Estados Unidos', 20, 240),
            $this->mcq('No sistema politico frances, o chefe de governo e o:', ['Chanceler', 'Primeiro-Ministro', 'Presidente do Conselho', 'Secretario de Estado'], 'Primeiro-Ministro', 16, 210),
            $this->mcq('Que indicador mede a variacao media dos precos ao consumidor?', ['PIB nominal', 'Desemprego', 'IPC', 'Balanca comercial'], 'IPC', 14, 170),
            $this->mcq('Quem escreveu "O Contrato Social"?', ['Montesquieu', 'Voltaire', 'Jean-Jacques Rousseau', 'Diderot'], 'Jean-Jacques Rousseau', 20, 250),
            $this->mcq('Qual orgao da ONU e responsavel por manter a paz e seguranca internacionais?', ['ECOSOC', 'Assembleia Geral', 'Conselho de Seguranca', 'UNESCO'], 'Conselho de Seguranca', 16, 210),
            $this->mcq('Qual imposto e aplicado sobre o valor acrescentado em muitos paises europeus?', ['IRS', 'IRC', 'IVA', 'IMI'], 'IVA', 12, 160),
            $this->mcq('Qual sistema politico concentra a chefia de Estado e de governo na mesma figura eleita?', ['Parlamentarismo', 'Presidencialismo', 'Semipresidencialismo', 'Monarquia parlamentar'], 'Presidencialismo', 18, 220),
            $this->mcq('Qual e a moeda oficial do Japao?', ['Won', 'Yuan', 'Iene', 'Ringgit'], 'Iene', 11, 150),
            $this->mcq('Que conceito descreve aumento geral e sustentado de precos?', ['Deflacao', 'Inflacao', 'Recessao', 'Estagnacao'], 'Inflacao', 12, 160),
            $this->mcq('Qual tratado instituiu oficialmente a Uniao Europeia?', ['Roma', 'Maastricht', 'Lisboa', 'Schengen'], 'Maastricht', 15, 190),
            $this->mcq('Qual e o banco central dos Estados Unidos?', ['Bank of America', 'Federal Reserve', 'US Treasury', 'IMF'], 'Federal Reserve', 15, 190),
            $this->mcq('Numa democracia liberal, qual principio garante divisao entre poderes?', ['Soberania popular', 'Laicidade', 'Separacao de poderes', 'Federalismo'], 'Separacao de poderes', 16, 200),
            $this->mcq('Que indicador mede o valor total de bens e servicos finais de um pais?', ['IDH', 'PIB', 'GINI', 'PPC'], 'PIB', 12, 160),
            $this->mcq('Qual pais tem o Bundestag como parlamento federal?', ['Austria', 'Suica', 'Alemanha', 'Belgica'], 'Alemanha', 13, 170),
            $this->mcq('O que significa a sigla OCDE?', ['Organizacao para Cooperacao e Desenvolvimento Economico', 'Organizacao de Comercio e Desenvolvimento Europeu', 'Ordem de Cooperacao para Direitos Economicos', 'Observatorio Comum de Dados Economicos'], 'Organizacao para Cooperacao e Desenvolvimento Economico', 20, 250),
            $this->mcq('Que curva economica relaciona desemprego e inflacao no curto prazo?', ['Curva de Laffer', 'Curva IS', 'Curva de Phillips', 'Curva de Lorenz'], 'Curva de Phillips', 17, 220),
            $this->mcq('Qual e o mandato principal do FMI?', ['Defender direitos humanos', 'Estabilidade financeira internacional', 'Promover turismo', 'Gerir comercio global'], 'Estabilidade financeira internacional', 18, 220),
            $this->mcq('Que conceito descreve producao de bens em varios paises por etapas?', ['Mercantilismo', 'Cadeias globais de valor', 'Autarquia', 'Dumping'], 'Cadeias globais de valor', 16, 200),
            $this->mcq('Qual indice mede desigualdade de rendimento?', ['HDI', 'GINI', 'CPI', 'PMI'], 'GINI', 13, 170),
            $this->mcq('No parlamento portugues, quantos deputados tem a Assembleia da Republica?', ['180', '200', '230', '250'], '230', 14, 190),
            $this->mcq('Que tipo de desemprego resulta de transicao entre empregos?', ['Ciclico', 'Estrutural', 'Friccional', 'Sazonal'], 'Friccional', 15, 180),
            $this->mcq('Qual e o principal objetivo de uma politica monetaria contracionista?', ['Aumentar consumo', 'Reduzir inflacao', 'Aumentar exportacoes', 'Baixar impostos'], 'Reduzir inflacao', 16, 200),
            $this->mcq('Que organizacao regula regras multilaterais de comercio internacional?', ['OMC', 'OPEP', 'NATO', 'BIS'], 'OMC', 14, 180),
            $this->mcq('Qual destes e um regime nao democratico?', ['Parlamentar', 'Autoritario', 'Semipresidencial', 'Federal'], 'Autoritario', 12, 160),
            $this->mcq('Que termo descreve aumento de producao sem aumento proporcional de fatores?', ['Produtividade', 'Elasticidade', 'Paridade', 'Subsidiariedade'], 'Produtividade', 13, 170),
            $this->mcq('Qual pais tem sistema politico conhecido como confederacao com democracia direta forte?', ['Suica', 'Italia', 'Grecia', 'Irlanda'], 'Suica', 15, 190),
            $this->mcq('O que mede o IDH?', ['Reservas cambiais', 'Desenvolvimento humano', 'Defice publico', 'Taxa de juros'], 'Desenvolvimento humano', 12, 160),
            $this->mcq('Que imposto incide sobre rendimento das pessoas singulares em Portugal?', ['IRC', 'IRS', 'IVA', 'ISV'], 'IRS', 11, 150),
            $this->mcq('Qual conceito politico descreve legitimidade baseada no voto popular?', ['Hereditariedade', 'Sufragio', 'Teocracia', 'Patrimonialismo'], 'Sufragio', 13, 170),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function mixBossQuestions(): array
    {
        return [
            $this->mcq('Qual cientista formulou as equacoes que unificam eletricidade e magnetismo?', ['Faraday', 'Maxwell', 'Ampere', 'Gauss'], 'Maxwell', 18, 260),
            $this->mcq('Qual e o unico continente sem desertos quentes?', ['Europa', 'Asia', 'America do Sul', 'Africa'], 'Europa', 18, 230),
            $this->mcq('Qual obra abre com "Todas as familias felizes se parecem"?', ['Crime e Castigo', 'Anna Karenina', 'Os Irmaos Karamazov', 'Guerra e Paz'], 'Anna Karenina', 20, 280),
            $this->mcq('Quem marcou o "Hand of God" no Mundial de 1986?', ['Pele', 'Maradona', 'Zico', 'Romario'], 'Maradona', 14, 170),
            $this->mcq('Qual tratado criou oficialmente a Uniao Europeia em 1993?', ['Tratado de Roma', 'Tratado de Nice', 'Tratado de Maastricht', 'Tratado de Lisboa'], 'Tratado de Maastricht', 20, 260),
            $this->mcq('Qual e a capital do Canada?', ['Toronto', 'Montreal', 'Ottawa', 'Vancouver'], 'Ottawa', 12, 160),
            $this->mcq('Quem escreveu "A Metamorfose"?', ['Kafka', 'Camus', 'Sartre', 'Mann'], 'Kafka', 15, 190),
            $this->mcq('Qual pais venceu a Copa America de 2021?', ['Argentina', 'Brasil', 'Uruguai', 'Chile'], 'Argentina', 13, 170),
            $this->mcq('Que orgao celular e responsavel pela producao de energia (ATP)?', ['Nucleo', 'Mitocondria', 'Ribossoma', 'Lisossoma'], 'Mitocondria', 14, 180),
            $this->mcq('Qual e o maior osso do corpo humano?', ['Femur', 'Tibia', 'Umero', 'Radio'], 'Femur', 12, 160),
            $this->mcq('Qual escritor criou Sherlock Holmes?', ['Agatha Christie', 'Arthur Conan Doyle', 'Edgar Allan Poe', 'G. K. Chesterton'], 'Arthur Conan Doyle', 14, 180),
            $this->mcq('Em que pais fica o salar de Uyuni?', ['Peru', 'Chile', 'Bolivia', 'Argentina'], 'Bolivia', 15, 190),
            $this->mcq('Qual e a moeda oficial do Reino Unido?', ['Euro', 'Libra esterlina', 'Dolar', 'Franco'], 'Libra esterlina', 10, 140),
            $this->mcq('Quem pintou "Guernica"?', ['Miro', 'Dali', 'Picasso', 'Goya'], 'Picasso', 13, 170),
            $this->mcq('Qual e a unidade SI de frequencia?', ['Newton', 'Hertz', 'Pascal', 'Watt'], 'Hertz', 11, 150),
            $this->mcq('Que pais sediou os Jogos Olimpicos de 2016?', ['China', 'Japao', 'Brasil', 'Reino Unido'], 'Brasil', 12, 160),
            $this->mcq('Quem escreveu "Ensaio sobre a Cegueira"?', ['Saramago', 'Lobo Antunes', 'Pessoa', 'Mia Couto'], 'Saramago', 14, 180),
            $this->mcq('Qual e o metal liquido a temperatura ambiente?', ['Mercurio', 'Ferro', 'Aluminio', 'Cobre'], 'Mercurio', 12, 160),
            $this->mcq('Qual pais tem a cidade de Casablanca?', ['Argelia', 'Marrocos', 'Tunisia', 'Egito'], 'Marrocos', 13, 170),
            $this->mcq('Qual e o principal idioma oficial do Brasil?', ['Espanhol', 'Portugues', 'Ingles', 'Frances'], 'Portugues', 9, 130),
            $this->mcq('Qual atleta venceu 9 medalhas de ouro olimpicas no sprint?', ['Carl Lewis', 'Usain Bolt', 'Justin Gatlin', 'Maurice Greene'], 'Usain Bolt', 15, 190),
            $this->mcq('Qual e a formula quimica da agua oxigenada?', ['H2O', 'H2O2', 'HO2', 'O2H2'], 'H2O2', 14, 180),
            $this->mcq('Qual autor escreveu "A Peste"?', ['Camus', 'Sartre', 'Beckett', 'Ionesco'], 'Camus', 14, 180),
            $this->mcq('Qual pais tem o maior PIB nominal do mundo?', ['China', 'Estados Unidos', 'Japao', 'Alemanha'], 'Estados Unidos', 13, 170),
            $this->mcq('Qual e o nome do satelite natural de Marte maior em tamanho?', ['Fobos', 'Deimos', 'Io', 'Europa'], 'Fobos', 16, 200),
            $this->mcq('Qual clube ganhou a Liga dos Campeoes 2022-23?', ['Inter', 'Manchester City', 'Real Madrid', 'PSG'], 'Manchester City', 14, 180),
            $this->mcq('Qual e a capital da Turquia?', ['Istambul', 'Ankara', 'Izmir', 'Bursa'], 'Ankara', 12, 160),
            $this->mcq('Quem compoz a 9a Sinfonia "Coral"?', ['Mozart', 'Haydn', 'Beethoven', 'Schubert'], 'Beethoven', 15, 190),
            $this->mcq('Qual e o maior mamifero do planeta?', ['Elefante africano', 'Baleia-azul', 'Cachalote', 'Girafa'], 'Baleia-azul', 12, 160),
            $this->mcq('Qual pais tem o parlamento chamado Dieta Nacional?', ['Coreia do Sul', 'Japao', 'Tailandia', 'Filipinas'], 'Japao', 15, 190),
        ];
    }
}
