import { Head, Link, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { useLiveReload } from '@/hooks/use-live-reload';
import { advance } from '@/routes/quizzes/admin/sessions';
import { show as displayShow } from '@/routes/quizzes/display';
import { joinPage as playerJoinPage } from '@/routes/quizzes/player';

type QuizPayload = {
    id: number;
    title: string;
};

type SessionPayload = {
    id: number;
    code: string;
    state: string;
    current_question_position: number;
};

type QuestionPayload = {
    id: number;
    prompt: string;
    time_limit_seconds: number | null;
};

type LeaderboardItem = {
    id: number;
    nickname: string;
    score: number;
};

export default function QuizAdminLive({
    quiz,
    session,
    question,
    answersCount,
    leaderboard,
}: {
    quiz: QuizPayload;
    session: SessionPayload;
    question: QuestionPayload | null;
    answersCount: number;
    leaderboard: LeaderboardItem[];
}) {
    useLiveReload({
        only: ['session', 'question', 'answersCount', 'leaderboard'],
        intervalMs: 1000,
    });

    const advanceSession = (
        action: 'reveal_answers' | 'next_question' | 'finish',
    ) => {
        router.post(advance.url(session.id), { action });
    };

    const isLobby = session.state === 'lobby';
    const isFinished = session.state === 'finished';

    return (
        <>
            <Head title={`Live ${quiz.title}`} />

            <div className="space-y-6 p-4 md:p-6">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-semibold">{quiz.title}</h1>
                        <p className="text-sm text-muted-foreground">
                            Painel ao vivo da sessão
                        </p>
                    </div>

                    <div className="flex flex-wrap gap-2">
                        <Button variant="outline" asChild>
                            <Link href={playerJoinPage()}>Ecrã jogador</Link>
                        </Button>
                        <Button variant="outline" asChild>
                            <Link href={displayShow({ session: session.id })}>
                                Ecrã display
                            </Link>
                        </Button>
                    </div>
                </div>

                <section className="rounded-xl border bg-card p-5">
                    <div className="grid gap-3 md:grid-cols-4">
                        <p>
                            Sessão: <strong>{session.code}</strong>
                        </p>
                        <p>
                            Estado: <strong>{session.state}</strong>
                        </p>
                        <p>
                            Pergunta: <strong>#{session.current_question_position}</strong>
                        </p>
                        <p>
                            Respostas: <strong>{answersCount}</strong>
                        </p>
                    </div>

                    <p className="mt-3 text-sm text-muted-foreground">
                        {question
                            ? `${question.prompt} ${question.time_limit_seconds ? `(${question.time_limit_seconds}s)` : ''}`
                            : 'Sem pergunta ativa.'}
                    </p>

                    <div className="mt-4 flex flex-wrap gap-2">
                        {isLobby ? (
                            <Button
                                type="button"
                                onClick={() => advanceSession('next_question')}
                            >
                                Começar quiz
                            </Button>
                        ) : (
                            <>
                                <Button
                                    variant="outline"
                                    type="button"
                                    onClick={() => advanceSession('reveal_answers')}
                                    disabled={isFinished}
                                >
                                    Mostrar respostas
                                </Button>
                                <Button
                                    type="button"
                                    onClick={() => advanceSession('next_question')}
                                    disabled={isFinished}
                                >
                                    Próxima pergunta
                                </Button>
                            </>
                        )}
                        <Button
                            variant="destructive"
                            type="button"
                            onClick={() => advanceSession('finish')}
                            disabled={isFinished}
                        >
                            Terminar sessão
                        </Button>
                    </div>
                </section>

                <section className="rounded-xl border bg-card p-5">
                    <h2 className="text-lg font-semibold">Leaderboard</h2>
                    <div className="mt-3 grid gap-2 md:grid-cols-2">
                        {leaderboard.map((player, index) => (
                            <div
                                key={player.id}
                                className="flex items-center justify-between rounded-lg border px-3 py-2"
                            >
                                <span>
                                    #{index + 1} {player.nickname}
                                </span>
                                <span className="font-semibold">{player.score}</span>
                            </div>
                        ))}
                    </div>
                </section>
            </div>
        </>
    );
}

QuizAdminLive.layout = {
    breadcrumbs: [
        {
            title: 'Quizzes',
            href: '/quizzes/admin',
        },
        {
            title: 'Painel ao vivo',
            href: '/quizzes/admin',
        },
    ],
};
