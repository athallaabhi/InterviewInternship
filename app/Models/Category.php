<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['emission_type_id', 'name', 'display_name', 'sort_order'];

    public function emissionType(): BelongsTo
    {
        return $this->belongsTo(EmissionType::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(CategoryValue::class);
    }
}
