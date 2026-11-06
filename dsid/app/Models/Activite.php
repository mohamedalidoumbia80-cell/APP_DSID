<?php

namespace App\Models;

use App\Enums\ActivityStatus;
use App\Enums\ActivityType;
use App\Enums\Priority;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activite extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'activites';

    protected $fillable = [
        'title', 'description', 'type', 'start_date', 'due_date', 'status',
        'progress', 'priority', 'domaine_id', 'responsable_id', 'axe_sdsi_id', 'validated_at', 'validated_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => ActivityType::class,
            'status' => ActivityStatus::class,
            'priority' => Priority::class,
            'start_date' => 'date',
            'due_date' => 'date',
            'progress' => 'integer',
            'validated_at' => 'datetime',
        ];
    }

    public function domaine(): BelongsTo
    {
        return $this->belongsTo(Domaine::class);
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function axeSdsi(): BelongsTo
    {
        return $this->belongsTo(AxeSdsi::class);
    }

    public function taches(): HasMany
    {
        return $this->hasMany(Tache::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function commentaires(): HasMany
    {
        return $this->hasMany(Commentaire::class);
    }

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function getIsValidatedAttribute(): bool
    {
        return $this->validated_at !== null;
    }

    public function scopeEnRetard(Builder $query): Builder
    {
        return $query->whereDate('due_date', '<', today())
            ->whereNotIn('status', [ActivityStatus::TERMINE->value, ActivityStatus::ANNULE->value]);
    }

    public function synchroniserAvancement(): void
    {
        $count = $this->taches()->count();
        $progress = $count === 0 ? 0 : (int) round((float) $this->taches()->avg('progress'));

        if ($this->progress !== $progress) {
            $this->update(['progress' => $progress]);
        }
    }

    public function getIsLateAttribute(): bool
    {
        return $this->due_date !== null
            && $this->due_date->isBefore(today())
            && ! in_array($this->status, [ActivityStatus::TERMINE, ActivityStatus::ANNULE], true);
    }
}
