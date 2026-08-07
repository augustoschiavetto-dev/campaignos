<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Timeline extends Model
{
    protected $table = 'timeline';

    protected $fillable = [
        'tipo_evento',
        'titulo',
        'descricao',
        'user_id',
        'relacionado_type',
        'relacionado_id',
    ];

    /**
     * Registra um novo evento na timeline de forma simplificada.
     */
    public static function registrar(string $tipo, string $titulo, ?string $descricao = null, ?int $userId = null, ?Model $relacionado = null): self
    {
        return self::create([
            'tipo_evento' => $tipo,
            'titulo' => $titulo,
            'descricao' => $descricao,
            'user_id' => $userId ?? (auth()->check() ? auth()->id() : null),
            'relacionado_type' => $relacionado ? get_class($relacionado) : null,
            'relacionado_id' => $relacionado ? $relacionado->getKey() : null,
        ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function relacionado()
    {
        return $this->morphTo();
    }
}
