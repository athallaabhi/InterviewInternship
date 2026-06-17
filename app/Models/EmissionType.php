<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmissionType extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'formula', 'formula_display', 'unit'];

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class)->orderBy('sort_order');
    }

    public function inputFields(): HasMany
    {
        return $this->hasMany(InputField::class);
    }

    public function coefficients(): HasMany
    {
        return $this->hasMany(Coefficient::class);
    }
}
