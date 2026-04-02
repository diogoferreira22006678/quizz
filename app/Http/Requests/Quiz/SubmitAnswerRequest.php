<?php

namespace App\Http\Requests\Quiz;

use Illuminate\Foundation\Http\FormRequest;

class SubmitAnswerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'quiz_question_id' => ['required', 'integer', 'exists:quiz_questions,id'],
            'answer_text' => ['nullable', 'string'],
            'answer_choice' => ['nullable', 'string', 'max:255'],
        ];
    }
}
