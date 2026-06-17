<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoefficientCategoryDep extends Model
{
    public $timestamps = false;
    protected $fillable = ['coefficient_id', 'category_id'];

    public function coefficient(): BelongsTo
    {
        return $this->belongsTo(Coefficient::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
