<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = ['first_name', 'last_name', 'email', 'password', 'role_id', 'service_id', 'is_active', 'email_verified_at'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function getNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function activitesResponsable(): HasMany
    {
        return $this->hasMany(Activite::class, 'responsable_id');
    }

    public function tachesAssignees(): HasMany
    {
        return $this->hasMany(Tache::class, 'agent_id');
    }

    public function documentsDeposes(): HasMany
    {
        return $this->hasMany(Document::class, 'uploaded_by');
    }

    public function hasRole(string ...$roles): bool
    {
        return $this->role !== null && in_array($this->role->name, $roles, true);
    }

    public function canAccessActivite(Activite $activite): bool
    {
        if ($this->hasRole('administrateur', 'directeur', 'lecteur')) {
            return true;
        }

        if ($this->hasRole('chef_service')) {
            return $activite->responsable?->service_id === $this->service_id
                || $activite->taches()->whereHas('agent', fn ($query) => $query->where('service_id', $this->service_id))->exists();
        }

        return $activite->responsable_id === $this->id
            || $activite->taches()->where('agent_id', $this->id)->exists();
    }
}
