<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TipoRelacionamento extends Model
{
    protected $table = 'tipos_relacionamento';

    protected $fillable = [
        'nome',
        'descricao',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function relacionamentos(): BelongsToMany
    {
        return $this->belongsToMany(Relacionamento::class, 'relacionamento_tipo', 'tipo_relacionamento_id', 'relacionamento_id');
    }
}
