<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MuralAviso extends Model
{
    protected $table = 'mural_avisos';

    protected $fillable = [
        'titulo',
        'mensagem',
        'autor_id',
        'prioridade',
        'data_inicio',
        'data_expiracao',
        'fixado',
        'anexo_path',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_expiracao' => 'date',
        'fixado' => 'boolean',
    ];

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }
}
