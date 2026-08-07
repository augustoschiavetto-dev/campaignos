<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Arquivo extends Model
{
    protected $table = 'arquivos';

    protected $fillable = [
        'nome',
        'categoria',
        'descricao',
        'path_ou_link',
        'is_link_externo',
        'relacionado_type',
        'relacionado_id',
        'responsavel_id',
        'tags',
        'data',
        'versao',
        'status',
        'observacoes',
    ];

    protected $casts = [
        'is_link_externo' => 'boolean',
        'data' => 'date',
        'versao' => 'integer',
    ];

    public function relacionado(): MorphTo
    {
        return $this->morphTo();
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }

    public function versoes(): HasMany
    {
        return $this->hasMany(ArquivoVersao::class, 'arquivo_id');
    }
}
