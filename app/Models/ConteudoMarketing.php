<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ConteudoMarketing extends Model
{
    protected $table = 'conteudos_marketing';

    protected $fillable = [
        'titulo',
        'tema',
        'objetivo',
        'tipo',
        'publico_alvo',
        'bairro_relacionado_id',
        'evento_relacionado_id',
        'demanda_relacionada_id',
        'campanha_tematica_id',
        'pauta_origem_id',
        'responsavel_id',
        'data_criacao',
        'prazo',
        'data_prevista_gravacao',
        'data_prevista_publicacao',
        'roteiro_texto',
        'chamada_principal',
        'observacoes_internas',
        'status',
        'prioridade',
        'link_arquivos',
        'link_publicado',
        'data_real_publicacao',
        'canais',
        'aprovado_por_id',
        'data_aprovacao',
        'texto_approved',
        'revisao_juridica_necessaria',
        'revisao_juridica_status',
        'revisao_juridica_observacao',
        'metricas_visualizacoes',
        'metricas_alcance',
        'metricas_curtidas',
        'metricas_comentarios',
        'metricas_compartilhamentos',
        'metricas_salvamentos',
        'metricas_cliques',
        'metricas_mensagens',
        'metricas_contatos_gerados',
        'metricas_desempenho_obs',
    ];

    protected $casts = [
        'data_criacao' => 'date',
        'prazo' => 'date',
        'data_prevista_gravacao' => 'date',
        'data_prevista_publicacao' => 'date',
        'data_real_publicacao' => 'date',
        'data_aprovacao' => 'datetime',
        'revisao_juridica_necessaria' => 'boolean',
        'canais' => 'array', // Converte automaticamente o JSON em array do PHP!
        'metricas_visualizacoes' => 'integer',
        'metricas_alcance' => 'integer',
        'metricas_curtidas' => 'integer',
        'metricas_comentarios' => 'integer',
        'metricas_compartilhamentos' => 'integer',
        'metricas_salvamentos' => 'integer',
        'metricas_cliques' => 'integer',
        'metricas_mensagens' => 'integer',
        'metricas_contatos_gerados' => 'integer',
    ];

    public function bairroRelacionado(): BelongsTo
    {
        return $this->belongsTo(Bairro::class, 'bairro_relacionado_id');
    }

    public function eventoRelacionado(): BelongsTo
    {
        return $this->belongsTo(Evento::class, 'evento_relacionado_id');
    }

    public function demandaRelacionada(): BelongsTo
    {
        return $this->belongsTo(DemandaCompromisso::class, 'demanda_relacionada_id');
    }

    public function campanhaTematica(): BelongsTo
    {
        return $this->belongsTo(CampanhaTematica::class, 'campanha_tematica_id');
    }

    public function pautaOrigem(): BelongsTo
    {
        return $this->belongsTo(BancoPauta::class, 'pauta_origem_id');
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }

    public function aprovadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprovado_por_id');
    }

    public function participantes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conteudo_participante', 'conteudo_id', 'user_id');
    }
}
