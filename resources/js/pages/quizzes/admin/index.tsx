import { Form, Head, Link } from '@inertiajs/react';
import AdminQuizController from '@/actions/App/Http/Controllers/Quiz/AdminQuizController';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { show as showAdminSession } from '@/routes/quizzes/admin/sessions';

type QuizListItem = {
    id: number;
    title: string;
    status: string;
    access_code: string;
    questions_count: number;
    sessions_count: number;
    is_public: boolean;
    latest_session: {
        id: number;
        code: string;
        state: string;
    } | null;
};

export default function QuizAdminIndex({
    quizzes,
}: {
    quizzes: QuizListItem[];
}) {
    return (
        <>
            <Head title="Quiz Admin" />

            <div className="space-y-6 p-4 md:p-6">
                <div className="flex items-center justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-semibold">Quiz Dashboard</h1>
                        <p className="text-sm text-muted-foreground">
                            Cria, lança e gere sessões de quiz em tempo real.
                        </p>
                    </div>

                    <Button asChild>
                        <Link href={AdminQuizController.create()}>Novo quiz</Link>
                    </Button>
                </div>

                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    {quizzes.map((quiz) => (
                        <Card key={quiz.id}>
                            <CardHeader>
                                <CardTitle>{quiz.title}</CardTitle>
                                <CardDescription>
                                    Código {quiz.access_code} · {quiz.status}
                                </CardDescription>
                            </CardHeader>

                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-2 gap-3 text-sm">
                                    <div className="rounded-lg border p-3">
                                        <p className="text-muted-foreground">Perguntas</p>
                                        <p className="text-lg font-semibold">
                                            {quiz.questions_count}
                                        </p>
                                    </div>
                                    <div className="rounded-lg border p-3">
                                        <p className="text-muted-foreground">Sessões</p>
                                        <p className="text-lg font-semibold">
                                            {quiz.sessions_count}
                                        </p>
                                    </div>
                                </div>

                                <div className="flex flex-wrap gap-2">
                                    <Button variant="outline" asChild>
                                        <Link href={AdminQuizController.edit(quiz.id)}>
                                            Editar
                                        </Link>
                                    </Button>

                                    <Form
                                        {...AdminQuizController.startSession.form(
                                            quiz.id,
                                        )}
                                    >
                                        <input
                                            type="hidden"
                                            name="start_immediately"
                                            value="0"
                                        />
                                        <Button type="submit">Criar sessão</Button>
                                    </Form>

                                    <Form
                                        {...AdminQuizController.destroy.form(
                                            quiz.id,
                                        )}
                                    >
                                        <Button variant="destructive" type="submit">
                                            Apagar
                                        </Button>
                                    </Form>
                                </div>

                                {quiz.latest_session && (
                                    <div className="rounded-lg border bg-muted/30 p-3 text-sm">
                                        <p>
                                            Sessão: <strong>{quiz.latest_session.code}</strong>
                                        </p>
                                        <p>
                                            ID: <strong>{quiz.latest_session.id}</strong> · Estado:{' '}
                                            {quiz.latest_session.state}
                                        </p>

                                        <div className="mt-2">
                                            <Button size="sm" variant="outline" asChild>
                                                <Link href={showAdminSession({ session: quiz.latest_session.id })}>
                                                    Gerir sessão ao vivo
                                                </Link>
                                            </Button>
                                        </div>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    ))}
                </div>
            </div>
        </>
    );
}

QuizAdminIndex.layout = {
    breadcrumbs: [
        {
            title: 'Quizzes',
            href: AdminQuizController.index(),
        },
    ],
};
