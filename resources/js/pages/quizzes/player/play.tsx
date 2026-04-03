import { Head, router } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';
import type { ChangeEvent, FormEvent } from 'react';
import PlayerQuizController from '@/actions/App/Http/Controllers/Quiz/PlayerQuizController';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useLiveReload } from '@/hooks/use-live-reload';
import { cn } from '@/lib/utils';

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
    time_limit_seconds: number | null;
};

type PlayerPayload = {
    id: number;
    nickname: string;
    score: number;
};

type ExistingAnswerPayload = {
    answer_choice: string | null;
    answer_text: string | null;
    is_correct: boolean | null;
    points_awarded: number;
} | null;

export default function QuizPlayerPlay({
    session,
    question,
    player,
    existingAnswer,
}: {
    session: SessionPayload;
    question: QuestionPayload | null;
    player: PlayerPayload;
    existingAnswer: ExistingAnswerPayload;
}) {
    const hasAnsweredCurrentQuestion = existingAnswer !== null;
    const [answerChoice, setAnswerChoice] = useState(existingAnswer?.answer_choice ?? '');
    const [answerText, setAnswerText] = useState(existingAnswer?.answer_text ?? '');
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [nowTick, setNowTick] = useState(() => Date.now());

    useLiveReload({
        only: ['session', 'question', 'player', 'existingAnswer'],
        intervalMs: 1000,
    });

    useEffect(() => {
        setAnswerChoice(existingAnswer?.answer_choice ?? '');
        setAnswerText(existingAnswer?.answer_text ?? '');
    }, [question?.id, existingAnswer?.answer_choice, existingAnswer?.answer_text]);

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

    const questionHasExpired = remainingSeconds !== null && remainingSeconds <= 0;
    const canAnswerQuestion =
        question !== null &&
        session.state === 'question_live' &&
        !hasAnsweredCurrentQuestion &&
        !questionHasExpired;

    const submitAnswer = (
        payload: {
            quiz_question_id: number;
            answer_choice: string | null;
            answer_text: string | null;
        },
    ) => {
        if (isSubmitting) {
            return;
        }

        setIsSubmitting(true);

        router.post(PlayerQuizController.submitAnswer.url(session.id), payload, {
            preserveScroll: true,
            onFinish: () => {
                setIsSubmitting(false);
            },
        });
    };

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (!question || !canAnswerQuestion || question.type === 'multiple_choice') {
            return;
        }

        submitAnswer({
            quiz_question_id: question.id,
            answer_choice: null,
            answer_text: answerText.trim() === '' ? null : answerText,
        });
    };

    const submitMultipleChoice = (option: string) => {
        if (!question || question.type !== 'multiple_choice' || !canAnswerQuestion) {
            return;
        }

        setAnswerChoice(option);

        submitAnswer({
            quiz_question_id: question.id,
            answer_choice: option,
            answer_text: null,
        });
    };

    return (
        <>
            <Head title={`Quiz ${session.code}`} />

            <main className="mx-auto flex min-h-screen w-full max-w-3xl flex-col gap-6 bg-slate-100 bg-gradient-to-b from-rose-50 to-sky-50 p-4 text-slate-950 md:p-8 dark:bg-slate-950 dark:from-slate-950 dark:to-slate-900 dark:text-slate-100">
                <header className="rounded-xl border border-slate-200 bg-white/90 p-4 dark:border-slate-700 dark:bg-slate-900/90">
                    <p className="text-sm font-medium text-slate-700 dark:text-slate-300">Sessão {session.code}</p>
                    <h1 className="text-xl font-semibold">Olá, {player.nickname}</h1>
                    <p className="text-sm text-slate-700 dark:text-slate-200">Pontuação: {player.score}</p>
                </header>

                {!question && (
                    <section className="rounded-xl border border-slate-200 bg-white/90 p-8 text-center dark:border-slate-700 dark:bg-slate-900/90">
                        <p className="text-lg font-medium">A aguardar próxima pergunta...</p>
                    </section>
                )}

                {question && (
                    <form
                        onSubmit={submit}
                        className="space-y-4 rounded-xl border border-slate-200 bg-white/90 p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900/90"
                    >
                        {remainingSeconds !== null && (
                            <div className="space-y-2">
                                <div className="inline-flex items-center rounded-full border border-cyan-300 bg-cyan-50 px-3 py-1 text-sm font-semibold text-cyan-900 dark:border-cyan-400/50 dark:bg-cyan-950/70 dark:text-cyan-100">
                                    Tempo restante: {remainingSeconds}s
                                </div>

                                <div className="h-2.5 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                    <div
                                        className="h-full rounded-full bg-gradient-to-r from-emerald-500 via-amber-400 to-rose-500 transition-all duration-700"
                                        style={{
                                            width: `${(remainingRatio ?? 0) * 100}%`,
                                        }}
                                    />
                                </div>
                            </div>
                        )}

                        {questionHasExpired && !hasAnsweredCurrentQuestion && (
                            <div className="rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-sm font-medium text-amber-900 dark:border-amber-400/60 dark:bg-amber-950/60 dark:text-amber-100">
                                Tempo esgotado para esta pergunta.
                            </div>
                        )}

                        <h2 className="text-xl font-semibold">{question.prompt}</h2>

                        {question.type === 'multiple_choice' && (
                            <div className="grid gap-3 sm:grid-cols-2">
                                {(question.options ?? []).map((option) => (
                                    <Button
                                        type="button"
                                        key={option}
                                        variant="outline"
                                        className={cn(
                                            'h-auto min-h-11 w-full justify-start whitespace-normal border-2 px-4 py-3 text-left text-base font-semibold',
                                            answerChoice === option
                                                ? 'border-cyan-700 bg-cyan-100 text-cyan-950 dark:border-cyan-300 dark:bg-cyan-950/70 dark:text-cyan-100'
                                                : 'border-slate-300 bg-white text-slate-900 hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800',
                                        )}
                                        disabled={!canAnswerQuestion || isSubmitting}
                                        onClick={() => submitMultipleChoice(option)}
                                    >
                                        {option}
                                    </Button>
                                ))}
                            </div>
                        )}

                        {question.type === 'multiple_choice' && !hasAnsweredCurrentQuestion && (
                            <p className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                Toca numa opção para responder.
                            </p>
                        )}

                        {question.type !== 'multiple_choice' && (
                            <Input
                                value={answerText}
                                onChange={(event: ChangeEvent<HTMLInputElement>) =>
                                    setAnswerText(event.target.value)
                                }
                                placeholder="Escreve a tua resposta"
                                disabled={!canAnswerQuestion || isSubmitting}
                            />
                        )}

                        {hasAnsweredCurrentQuestion && (
                            <div className="rounded-lg border border-emerald-300 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-900 dark:border-emerald-400/60 dark:bg-emerald-950/60 dark:text-emerald-100">
                                {existingAnswer.is_correct
                                    ? 'Resposta correta!'
                                    : 'Resposta registada.'}{' '}
                                Pontos ganhos: {existingAnswer.points_awarded}
                            </div>
                        )}

                        {question.type !== 'multiple_choice' && (
                            <Button
                                type="submit"
                                disabled={!canAnswerQuestion || isSubmitting || answerText.trim() === ''}
                            >
                                Responder
                            </Button>
                        )}
                    </form>
                )}
            </main>
        </>
    );
}
