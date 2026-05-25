<?php

namespace App\Http\Requests\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status'      => ['nullable', new Enum(TaskStatus::class)],
            'priority'    => ['nullable', new Enum(TaskPriority::class)],
            'due_date'    => ['nullable', 'date', 'after_or_equal:today'],
            'category_id' => ['nullable', 'exists:categories,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'     => 'Title is required',
            'title.min'          => 'Title must be at least 3 characters',
            'due_date.after_or_equal' => 'Due date must be today or in the future',
            'category_id.exists' => 'Category not found',
        ];
    }
}