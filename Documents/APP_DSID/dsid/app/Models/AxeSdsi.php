<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AxeSdsi extends Model
{
    use HasFactory;

    protected $table = 'axes_sdsi';

    protected $fillable = ['title', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function activites(): HasMany
    {
        return $this->hasMany(Activite::class);
    }
}
