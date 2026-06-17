<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InputField extends Model
{
    protected $fillable = ['emission_type_id', 'name', 'display_name', 'unit'];

    public function emissionType(): BelongsTo
    {
        return $this->belongsTo(EmissionType::class);
    }
}
