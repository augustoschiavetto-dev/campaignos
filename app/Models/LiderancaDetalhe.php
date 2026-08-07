<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiderancaDetalhe extends Model
{
    protected $table = 'liderancas_detalhes';

    protected $fillable = [
        'relacionamento_id',
        'area_influencia',
        'votos_estimados',
        'data_estimativa',
        'responsavel_estimativa_id',
        'justificativa',
        'nivel_confianca',
        'observacoes_influencia',
    ];

    protected $casts = [
        'votos_estimados' => 'integer',
        'data_estimativa' => 'date',
    ];

    public function relacionamento(): BelongsTo
    {
        return $this->belongsTo(Relacionamento::class, 'relacionamento_id');
    }

    public function responsavelEstimativa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsavel_estimativa_id');
    }
}
