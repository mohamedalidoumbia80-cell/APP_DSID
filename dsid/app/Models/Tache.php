<?php

namespace App\Models;

use App\Enums\ActivityStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tache extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'taches';

    protected $fillable = ['title', 'description', 'due_date', 'status', 'progress', 'activite_id', 'agent_id', 'created_by'];

    protected function casts(): array
    {
        return ['status' => ActivityStatus::class, 'due_date' => 'date', 'progress' => 'integer'];
    }

    public function activite(): BelongsTo
    {
        return $this->belongsTo(Activite::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeEnRetard(Builder $query): Builder
    {
        return $query->whereDate('due_date', '<', today())
            ->whereNotIn('status', [ActivityStatus::TERMINE->value, ActivityStatus::ANNULE->value]);
    }

    public function getIsLateAttribute(): bool
    {
        return $this->due_date !== null
            && $this->due_date->isBefore(today())
            && ! in_array($this->status, [ActivityStatus::TERMINE, ActivityStatus::ANNULE], true);
    }
}
