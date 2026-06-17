<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CategoryValue extends Model
{
    protected $fillable = ['category_id', 'code', 'label'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function coefficientValues(): BelongsToMany
    {
        return $this->belongsToMany(CoefficientValue::class, 'coefficient_value_category_pivot');
    }
}
