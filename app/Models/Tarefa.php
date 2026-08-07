<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Tarefa extends Model
{
    protected $table = 'tarefas';

    protected $fillable = [
        'titulo',
        'descricao',
        'responsavel_id',
        'data_inicio',
        'prazo',
        'prioridade',
        'status',
        'checklist',
        'data_conclusao',
        'relacionado_type',
        'relacionado_id',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'prazo' => 'date',
        'checklist' => 'array',
        'data_conclusao' => 'datetime',
    ];

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }

    public function relacionado(): MorphTo
    {
        return $this->morphTo();
    }

    public function comentarios(): HasMany
    {
        return $this->hasMany(TarefaComentario::class, 'tarefa_id');
    }
}
