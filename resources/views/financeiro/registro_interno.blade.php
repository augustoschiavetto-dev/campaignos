<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Interno Financeiro {{ $lancamento->protocolo_interno }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            margin: 40px;
            font-size: 13px;
            line-height: 1.6;
        }
        .container {
            border: 2px dashed #000;
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
            position: relative;
        }
        .header {
            text-align: center;
            border-bottom: 2px double #000;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }
        .header h2 {
            font-size: 13px;
            margin: 0 0 10px 0;
            font-weight: normal;
        }
        .alerta-titulo {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            color: #c00;
            margin: 15px 0;
            text-transform: uppercase;
        }
        .registro-titulo {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 10px 0;
        }
        .section-title {
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 1px solid #ddd;
            padding-bottom: 3px;
            margin-top: 25px;
            margin-bottom: 10px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table td {
            padding: 6px 0;
            vertical-align: top;
        }
        .data-table td.label {
            font-weight: bold;
            width: 180px;
        }
        .valor-extenso {
            font-style: italic;
            font-weight: bold;
            background-color: #f5f5f5;
            padding: 8px;
            border: 1px solid #ccc;
            margin: 15px 0;
        }
        .assinaturas {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .assinatura-box {
            text-align: center;
            width: 45%;
        }
        .linha-assinatura {
            border-top: 1px solid #000;
            margin-top: 40px;
            padding-top: 5px;
        }
        .footer-legal {
            margin-top: 30px;
            font-size: 10px;
            text-align: justify;
            color: #555;
            border-top: 1px dashed #ccc;
            padding-top: 15px;
        }
        .btn-imprimir {
            display: block;
            width: 120px;
            margin: 20px auto;
            padding: 10px;
            text-align: center;
            background-color: #004e3b;
            color: white;
            text-decoration: none;
            font-weight: bold;
            border-radius: 5px;
        }
        @media print {
            .btn-imprimir {
                display: none;
            }
            body {
                margin: 0;
            }
            .container {
                border: 2px dashed #000;
                padding: 20px;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h1>Controle Administrativo Interno</h1>
            <h2>CAMPANHA DE GUTO SCHIAVETTO — DEPUTADO FEDERAL</h2>
            <strong>CNPJ da Campanha: 12.345.678/0001-99</strong><br>
        </div>

        <div class="alerta-titulo">
            REGISTRO INTERNO — SEM VALIDADE COMO RECIBO ELEITORAL OFICIAL
        </div>

        <div class="registro-titulo">
            PROTOCOLO INTERNO DE RECEBIMENTO Nº {{ $lancamento->protocolo_interno }}
        </div>

        <div class="section-title">Dados da Entrada</div>
        <table class="data-table">
            <tr>
                <td class="label">Valor:</td>
                <td style="font-size: 15px; font-weight: bold;">R$ {{ number_format($lancamento->valor, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">Meio de Recebimento:</td>
                <td>{{ $lancamento->meio_pagamento }}</td>
            </tr>
            <tr>
                <td class="label">Data de Recebimento:</td>
                <td>{{ $lancamento->data_lancamento->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td class="label">Categoria contábil:</td>
                <td>{{ $lancamento->categoria }}</td>
            </tr>
        </table>

        <div class="valor-extenso">
            Valor por Extenso: 
            @php
                if (class_exists('NumberFormatter')) {
                    $f = new \NumberFormatter("pt_BR", \NumberFormatter::SPELLOUT);
                    echo mb_strtoupper($f->format($lancamento->valor)) . " REAIS";
                } else {
                    echo number_format($lancamento->valor, 2, ',', '.') . " REAIS";
                }
            @endphp
        </div>

        <div class="section-title">Identificação da Origem</div>
        <table class="data-table">
            <tr>
                <td class="label">Nome do Ofertante:</td>
                <td>{{ $lancamento->nome_cadastrado }}</td>
            </tr>
            <tr>
                <td class="label">CPF/CNPJ do Ofertante:</td>
                <td>
                    @if(auth()->user()->can('financeiro.visualizar_dados_fiscais'))
                        {{ $lancamento->cpf_cnpj }}
                    @else
                        ***.***.***-**
                    @endif
                </td>
            </tr>
        </table>

        <div class="assinaturas">
            <div class="assinatura-box">
                <div class="linha-assinatura">
                    {{ $lancamento->nome_cadastrado }}<br>
                    <strong>Ofertante / Doador</strong>
                </div>
            </div>
            <div class="assinatura-box">
                <div class="linha-assinatura">
                    COMITÊ FINANCEIRO DE CAMPANHA<br>
                    <strong>Responsável Administrativo</strong>
                </div>
            </div>
        </div>

        <div class="footer-legal">
            <strong>Aviso de Isenção e Conformidade:</strong> Este documento destina-se exclusivamente ao controle administrativo interno. A emissão e o registro oficiais devem ser realizados no sistema de prestação de contas da Justiça Eleitoral, sob responsabilidade da contabilidade da campanha. A classificação, documentação e admissibilidade da receita devem ser validadas pela contabilidade eleitoral.
        </div>
    </div>

    <a href="#" onclick="window.print(); return false;" class="btn-imprimir">🖨️ Imprimir Protocolo</a>

</body>
</html>
