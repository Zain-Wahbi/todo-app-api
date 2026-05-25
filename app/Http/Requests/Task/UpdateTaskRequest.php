<?php

namespace App\Http\Requests\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['sometimes', 'string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status'      => ['sometimes', new Enum(TaskStatus::class)],
            'priority'    => ['sometimes', new Enum(TaskPriority::class)],
            'due_date'    => ['nullable', 'date'],
            'category_id' => ['nullable', 'exists:categories,id'],
        ];
    }
}