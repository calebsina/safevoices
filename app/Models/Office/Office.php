<?php

namespace App\Models\Office;

use App\Models\User\User;
use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** MINAS unit / regional office. Translatable: name. */
class Office extends Model
{
    use Translatable;

    protected $fillable = ['key', 'region', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
