<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evento extends Model
{
    protected $table = 'eventos';

    protected $fillable = [
        'titulo',
        'tipo',
        'descricao',
        'data_hora_inicio',
        'data_hora_fim',
        'endereco',
        'bairro_id',
        'responsavel_id',
        'prioridade',
        'status',
        'tempo_deslocamento_manual',
        'custo_estimado',
        'custo_realizado',
        'resultado',
        'checklist',
    ];

    protected $casts = [
        'data_hora_inicio' => 'datetime',
        'data_hora_fim' => 'datetime',
        'checklist' => 'array',
        'custo_estimado' => 'decimal:2',
        'custo_realizado' => 'decimal:2',
    ];

    public function bairro(): BelongsTo
    {
        return $this->belongsTo(Bairro::class, 'bairro_id');
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }

    /**
     * Verifica se existe algum compromisso do mesmo candidato (responsável) no mesmo intervalo.
     */
    public static function detectarSobreposicao(string $inicio, string $fim, ?int $eventoIgnorarId = null, ?int $responsavelId = null): bool
    {
        $query = self::where(function ($q) use ($inicio, $fim) {
            $q->whereBetween('data_hora_inicio', [$inicio, $fim])
              ->orWhereBetween('data_hora_fim', [$inicio, $fim])
              ->orWhere(function ($sub) use ($inicio, $fim) {
                  $sub->where('data_hora_inicio', '<=', $inicio)
                      ->where('data_hora_fim', '>=', $fim);
              });
        })
        ->whereNotIn('status', ['cancelado', 'recusado']);

        if ($eventoIgnorarId) {
            $query->where('id', '!=', $eventoIgnorarId);
        }

        if ($responsavelId) {
            $query->where('responsavel_id', $responsavelId);
        }

        return $query->exists();
    }
}
