<?php

namespace App\Http\Requests;

use App\Enums\ActivityStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTacheRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('tache')) ?? false;
    }

    public function rules(): array
    {
        $rules = [
            'status' => ['required', Rule::enum(ActivityStatus::class)],
            'progress' => ['required', 'integer', 'between:0,100'],
        ];

        if (! $this->user()->hasRole('agent')) {
            $rules += [
                'title' => ['required', 'string', 'max:200'],
                'description' => ['nullable', 'string', 'max:10000'],
                'due_date' => ['nullable', 'date'],
                'agent_id' => ['required', 'integer', 'exists:users,id'],
            ];
        }

        return $rules;
    }
}
