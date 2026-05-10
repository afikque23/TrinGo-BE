<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MotorType extends Model
{
    protected $fillable = ['name', 'slug', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function componentConfigs(): HasMany
    {
        return $this->hasMany(ComponentConfig::class);
    }

    public function activeComponents(): HasMany
    {
        return $this->hasMany(ComponentConfig::class)->where('is_active', true);
    }
}
