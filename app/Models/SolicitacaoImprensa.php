<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitacaoImprensa extends Model
{
    protected $table = 'solicitacoes_imprensa';

    protected $fillable = [
        'veiculo_id',
        'jornalista_id',
        'pauta',
        'data_recebida',
        'prazo_resposta',
        'responsavel_id',
        'candidato_porta_voz',
        'evento_relacionado_id',
        'status',
        'observacoes',
        'resposta_preparada',
        'resultado',
    ];

    protected $casts = [
        'data_recebida' => 'date',
        'prazo_resposta' => 'datetime',
    ];

    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(VeiculoImprensa::class, 'veiculo_id');
    }

    public function jornalista(): BelongsTo
    {
        return $this->belongsTo(Relacionamento::class, 'jornalista_id');
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }

    public function eventoRelacionado(): BelongsTo
    {
        return $this->belongsTo(Evento::class, 'evento_relacionado_id');
    }
}
