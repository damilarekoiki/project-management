<?php

namespace App\Http\Requests;

use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectStoreRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|unique:tasks|max:255',
            'description' => 'nullable|string',
            'deadline' => ['nullable', Rule::date()->todayOrAfter()],
            'tasks' => 'array',
            'tasks.*.assignee_id' => 'nullable|integer|exists:users,id',
            'tasks.*.title' => ['nullable', 'string', 'max:255',
                // Rule::unique('tasks')->where(fn (Builder $query) => $query->where('project_id', request()->route('project')))
            ],
            'tasks.*.status' => ['nullable', 'string', Rule::enum(TaskStatus::class)],
            'tasks.*.due_date' => ['nullable', Rule::date()->todayOrAfter()],
        ];
    }
}
