<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Interacao extends Model
{
    protected $table = 'interacoes';

    protected $fillable = [
        'relacionamento_id',
        'user_id',
        'tipo',
        'data_interacao',
        'descricao',
    ];

    protected $casts = [
        'data_interacao' => 'date',
    ];

    public function relacionamento(): BelongsTo
    {
        return $this->belongsTo(Relacionamento::class, 'relacionamento_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
