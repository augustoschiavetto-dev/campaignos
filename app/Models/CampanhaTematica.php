<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CampanhaTematica extends Model
{
    protected $table = 'campanhas_tematicas';

    protected $fillable = [
        'nome',
        'descricao',
        'objetivo',
        'data_inicio',
        'data_termino',
        'responsavel_id',
        'status',
        'prioridade',
        'observacoes',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_termino' => 'date',
    ];

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }

    public function conteudos(): HasMany
    {
        return $this->hasMany(ConteudoMarketing::class, 'campanha_tematica_id');
    }
}
