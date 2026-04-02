import { Head, useForm } from '@inertiajs/react';
import PlayerQuizController from '@/actions/App/Http/Controllers/Quiz/PlayerQuizController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

export default function QuizPlayerJoin() {
    const { data, setData, post, processing, errors } = useForm({
        code: '',
        nickname: '',
    });

    const submit = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        post(PlayerQuizController.join.url());
    };

    return (
        <>
            <Head title="Entrar no Quiz" />

            <main className="grid min-h-screen place-items-center bg-gradient-to-b from-amber-50 via-white to-cyan-50 p-6">
                <form
                    onSubmit={submit}
                    className="w-full max-w-md space-y-5 rounded-2xl border bg-white/90 p-6 shadow-xl"
                >
                    <div>
                        <h1 className="text-2xl font-semibold">Entrar no quiz</h1>
                        <p className="text-sm text-muted-foreground">
                            Insere o código da sessão e o teu nome.
                        </p>
                    </div>

                    <div className="space-y-2">
                        <Input
                            value={data.code}
                            onChange={(event) =>
                                setData('code', event.target.value.toUpperCase())
                            }
                            placeholder="Código (8 chars)"
                        />
                        <InputError message={errors.code} />
                    </div>

                    <div className="space-y-2">
                        <Input
                            value={data.nickname}
                            onChange={(event) =>
                                setData('nickname', event.target.value)
                            }
                            placeholder="Nickname"
                        />
                        <InputError message={errors.nickname} />
                    </div>

                    <Button type="submit" className="w-full" disabled={processing}>
                        Entrar
                    </Button>
                </form>
            </main>
        </>
    );
}
