<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BancoPauta extends Model
{
    protected $table = 'banco_pautas';

    protected $fillable = [
        'titulo',
        'descricao',
        'origem',
        'tema',
        'contato_relacionado_id',
        'bairro_relacionado_id',
        'demanda_relacionada_id',
        'prioridade',
        'responsavel_id',
        'status',
        'prazo',
        'observacoes',
    ];

    protected $casts = [
        'prazo' => 'date',
    ];

    public function contatoRelacionado(): BelongsTo
    {
        return $this->belongsTo(Relacionamento::class, 'contato_relacionado_id');
    }

    public function bairroRelacionado(): BelongsTo
    {
        return $this->belongsTo(Bairro::class, 'bairro_relacionado_id');
    }

    public function demandaRelacionada(): BelongsTo
    {
        return $this->belongsTo(DemandaCompromisso::class, 'demanda_relacionada_id');
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }

    public function conteudosGerados(): HasMany
    {
        return $this->hasMany(ConteudoMarketing::class, 'pauta_origem_id');
    }
}
