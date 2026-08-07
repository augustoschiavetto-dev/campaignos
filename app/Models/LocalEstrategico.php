<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocalEstrategico extends Model
{
    protected $table = 'locais_estrategicos';

    protected $fillable = [
        'nome',
        'tipo',
        'endereco',
        'bairro_id',
        'contato_responsavel',
        'telefone',
        'observacoes',
        'nivel_prioridade',
        'data_ultima_visita',
        'proxima_acao',
        'status',
    ];

    protected $casts = [
        'data_ultima_visita' => 'date',
    ];

    public function bairro(): BelongsTo
    {
        return $this->belongsTo(Bairro::class, 'bairro_id');
    }
}
