import { Head, useForm } from '@inertiajs/react';
import { useEffect } from 'react';
import PlayerQuizController from '@/actions/App/Http/Controllers/Quiz/PlayerQuizController';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useLiveReload } from '@/hooks/use-live-reload';

type SessionPayload = {
    id: number;
    code: string;
    state: string;
};

type QuestionPayload = {
    id: number;
    type: 'multiple_choice' | 'open_text' | 'blur_image' | 'audio';
    prompt: string;
    options: string[] | null;
    media_path: string | null;
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

    useLiveReload({
        only: ['session', 'question', 'player', 'existingAnswer'],
        intervalMs: 1000,
    });

    const { data, setData, post, processing } = useForm({
        quiz_question_id: question?.id ?? 0,
        answer_choice: existingAnswer?.answer_choice ?? '',
        answer_text: existingAnswer?.answer_text ?? '',
    });

    useEffect(() => {
        setData('quiz_question_id', question?.id ?? 0);
        setData('answer_choice', existingAnswer?.answer_choice ?? '');
        setData('answer_text', existingAnswer?.answer_text ?? '');
    }, [question?.id, existingAnswer?.answer_choice, existingAnswer?.answer_text, setData]);

    const submit = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (!question) {
            return;
        }

        post(PlayerQuizController.submitAnswer.url(session.id));
    };

    return (
        <>
            <Head title={`Quiz ${session.code}`} />

            <main className="mx-auto flex min-h-screen w-full max-w-3xl flex-col gap-6 bg-gradient-to-b from-rose-50 to-sky-50 p-4 md:p-8">
                <header className="rounded-xl border bg-white/90 p-4">
                    <p className="text-sm text-muted-foreground">Sessão {session.code}</p>
                    <h1 className="text-xl font-semibold">Olá, {player.nickname}</h1>
                    <p className="text-sm">Pontuação: {player.score}</p>
                </header>

                {!question && (
                    <section className="rounded-xl border bg-white/90 p-8 text-center">
                        <p className="text-lg font-medium">A aguardar próxima pergunta...</p>
                    </section>
                )}

                {question && (
                    <form
                        onSubmit={submit}
                        className="space-y-4 rounded-xl border bg-white/90 p-6"
                    >
                        <h2 className="text-xl font-semibold">{question.prompt}</h2>

                        {question.type === 'multiple_choice' && (
                            <div className="grid gap-3 sm:grid-cols-2">
                                {(question.options ?? []).map((option) => (
                                    <Button
                                        type="button"
                                        key={option}
                                        variant={
                                            data.answer_choice === option
                                                ? 'default'
                                                : 'outline'
                                        }
                                        disabled={hasAnsweredCurrentQuestion}
                                        onClick={() =>
                                            setData('answer_choice', option)
                                        }
                                    >
                                        {option}
                                    </Button>
                                ))}
                            </div>
                        )}

                        {question.type !== 'multiple_choice' && (
                            <Input
                                value={data.answer_text}
                                onChange={(event) =>
                                    setData('answer_text', event.target.value)
                                }
                                placeholder="Escreve a tua resposta"
                                disabled={hasAnsweredCurrentQuestion}
                            />
                        )}

                        {hasAnsweredCurrentQuestion && (
                            <div className="rounded-lg border border-emerald-300 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
                                {existingAnswer.is_correct
                                    ? 'Resposta correta!'
                                    : 'Resposta registada.'}{' '}
                                Pontos ganhos: {existingAnswer.points_awarded}
                            </div>
                        )}

                        <input
                            type="hidden"
                            name="quiz_question_id"
                            value={data.quiz_question_id}
                        />

                        <Button
                            type="submit"
                            disabled={
                                processing ||
                                session.state !== 'question_live' ||
                                hasAnsweredCurrentQuestion
                            }
                        >
                            Responder
                        </Button>
                    </form>
                )}
            </main>
        </>
    );
}
