<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracaoCampanha extends Model
{
    protected $table = 'configuracoes_campanha';

    protected $fillable = [
        'nome_campanha',
        'candidato_nome',
        'candidato_nome_politico',
        'candidato_cargo',
        'candidato_numero',
        'partido_sigla',
        'partido_coligacao',
        'campanha_cnpj',
        'campanha_cidade',
        'campanha_uf',
        'data_primeiro_turno',
        'campanha_timezone',
        'campanha_telefone',
        'campanha_email',
        'campanha_site',
        'campanha_instagram',
        'campanha_facebook',
        'campanha_youtube',
        'identidade_cor_primaria',
        'identidade_cor_secundaria',
        'identidade_logo_path',
        'texto_institucional_curto',
        'preferencia_tema',
        'prioridades_dia',
    ];

    protected $casts = [
        'data_primeiro_turno' => 'date',
        'prioridades_dia' => 'array',
    ];

    /**
     * Retorna a configuração ativa ou cria uma default com os dados do candidato de Limeira.
     */
    public static function obter(): self
    {
        $config = self::first();
        if (!$config) {
            $config = self::create([
                'nome_campanha' => 'Guto Schiavetto Federal 2026',
                'candidato_nome' => 'Guto Schiavetto',
                'candidato_nome_politico' => 'Guto Schiavetto',
                'candidato_cargo' => 'Deputado Federal',
                'candidato_numero' => '9999',
                'partido_sigla' => 'PARTIDO_EXEMPLO',
                'partido_coligacao' => 'Limeira no Coração',
                'campanha_cnpj' => '00.000.000/0001-00',
                'campanha_cidade' => 'Limeira',
                'campanha_uf' => 'SP',
                'data_primeiro_turno' => '2026-10-04',
                'campanha_timezone' => 'America/Sao_Paulo',
                'campanha_telefone' => '(19) 99999-9999',
                'campanha_email' => 'contato@gutoschiavetto.com.br',
                'campanha_site' => 'www.gutoschiavetto.com.br',
                'campanha_instagram' => 'https://instagram.com/gutoschiavetto',
                'campanha_facebook' => 'https://facebook.com/gutoschiavetto',
                'campanha_youtube' => 'https://youtube.com/gutoschiavetto',
                'identidade_cor_primaria' => '#1e3a8a',
                'identidade_cor_secundaria' => '#10b981',
                'texto_institucional_curto' => 'Campanha focada no desenvolvimento regional e social do município de Limeira-SP.',
                'preferencia_tema' => 'escuro',
                'prioridades_dia' => [
                    ['id' => 1, 'titulo' => 'Revisar material de panfletagem', 'concluido' => false],
                    ['id' => 2, 'titulo' => 'Confirmar local da caminhada no Bairro Centro', 'concluido' => false],
                    ['id' => 3, 'titulo' => 'Gravar roteiro de vídeo para Instagram', 'concluido' => false]
                ],
            ]);
        }
        return $config;
    }
}
