<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialMovimentacao extends Model
{
    protected $table = 'materiais_movimentacoes';

    protected $fillable = [
        'material_id',
        'tipo_movimentacao',
        'quantidade',
        'quantidade_anterior',
        'quantidade_nova',
        'data_hora',
        'responsavel_id',
        'destinatario_id',
        'destinatario_type',
        'evento_relacionado_id',
        'bairro_relacionado_id',
        'observacao',
        'comprovante_path',
    ];

    protected $casts = [
        'quantidade' => 'integer',
        'quantidade_anterior' => 'integer',
        'quantidade_nova' => 'integer',
        'data_hora' => 'datetime',
    ];

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'material_id');
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }

    public function eventoRelacionado(): BelongsTo
    {
        return $this->belongsTo(Evento::class, 'evento_relacionado_id');
    }

    public function bairroRelacionado(): BelongsTo
    {
        return $this->belongsTo(Bairro::class, 'bairro_relacionado_id');
    }

    public function destinatario()
    {
        if ($this->destinatario_type) {
            return $this->destinatario_type::find($this->destinatario_id);
        }
        return null;
    }
}
