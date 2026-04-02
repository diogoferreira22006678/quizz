<?php

namespace App\Http\Requests\Quiz;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateQuizRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'is_public' => ['nullable', 'boolean'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.id' => ['nullable', 'integer', 'exists:quiz_questions,id'],
            'questions.*.type' => ['required', Rule::in(['multiple_choice', 'open_text', 'blur_image', 'audio'])],
            'questions.*.prompt' => ['required', 'string'],
            'questions.*.options' => ['nullable', 'array'],
            'questions.*.options.*' => ['nullable', 'string', 'max:255'],
            'questions.*.correct_answer' => ['nullable', 'string'],
            'questions.*.media_path' => ['nullable', 'string', 'max:255'],
            'questions.*.media_file' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,gif,mp3,wav,ogg,m4a,aac'],
            'questions.*.time_limit_seconds' => ['nullable', 'integer', 'min:5', 'max:300'],
            'questions.*.points' => ['nullable', 'integer', 'min:10', 'max:10000'],
        ];
    }
}
