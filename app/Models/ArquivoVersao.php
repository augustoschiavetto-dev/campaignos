<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArquivoVersao extends Model
{
    protected $table = 'arquivos_versoes';

    protected $fillable = [
        'arquivo_id',
        'versao',
        'data',
        'autor_id',
        'status',
        'observacao',
        'conteudo_texto',
        'file_path',
    ];

    protected $casts = [
        'versao' => 'integer',
        'data' => 'date',
    ];

    public function arquivo(): BelongsTo
    {
        return $this->belongsTo(Arquivo::class, 'arquivo_id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }
}
