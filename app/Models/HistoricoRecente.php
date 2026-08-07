<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class HistoricoRecente extends Model
{
    protected $table = 'historico_recente';

    protected $fillable = [
        'user_id',
        'acessavel_type',
        'acessavel_id',
        'titulo',
        'url',
        'visited_at',
    ];

    protected $casts = [
        'visited_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function acessavel(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Helper estático rápido para registrar acessos da equipe
     */
    public static function registrarAcesso(int $userId, string $type, int $id, string $titulo, string $url): void
    {
        self::updateOrCreate(
            [
                'user_id' => $userId,
                'acessavel_type' => $type,
                'acessavel_id' => $id,
            ],
            [
                'titulo' => $titulo,
                'url' => $url,
                'visited_at' => now(),
            ]
        );
    }
}
