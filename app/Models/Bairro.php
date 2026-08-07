<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Bairro extends Model
{
    protected $table = 'bairros';

    protected $fillable = [
        'municipio_id',
        'regiao_id',
        'nome',
        'nome_alternativo',
        'prioridade',
        'responsavel_id',
        'populacao_estimada_manual',
        'meta_contatos',
        'observacoes',
        'data_ultima_acao',
        'data_proxima_acao',
        'status_cobertura',
        'ativo',
    ];

    protected $casts = [
        'data_ultima_acao' => 'date',
        'data_proxima_acao' => 'date',
        'ativo' => 'boolean',
        'populacao_estimada_manual' => 'integer',
        'meta_contatos' => 'integer',
    ];

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class, 'municipio_id');
    }

    public function regiao(): BelongsTo
    {
        return $this->belongsTo(Regiao::class, 'regiao_id');
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }

    public function locaisEstrategicos(): HasMany
    {
        return $this->hasMany(LocalEstrategico::class, 'bairro_id');
    }

    public function contatos(): HasMany
    {
        return $this->hasMany(Relacionamento::class, 'bairro_id');
    }

    public function eventos(): HasMany
    {
        return $this->hasMany(Evento::class, 'bairro_id');
    }

    public function tarefas(): MorphMany
    {
        return $this->morphMany(Tarefa::class, 'relacionado');
    }

    public function demandas(): HasMany
    {
        return $this->hasMany(DemandaCompromisso::class, 'bairro_relacionado_id');
    }
}
