import { Head } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';
import { useLiveReload } from '@/hooks/use-live-reload';

type SessionPayload = {
    id: number;
    code: string;
    state: string;
    started_at: string | null;
};

type QuestionPayload = {
    id: number;
    type: 'multiple_choice' | 'open_text' | 'blur_image' | 'audio';
    prompt: string;
    options: string[] | null;
    media_path: string | null;
    correct_answer: string | null;
    time_limit_seconds: number | null;
    points: number;
};

type LeaderboardItem = {
    id: number;
    nickname: string;
    score: number;
};

export default function QuizDisplayShow({
    session,
    question,
    leaderboard,
    answersCount,
}: {
    session: SessionPayload;
    question: QuestionPayload | null;
    leaderboard: LeaderboardItem[];
    answersCount: number;
}) {
    const isReveal = session.state === 'answers_revealed';
    const isBlurImage = question?.type === 'blur_image' && question.media_path;
    const [nowTick, setNowTick] = useState(() => Date.now());

    useLiveReload({
        only: ['session', 'question', 'leaderboard', 'answersCount'],
        intervalMs: 1000,
        runWhenHidden: true,
    });

    useEffect(() => {
        const timerInterval = setInterval(() => {
            setNowTick(Date.now());
        }, 1000);

        return () => {
            clearInterval(timerInterval);
        };
    }, []);

    const remainingSeconds = useMemo(() => {
        if (!question || !session.started_at || question.time_limit_seconds === null) {
            return null;
        }

        const elapsed = Math.floor((nowTick - new Date(session.started_at).getTime()) / 1000);

        return Math.max(0, question.time_limit_seconds - elapsed);
    }, [nowTick, question, session.started_at]);

    const remainingRatio = useMemo(() => {
        if (!question || question.time_limit_seconds === null || remainingSeconds === null) {
            return null;
        }

        if (question.time_limit_seconds <= 0) {
            return 0;
        }

        return Math.max(0, Math.min(1, remainingSeconds / question.time_limit_seconds));
    }, [question, remainingSeconds]);

    return (
        <>
            <Head title={`Display ${session.code}`} />

            <main className="min-h-screen bg-slate-100 bg-[radial-gradient(circle_at_top,_rgba(14,116,144,0.2),_transparent_55%),radial-gradient(circle_at_bottom,_rgba(251,191,36,0.2),_transparent_50%)] p-4 text-slate-900 md:p-8">
                <div className="mx-auto grid w-full max-w-7xl gap-6 xl:grid-cols-[2fr_1fr]">
                    <section className="space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-xl backdrop-blur">
                        <p className="text-sm font-medium text-cyan-800">
                            Sessão {session.code} · {session.state}
                        </p>

                        {question && remainingSeconds !== null && (
                            <div className="space-y-2">
                                <div className="inline-flex items-center rounded-full border border-cyan-300 bg-cyan-50 px-3 py-1 text-sm font-semibold text-cyan-900">
                                    Tempo restante: {remainingSeconds}s
                                </div>

                                <div className="h-3 w-full overflow-hidden rounded-full bg-slate-200">
                                    <div
                                        className="h-full rounded-full bg-gradient-to-r from-emerald-500 via-amber-400 to-rose-500 transition-all duration-700"
                                        style={{
                                            width: `${(remainingRatio ?? 0) * 100}%`,
                                        }}
                                    />
                                </div>
                            </div>
                        )}

                        {!question && (
                            <h1 className="text-4xl font-semibold">
                                A aguardar próxima pergunta...
                            </h1>
                        )}

                        {question && (
                            <>
                                <h1 className="text-4xl font-semibold leading-tight">
                                    {question.prompt}
                                </h1>

                                <p className="text-sm font-medium text-slate-700">
                                    Pontos base: {question.points}
                                </p>

                                {question.type === 'multiple_choice' && (
                                    <div className="grid gap-3 pt-2 md:grid-cols-2">
                                        {(question.options ?? []).map((option) => (
                                            <div
                                                key={option}
                                                className="rounded-xl border border-cyan-300 bg-cyan-50 px-4 py-3 font-semibold text-cyan-950"
                                            >
                                                {option}
                                            </div>
                                        ))}
                                    </div>
                                )}

                                {isBlurImage && (
                                    <img
                                        src={`/storage/${question.media_path}`}
                                        alt="Pergunta visual"
                                        className="max-h-[380px] w-full rounded-xl object-cover transition-all duration-700"
                                        style={{
                                            filter: isReveal
                                                ? 'blur(0px)'
                                                : 'blur(18px)',
                                        }}
                                    />
                                )}

                                {question.type === 'audio' && question.media_path && (
                                    <audio
                                        className="w-full"
                                        src={`/storage/${question.media_path}`}
                                        controls
                                        autoPlay
                                    />
                                )}

                                {isReveal && question.correct_answer && (
                                    <div className="rounded-xl border border-emerald-300 bg-emerald-50 p-4 text-lg font-semibold text-emerald-900">
                                        Resposta correta: {question.correct_answer}
                                    </div>
                                )}
                            </>
                        )}
                    </section>

                    <aside className="space-y-4">
                        <section className="rounded-2xl border border-slate-200 bg-white p-4 shadow-xl">
                            <h2 className="mb-3 text-lg font-semibold">Leaderboard</h2>

                            <div className="space-y-2">
                                {leaderboard.map((player, index) => (
                                    <div
                                        key={player.id}
                                        className="flex items-center justify-between rounded-lg border border-slate-300 px-3 py-2 text-slate-900"
                                    >
                                        <span>
                                            #{index + 1} {player.nickname}
                                        </span>
                                        <span className="font-semibold">
                                            {player.score}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </section>

                        <section className="rounded-2xl border border-slate-200 bg-white p-4 shadow-xl">
                            <h2 className="mb-3 text-lg font-semibold">
                                Respostas em tempo real
                            </h2>
                            <div className="rounded-lg border border-slate-300 px-3 py-4 text-center">
                                <p className="text-3xl font-bold">{answersCount}</p>
                                <p className="text-sm font-medium text-slate-700">
                                    respostas recebidas
                                </p>
                            </div>
                        </section>
                    </aside>
                </div>
            </main>
        </>
    );
}
