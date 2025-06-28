<?php

namespace App\Http\Requests;

use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectTaskUpdateRequest extends FormRequest
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
            //
            'tasks' => 'required|array|min:1',
            'tasks.*.id' => 'integer|exists:tasks,id',
            'tasks.*.assignee_id' => 'nullable|integer|exists:users,id',
            'tasks.*.title' => ['required', 'string', 'max:255'],
            'tasks.*.status' => ['nullable', 'string', Rule::enum(TaskStatus::class)],
            'tasks.*.due_date' => ['nullable', Rule::date()->todayOrAfter()],
        ];
    }
}
