<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Kit extends Model
{
    protected $table = 'kits';

    protected $fillable = [
        'nome',
        'descricao',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function materiais(): BelongsToMany
    {
        return $this->belongsToMany(Material::class, 'kit_itens', 'kit_id', 'material_id')
                    ->withPivot('quantidade_prevista', 'observacao');
    }

    public function eventos(): BelongsToMany
    {
        return $this->belongsToMany(Evento::class, 'kit_evento', 'kit_id', 'evento_id');
    }
}
