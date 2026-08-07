<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceiroLancamento extends Model
{
    protected $table = 'financeiro_lancamentos';

    protected $fillable = [
        'tipo',
        'categoria',
        'valor',
        'data_lancamento',
        'nome_cadastrado',
        'cpf_cnpj',
        'meio_pagamento',
        'comprovante_path',
        'status',
        'protocolo_interno',
        'criado_por_id',
        'observacoes',
        
        // 1. Campos do Recibo Oficial (Justiça Eleitoral)
        'recibo_oficial_necessario',
        'recibo_oficial_status',
        'recibo_oficial_numero',
        'recibo_oficial_data_emissao',
        'recibo_oficial_arquivo',
        'recibo_oficial_registrado_por',
        'recibo_oficial_conferido_por',
        'recibo_oficial_data_conferencia',
        'recibo_oficial_observacao',

        // 2. Decisão sobre Exigência de Recibo
        'exigencia_decisao',
        'exigencia_responsavel_id',
        'exigencia_data',
        'exigencia_justificativa',
        'exigencia_orientacao_contabil',

        // 3. Retificação Histórica
        'valor_anterior',
        'valor_novo',
        'retificado_por_id',
        'retificado_em',
        'retificado_motivo',
        'comprovante_path_anterior',

        // 4. Conciliação Bancária
        'conta_bancaria_campanha',
        'data_transacao_bancaria',
        'identificador_bancario',
        'valor_bancario',
        'situacao_conciliacao',
        'data_conciliacao',
        'conciliado_por_id',
        'divergencia_identificada',
        'justificativa_divergencia',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data_lancamento' => 'date',
        'recibo_oficial_necessario' => 'boolean',
        'recibo_oficial_data_emissao' => 'date',
        'recibo_oficial_data_conferencia' => 'datetime',
        'exigencia_data' => 'datetime',
        'retificado_em' => 'datetime',
        'data_transacao_bancaria' => 'date',
        'data_conciliacao' => 'datetime',
    ];

    public function criadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'criado_por_id');
    }

    public function documentos()
    {
        return $this->hasMany(FinanceiroDocumento::class, 'lancamento_id');
    }

    /**
     * Limpa o CPF ou CNPJ de caracteres não numéricos
     */
    public static function normalizarCpfCnpj(string $value): string
    {
        return preg_replace('/[^0-9]/', '', $value);
    }

    /**
     * Gera o próximo protocolo interno para lançamentos confirmados (ex: FIN-2026-000001)
     */
    public static function gerarProximoProtocolo(): string
    {
        $ano = date('Y');
        $ultimo = self::whereNotNull('protocolo_interno')
            ->where('protocolo_interno', 'like', "FIN-{$ano}-%")
            ->orderBy('protocolo_interno', 'desc')
            ->first();

        if ($ultimo) {
            $num = (int) substr($ultimo->protocolo_interno, 9) + 1;
        } else {
            $num = 1;
        }

        return "FIN-{$ano}-" . str_pad($num, 6, '0', STR_PAD_LEFT);
    }
}
