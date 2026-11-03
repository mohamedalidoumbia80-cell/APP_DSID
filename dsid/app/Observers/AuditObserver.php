<?php

namespace App\Observers;

use App\Models\JournalAction;
use Illuminate\Database\Eloquent\Model;

class AuditObserver
{
    public function created(Model $model): void
    {
        $this->record($model, 'creation', null, $this->safeAttributes($model->getAttributes()));
    }

    public function updated(Model $model): void
    {
        $changes = $this->safeAttributes($model->getChanges());
        $before = [];

        foreach (array_keys($changes) as $key) {
            $before[$key] = $model->getRawOriginal($key);
        }

        $this->record($model, 'modification', $before, $changes);
    }

    public function deleted(Model $model): void
    {
        $this->record($model, 'suppression_ou_archivage', $this->safeAttributes($model->getAttributes()), null);
    }

    private function record(Model $model, string $action, ?array $before, ?array $after): void
    {
        JournalAction::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $model::class,
            'subject_id' => $model->getKey(),
            'before' => $before,
            'after' => $after,
            'ip_address' => app()->runningInConsole() ? null : request()->ip(),
            'created_at' => now(),
        ]);
    }

    private function safeAttributes(array $attributes): array
    {
        unset($attributes['password'], $attributes['remember_token'], $attributes['two_factor_secret'], $attributes['two_factor_recovery_codes']);

        return $attributes;
    }
}
