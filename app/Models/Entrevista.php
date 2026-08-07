<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Entrevista extends Model
{
    protected $table = 'entrevistas';

    protected $fillable = [
        'veiculo_id',
        'jornalista_id',
        'pauta',
        'data',
        'horario',
        'local_link',
        'responsavel_id',
        'porta_voz',
        'status',
        'briefing',
        'perguntas_provaveis',
        'pontos_atencao',
        'respostas_sugeridas',
        'assuntos_evitar',
        'compromissos_relacionados',
        'resultado',
        'link_publicado',
    ];

    protected $casts = [
        'data' => 'date',
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
}
