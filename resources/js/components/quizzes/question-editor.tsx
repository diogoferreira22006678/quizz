import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

export type QuizQuestionInput = {
    id?: number;
    type: 'multiple_choice' | 'open_text' | 'blur_image' | 'audio';
    prompt: string;
    options: string[];
    correct_answer: string;
    media_path: string | null;
    media_file: File | null;
    time_limit_seconds: number;
    points: number;
};

type Props = {
    questions: QuizQuestionInput[];
    onChange: (questions: QuizQuestionInput[]) => void;
};

const QUESTION_TYPES: Array<QuizQuestionInput['type']> = [
    'multiple_choice',
    'open_text',
    'blur_image',
    'audio',
];

export default function QuestionEditor({ questions, onChange }: Props) {
    const addQuestion = () => {
        onChange([
            ...questions,
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
        ]);
    };

    const updateQuestion = (index: number, patch: Partial<QuizQuestionInput>) => {
        onChange(
            questions.map((question, current) =>
                current === index ? { ...question, ...patch } : question,
            ),
        );
    };

    const removeQuestion = (index: number) => {
        onChange(questions.filter((_, current) => current !== index));
    };

    const updateOption = (
        questionIndex: number,
        optionIndex: number,
        value: string,
    ) => {
        const question = questions[questionIndex];
        const options = [...question.options];
        options[optionIndex] = value;

        updateQuestion(questionIndex, { options });
    };

    const mediaAcceptByType = (type: QuizQuestionInput['type']): string => {
        if (type === 'audio') {
            return 'audio/*';
        }

        if (type === 'blur_image') {
            return 'image/*';
        }

        return 'image/*,audio/*';
    };

    return (
        <div className="space-y-6">
            {questions.map((question, index) => (
                <section
                    key={question.id ?? `new-${index}`}
                    className="space-y-4 rounded-xl border p-5"
                >
                    <div className="flex items-center justify-between">
                        <h3 className="text-sm font-semibold">
                            Pergunta {index + 1}
                        </h3>

                        <Button
                            type="button"
                            variant="ghost"
                            onClick={() => removeQuestion(index)}
                            disabled={questions.length === 1}
                        >
                            Remover
                        </Button>
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor={`question-${index}-type`}>
                            Tipo de pergunta
                        </Label>
                        <select
                            id={`question-${index}-type`}
                            className="h-9 rounded-md border bg-background px-3 text-sm"
                            value={question.type}
                            onChange={(event) => {
                                const nextType =
                                    event.target.value as QuizQuestionInput['type'];
                                updateQuestion(index, {
                                    type: nextType,
                                    options:
                                        nextType === 'multiple_choice'
                                            ? question.options.length > 0
                                                ? question.options
                                                : ['', '', '', '']
                                            : [],
                                });
                            }}
                        >
                            {QUESTION_TYPES.map((type) => (
                                <option key={type} value={type}>
                                    {type}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor={`question-${index}-prompt`}>
                            Pergunta
                        </Label>
                        <Input
                            id={`question-${index}-prompt`}
                            value={question.prompt}
                            onChange={(event) =>
                                updateQuestion(index, {
                                    prompt: event.target.value,
                                })
                            }
                            placeholder="Escreve a pergunta"
                        />
                    </div>

                    {question.type === 'multiple_choice' && (
                        <div className="grid gap-3 md:grid-cols-2">
                            {question.options.map((option, optionIndex) => (
                                <div key={`${index}-${optionIndex}`} className="grid gap-2">
                                    <Label htmlFor={`question-${index}-option-${optionIndex}`}>
                                        Opção {optionIndex + 1}
                                    </Label>
                                    <Input
                                        id={`question-${index}-option-${optionIndex}`}
                                        value={option}
                                        onChange={(event) =>
                                            updateOption(
                                                index,
                                                optionIndex,
                                                event.target.value,
                                            )
                                        }
                                    />
                                </div>
                            ))}
                        </div>
                    )}

                    <div className="grid gap-2">
                        <Label htmlFor={`question-${index}-answer`}>
                            Resposta correta
                        </Label>
                        <Input
                            id={`question-${index}-answer`}
                            value={question.correct_answer}
                            onChange={(event) =>
                                updateQuestion(index, {
                                    correct_answer: event.target.value,
                                })
                            }
                            placeholder="Resposta certa"
                        />
                    </div>

                    <div className="grid gap-4 md:grid-cols-3">
                        <div className="grid gap-2">
                            <Label htmlFor={`question-${index}-media`}>
                                Upload de media (opcional)
                            </Label>
                            <Input
                                id={`question-${index}-media`}
                                type="file"
                                accept={mediaAcceptByType(question.type)}
                                onChange={(event) =>
                                    updateQuestion(index, {
                                        media_file:
                                            event.target.files?.[0] ?? null,
                                    })
                                }
                            />
                            {question.media_path && (
                                <p className="text-xs text-muted-foreground">
                                    Ficheiro atual: {question.media_path}
                                </p>
                            )}
                            {question.media_file && (
                                <p className="text-xs text-muted-foreground">
                                    Novo ficheiro: {question.media_file.name}
                                </p>
                            )}
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor={`question-${index}-timer`}>
                                Tempo (seg)
                            </Label>
                            <Input
                                id={`question-${index}-timer`}
                                type="number"
                                min={5}
                                max={300}
                                value={question.time_limit_seconds}
                                onChange={(event) =>
                                    updateQuestion(index, {
                                        time_limit_seconds:
                                            Number(event.target.value) || 20,
                                    })
                                }
                            />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor={`question-${index}-points`}>
                                Pontos
                            </Label>
                            <Input
                                id={`question-${index}-points`}
                                type="number"
                                min={10}
                                max={10000}
                                value={question.points}
                                onChange={(event) =>
                                    updateQuestion(index, {
                                        points: Number(event.target.value) || 100,
                                    })
                                }
                            />
                        </div>
                    </div>
                </section>
            ))}

            <Button type="button" variant="outline" onClick={addQuestion}>
                Adicionar pergunta
            </Button>
        </div>
    );
}
