<?php

namespace App\Models;

use Database\Factories\RuralProducerFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RuralProducer extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'cpf_cnpj',
        'phone',
        'email',
        'postal_code',
        'street',
        'number',
        'complement',
        'district',
        'city',
        'state',
    ];

    public function farms(): HasMany
    {
        return $this->hasMany(Farm::class);
    }
}
