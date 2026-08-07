<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Material extends Model
{
    protected $table = 'materiais';

    protected $fillable = [
        'nome',
        'categoria',
        'codigo_interno',
        'descricao',
        'unidade',
        'quantidade_atual',
        'quantidade_minima',
        'localizacao',
        'estado_conservacao',
        'responsavel_id',
        'valor_estimado',
        'observacoes',
        'ativo',
    ];

    protected $casts = [
        'quantidade_atual' => 'integer',
        'quantidade_minima' => 'integer',
        'valor_estimado' => 'decimal:2',
        'ativo' => 'boolean',
    ];

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }

    public function movimentacoes(): HasMany
    {
        return $this->hasMany(MaterialMovimentacao::class, 'material_id');
    }

    public function kits(): BelongsToMany
    {
        return $this->belongsToMany(Kit::class, 'kit_itens', 'material_id', 'kit_id')
                    ->withPivot('quantidade_prevista', 'observacao');
    }
}
