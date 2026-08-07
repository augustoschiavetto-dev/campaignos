<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VeiculoImprensa extends Model
{
    protected $table = 'veiculos_imprensa';

    protected $fillable = [
        'nome',
        'tipo',
        'cidade',
        'site',
        'observacoes',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function solicitacoes(): HasMany
    {
        return $this->hasMany(SolicitacaoImprensa::class, 'veiculo_id');
    }

    public function entrevistas(): HasMany
    {
        return $this->hasMany(Entrevista::class, 'veiculo_id');
    }
}
