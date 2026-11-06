<?php

namespace App\Http\Requests;

use App\Enums\ActivityStatus;
use App\Enums\ActivityType;
use App\Enums\Priority;
use App\Models\Activite;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateActiviteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('activite')) ?? false;
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
            /** @var Activite $activite */
            $activite = $this->route('activite');
            $status = $this->input('status');

            if (in_array($status, [ActivityStatus::EN_COURS->value, ActivityStatus::TERMINE->value], true)
                && $activite->taches()->count() === 0) {
                $validator->errors()->add('status', 'Ajoutez au moins une tâche avant de démarrer ou terminer cette activité.');
            }

            if ($status === ActivityStatus::TERMINE->value
                && $activite->taches()->where('status', '!=', ActivityStatus::TERMINE->value)->exists()) {
                $validator->errors()->add('status', 'Toutes les tâches doivent être terminées avant de clôturer l’activité.');
            }
        });
    }
}
