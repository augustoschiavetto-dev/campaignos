<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Municipio extends Model
{
    protected $table = 'municipios';

    protected $fillable = [
        'nome',
        'estado',
        'codigo_ibge',
        'municipio_principal',
        'ativo',
    ];

    protected $casts = [
        'municipio_principal' => 'boolean',
        'ativo' => 'boolean',
    ];

    public function regioes(): HasMany
    {
        return $this->hasMany(Regiao::class, 'municipio_id');
    }

    public function bairros(): HasMany
    {
        return $this->hasMany(Bairro::class, 'municipio_id');
    }
}
