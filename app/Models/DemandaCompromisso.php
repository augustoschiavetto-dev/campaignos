<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemandaCompromisso extends Model
{
    protected $table = 'demandas_compromissos';

    protected $fillable = [
        'titulo',
        'descricao',
        'tipo',
        'contato_relacionado_id',
        'lideranca_relacionada_id',
        'bairro_relacionado_id',
        'evento_relacionado_id',
        'responsavel_interno_id',
        'origem',
        'data_registro',
        'prazo',
        'prioridade',
        'status',
        'proxima_acao',
        'data_proxima_acao',
        'resultado',
        'motivo_cancelamento',
        'observacoes_publicas',
        'observacoes_internas',
        'aprovado_por_id',
        'data_aprovacao',
        'texto_anterior',
        'texto_aprovado',
    ];

    protected $casts = [
        'data_registro' => 'date',
        'prazo' => 'date',
        'data_proxima_acao' => 'date',
        'data_aprovacao' => 'datetime',
    ];

    public function contatoRelacionado(): BelongsTo
    {
        return $this->belongsTo(Relacionamento::class, 'contato_relacionado_id');
    }

    public function liderancaRelacionada(): BelongsTo
    {
        return $this->belongsTo(Relacionamento::class, 'lideranca_relacionada_id');
    }

    public function bairroRelacionado(): BelongsTo
    {
        return $this->belongsTo(Bairro::class, 'bairro_relacionado_id');
    }

    public function eventoRelacionado(): BelongsTo
    {
        return $this->belongsTo(Evento::class, 'evento_relacionado_id');
    }

    public function responsavelInterno(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsavel_interno_id');
    }

    public function aprovadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprovado_por_id');
    }
}
