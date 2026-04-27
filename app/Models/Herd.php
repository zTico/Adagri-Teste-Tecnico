<?php

namespace App\Models;

use App\Enums\HerdPurpose;
use App\Enums\HerdSpecies;
use Database\Factories\HerdFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Herd extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'species',
        'quantity',
        'purpose',
        'farm_id',
    ];

    protected function casts(): array
    {
        return [
            'species' => HerdSpecies::class,
            'purpose' => HerdPurpose::class,
            'quantity' => 'integer',
        ];
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }
}
