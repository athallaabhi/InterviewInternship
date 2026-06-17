<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Coefficient extends Model
{
    protected $fillable = ['emission_type_id', 'name', 'display_name'];

    public function emissionType(): BelongsTo
    {
        return $this->belongsTo(EmissionType::class);
    }

    public function categoryDeps(): HasMany
    {
        return $this->hasMany(CoefficientCategoryDep::class);
    }

    public function dependentCategories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'coefficient_category_deps');
    }

    public function values(): HasMany
    {
        return $this->hasMany(CoefficientValue::class);
    }
}
