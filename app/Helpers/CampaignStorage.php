<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CampaignStorage
{
    /**
     * Salva um arquivo no diretório específico de um módulo.
     * Diretórios: contatos, eventos, financeiro, marketing, contratos, logos, documentos
     */
    public static function salvar(UploadedFile $arquivo, string $modulo, string $disco = 'public'): string
    {
        $modulosValidos = ['contatos', 'eventos', 'financeiro', 'marketing', 'contratos', 'logos', 'documentos'];
        
        $pasta = in_array($modulo, $modulosValidos) ? $modulo : 'documentos';
        
        // Gera um nome único mantendo a extensão
        $nomeArquivo = uniqid() . '_' . time() . '.' . $arquivo->getClientOriginalExtension();
        
        return $arquivo->storeAs($pasta, $nomeArquivo, $disco);
    }

    /**
     * Remove um arquivo do storage.
     */
    public static function remover(?string $caminho, string $disco = 'public'): bool
    {
        if ($caminho && Storage::disk($disco)->exists($caminho)) {
            return Storage::disk($disco)->delete($caminho);
        }
        return false;
    }

    /**
     * Retorna a URL pública de um arquivo.
     */
    public static function url(?string $caminho): string
    {
        if (!$caminho) {
            return '';
        }
        return Storage::url($caminho);
    }
}
