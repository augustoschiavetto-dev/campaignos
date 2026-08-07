<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceiroDocumento extends Model
{
    protected $table = 'financeiro_documentos';

    protected $fillable = [
        'lancamento_id',
        'tipo_documento',
        'arquivo_path',
        'criado_por_id',
    ];

    public function lancamento(): BelongsTo
    {
        return $this->belongsTo(FinanceiroLancamento::class, 'lancamento_id');
    }

    public function criadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'criado_por_id');
    }
}
