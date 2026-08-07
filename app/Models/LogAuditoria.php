<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class LogAuditoria extends Model
{
    protected $table = 'logs_auditoria';
    
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'acao',
        'tabela',
        'registro_id',
        'valor_anterior',
        'valor_novo',
        'ip_origem',
        'navegador',
        'dispositivo',
    ];

    public static function registrar(?int $userId, string $acao, ?string $tabela = null, ?int $registroId = null, ?array $valorAnterior = null, ?array $valorNovo = null): void
    {
        $userAgent = Request::header('User-Agent', '');
        $dispositivo = self::detectarDispositivo($userAgent);
        $navegador = self::detectarNavegador($userAgent);

        self::create([
            'user_id' => $userId,
            'acao' => $acao,
            'tabela' => $tabela,
            'registro_id' => $registroId,
            'valor_anterior' => $valorAnterior ? json_encode($valorAnterior, JSON_UNESCAPED_UNICODE) : null,
            'valor_novo' => $valorNovo ? json_encode($valorNovo, JSON_UNESCAPED_UNICODE) : null,
            'ip_origem' => Request::ip(),
            'navegador' => $navegador,
            'dispositivo' => $dispositivo,
        ]);
    }

    private static function detectarDispositivo(string $userAgent): string
    {
        if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i', $userAgent)) {
            return 'Tablet';
        }
        if (preg_match('/(up\.browser|up\.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile|iphone|ipad|ipod)/i', $userAgent)) {
            return 'Celular';
        }
        return 'Computador';
    }

    private static function detectarNavegador(string $userAgent): string
    {
        if (preg_match('/MSIE/i', $userAgent) && !preg_match('/Opera/i', $userAgent)) {
            return 'Internet Explorer';
        }
        if (preg_match('/Firefox/i', $userAgent)) {
            return 'Mozilla Firefox';
        }
        if (preg_match('/Chrome/i', $userAgent) && !preg_match('/(Edg|OPR)/i', $userAgent)) {
            return 'Google Chrome';
        }
        if (preg_match('/Safari/i', $userAgent) && !preg_match('/Chrome/i', $userAgent)) {
            return 'Apple Safari';
        }
        if (preg_match('/Edg/i', $userAgent)) {
            return 'Microsoft Edge';
        }
        if (preg_match('/(Opera|OPR)/i', $userAgent)) {
            return 'Opera';
        }
        return 'Navegador Desconhecido';
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
