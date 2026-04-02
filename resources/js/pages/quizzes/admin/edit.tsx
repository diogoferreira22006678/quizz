import { Head, Link, router, useForm } from '@inertiajs/react';
import AdminQuizController from '@/actions/App/Http/Controllers/Quiz/AdminQuizController';
import QuestionEditor, {
    type QuizQuestionInput,
} from '@/components/quizzes/question-editor';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { show as displayShow } from '@/routes/quizzes/display';
import { joinPage as playerJoinPage } from '@/routes/quizzes/player';

type QuizDetails = {
    id: number;
    title: string;
    description: string | null;
    status: 'draft' | 'published' | 'archived';
    is_public: boolean;
    questions: Array<
        QuizQuestionInput & {
            id: number;
        }
    >;
    latestSession: {
        id: number;
        code: string;
        state: string;
        current_question_position: number;
        current_question: {
            id: number;
            prompt: string;
        } | null;
    } | null;
};

type QuizFormData = {
    title: string;
    description: string;
    status: 'draft' | 'published' | 'archived';
    is_public: boolean;
    questions: QuizQuestionInput[];
};

export default function QuizAdminEdit({ quiz }: { quiz: QuizDetails }) {
    const { data, setData, put, processing, errors } = useForm<QuizFormData>({
        title: quiz.title,
        description: quiz.description ?? '',
        status: quiz.status,
        is_public: quiz.is_public,
        questions: quiz.questions.map((question) => ({
            id: question.id,
            type: question.type,
            prompt: question.prompt,
            options: question.options ?? [],
            correct_answer: question.correct_answer ?? '',
            media_path: question.media_path ?? null,
            media_file: null,
            time_limit_seconds: question.time_limit_seconds ?? 20,
            points: question.points ?? 100,
        })),
    });

    const submit = () => {
        put(AdminQuizController.update.url(quiz.id), {
            forceFormData: true,
        });
    };

    const startSession = () => {
        router.post(AdminQuizController.startSession.url(quiz.id), {
            start_immediately: false,
        });
    };

    const advanceSession = (
        sessionId: number,
        action: 'reveal_answers' | 'next_question' | 'finish',
    ) => {
        router.post(AdminQuizController.advance.url(sessionId), {
            action,
        });
    };

    return (
        <>
            <Head title={`Editar ${quiz.title}`} />

            <div className="space-y-6 p-4 md:p-6">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-semibold">{quiz.title}</h1>
                        <p className="text-sm text-muted-foreground">
                            Atualiza perguntas e gere sessões ao vivo.
                        </p>
                    </div>

                    <div className="flex gap-2">
                        <Button
                            type="button"
                            onClick={startSession}
                        >
                            Criar sessão
                        </Button>
                    </div>
                </div>

                {quiz.latestSession && (
                    <section className="rounded-xl border border-emerald-300 bg-emerald-50/60 p-4">
                        <h2 className="text-sm font-semibold uppercase tracking-wide text-emerald-800">
                            Sessão ativa
                        </h2>
                        <div className="mt-2 grid gap-2 text-sm md:grid-cols-3">
                            <p>
                                Código: <strong>{quiz.latestSession.code}</strong>
                            </p>
                            <p>
                                ID: <strong>{quiz.latestSession.id}</strong>
                            </p>
                            <p>
                                Estado: <strong>{quiz.latestSession.state}</strong>
                            </p>
                        </div>

                        <p className="mt-2 text-sm text-emerald-900">
                            Pergunta atual: #{quiz.latestSession.current_question_position}{' '}
                            {quiz.latestSession.current_question?.prompt ?? 'A definir'}
                        </p>
                        <div className="mt-3 flex flex-wrap gap-2">
                            <Button variant="outline" asChild>
                                <Link href={playerJoinPage()}>
                                    Abrir ecrã jogador (usar código acima)
                                </Link>
                            </Button>
                            <Button variant="outline" asChild>
                                <Link href={displayShow({ session: quiz.latestSession.id })}>
                                    Abrir display da sessão
                                </Link>
                            </Button>
                        </div>

                        <div className="mt-3 flex flex-wrap gap-2">
                            <Button
                                variant="outline"
                                type="button"
                                onClick={() =>
                                    advanceSession(
                                        quiz.latestSession!.id,
                                        'reveal_answers',
                                    )
                                }
                            >
                                Mostrar respostas
                            </Button>

                            <Button
                                type="button"
                                onClick={() =>
                                    advanceSession(
                                        quiz.latestSession!.id,
                                        'next_question',
                                    )
                                }
                            >
                                Próxima pergunta
                            </Button>

                            <Button
                                variant="destructive"
                                type="button"
                                onClick={() =>
                                    advanceSession(
                                        quiz.latestSession!.id,
                                        'finish',
                                    )
                                }
                            >
                                Terminar sessão
                            </Button>
                        </div>
                    </section>
                )}

                <div className="grid gap-4 md:grid-cols-2">
                    <div className="grid gap-2">
                        <Label htmlFor="title">Título</Label>
                        <Input
                            id="title"
                            value={data.title}
                            onChange={(event) =>
                                setData('title', event.target.value)
                            }
                        />
                        <InputError message={errors.title} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="status">Estado</Label>
                        <select
                            id="status"
                            className="h-9 rounded-md border bg-background px-3 text-sm"
                            value={data.status}
                            onChange={(event) =>
                                setData(
                                    'status',
                                    event.target.value as QuizFormData['status'],
                                )
                            }
                        >
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="description">Descrição</Label>
                    <textarea
                        id="description"
                        className="min-h-24 rounded-md border bg-background p-3 text-sm"
                        value={data.description}
                        onChange={(event) =>
                            setData('description', event.target.value)
                        }
                    />
                </div>

                <label className="flex items-center gap-2 text-sm">
                    <input
                        type="checkbox"
                        checked={data.is_public}
                        onChange={(event) =>
                            setData('is_public', event.target.checked)
                        }
                    />
                    Quiz público
                </label>

                <QuestionEditor
                    questions={data.questions}
                    onChange={(questions) => setData('questions', questions)}
                />

                <Button type="button" onClick={submit} disabled={processing}>
                    Guardar alterações
                </Button>
            </div>
        </>
    );
}

QuizAdminEdit.layout = {
    breadcrumbs: [
        {
            title: 'Quizzes',
            href: AdminQuizController.index(),
        },
    ],
};
