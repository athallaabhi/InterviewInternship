<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CoefficientValue extends Model
{
    protected $fillable = ['coefficient_id', 'value', 'based_on'];

    protected $casts = ['value' => 'float'];

    public function coefficient(): BelongsTo
    {
        return $this->belongsTo(Coefficient::class);
    }

    public function categoryValues(): BelongsToMany
    {
        return $this->belongsToMany(CategoryValue::class, 'coefficient_value_category_pivot');
    }
}
