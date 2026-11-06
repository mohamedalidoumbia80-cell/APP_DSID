<?php

namespace App\Http\Requests;

use App\Models\Tache;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreTacheRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', [Tache::class, $this->route('activite')]) ?? false;
    }

    public function rules(): array
    {
        $activite = $this->route('activite');

        return [
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:10000'],
            'due_date' => ['nullable', 'date', 'after_or_equal:'.$activite->start_date->toDateString()],
            'agent_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $agentId = $this->input('agent_id');
            if ($agentId && ! User::whereKey($agentId)->where('is_active', true)->whereHas('role', fn ($query) => $query->where('name', 'agent'))->exists()) {
                $validator->errors()->add('agent_id', 'Choisissez un agent actif.');
            }
        });
    }
}
