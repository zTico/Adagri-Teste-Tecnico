<?php

namespace App\Models;

use Database\Factories\FarmFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Farm extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'city',
        'state',
        'state_registration',
        'total_area',
        'rural_producer_id',
    ];

    protected function casts(): array
    {
        return [
            'total_area' => 'decimal:2',
        ];
    }

    public function ruralProducer(): BelongsTo
    {
        return $this->belongsTo(RuralProducer::class);
    }

    public function herds(): HasMany
    {
        return $this->hasMany(Herd::class);
    }
}
