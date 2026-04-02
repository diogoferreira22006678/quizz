import { Head, useForm } from '@inertiajs/react';
import AdminQuizController from '@/actions/App/Http/Controllers/Quiz/AdminQuizController';
import QuestionEditor, {
    type QuizQuestionInput,
} from '@/components/quizzes/question-editor';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type QuizFormData = {
    title: string;
    description: string;
    status: 'draft' | 'published' | 'archived';
    is_public: boolean;
    questions: QuizQuestionInput[];
};

export default function QuizAdminCreate() {
    const { data, setData, post, processing, errors } = useForm<QuizFormData>({
        title: '',
        description: '',
        status: 'draft',
        is_public: false,
        questions: [
            {
                type: 'multiple_choice',
                prompt: '',
                options: ['', '', '', ''],
                correct_answer: '',
                media_path: null,
                media_file: null,
                time_limit_seconds: 20,
                points: 100,
            },
        ],
    });

    const submit = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        post(AdminQuizController.store.url(), {
            forceFormData: true,
        });
    };

    return (
        <>
            <Head title="Novo Quiz" />

            <form onSubmit={submit} className="space-y-6 p-4 md:p-6">
                <div>
                    <h1 className="text-2xl font-semibold">Criar novo quiz</h1>
                    <p className="text-sm text-muted-foreground">
                        Define perguntas, respostas corretas e media.
                    </p>
                </div>

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

                <Button type="submit" disabled={processing}>
                    Guardar quiz
                </Button>
            </form>
        </>
    );
}

QuizAdminCreate.layout = {
    breadcrumbs: [
        {
            title: 'Quizzes',
            href: AdminQuizController.index(),
        },
        {
            title: 'Novo',
            href: AdminQuizController.create(),
        },
    ],
};
