<?php

namespace App\Http\Requests;

use App\Enums\ActivityStatus;
use App\Enums\ActivityType;
use App\Enums\Priority;
use App\Models\Activite;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreActiviteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Activite::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:10000'],
            'type' => ['required', Rule::enum(ActivityType::class)],
            'start_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date', Rule::requiredIf($this->input('type') === ActivityType::PROJET->value)],
            'status' => ['required', Rule::enum(ActivityStatus::class)],
            'progress' => ['sometimes', 'integer', 'between:0,100'],
            'priority' => ['required', Rule::enum(Priority::class)],
            'domaine_id' => ['required', 'integer', 'exists:domaines,id'],
            'responsable_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (in_array($this->input('status'), [ActivityStatus::EN_COURS->value, ActivityStatus::TERMINE->value], true)) {
                $validator->errors()->add('status', 'Une activité doit avoir au moins une tâche avant de démarrer ou d’être terminée.');
            }
        });
    }
}
