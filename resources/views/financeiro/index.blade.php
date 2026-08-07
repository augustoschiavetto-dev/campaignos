@extends('layouts.app')

@section('title', 'Controle Financeiro Interno')
@section('header_title', '💰 Gestão Financeira & Apoio Contábil')

@section('content')
<div class="space-y-6">
    <!-- Aviso de conformidade legal -->
    <div class="p-4 bg-blue-50 dark:bg-slate-900 border-l-4 border-blue-500 rounded-r-xl text-xs text-blue-800 dark:text-blue-200">
        <strong>⚠️ AVISO DE COMPLIANCE CONTÁBIL:</strong> O CampaignOS é uma ferramenta exclusivamente de apoio administrativo interno. Ele <strong>NÃO</strong> emite recibo eleitoral oficial, não substitui o sistema SPCE da Justiça Eleitoral ou serviços de contabilidade e não garante conformidade legal. A classificação, admissibilidade e documentação final de todas as receitas e despesas devem ser validadas e registradas oficialmente no sistema do TSE pelo contador responsável da campanha.
    </div>

    @if(session('success'))
        <div class="p-3 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 rounded-xl text-xs">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="p-3 bg-red-50 dark:bg-red-950/20 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800 rounded-xl text-xs space-y-1">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <!-- Painel de Indicadores e Alertas Contábeis -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 p-4 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm">
            <span class="text-slate-400 text-xs font-semibold block uppercase">Total Arrecadado (Conferido)</span>
            <span class="text-xl font-bold text-slate-800 dark:text-white mt-1 block">R$ {{ number_format($receitasTotais, 2, ',', '.') }}</span>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm">
            <span class="text-slate-400 text-xs font-semibold block uppercase">Despesas Contratadas (Conferidas)</span>
            <span class="text-xl font-bold text-red-500 mt-1 block">R$ {{ number_format($despesasPagas, 2, ',', '.') }}</span>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm">
            <span class="text-slate-400 text-xs font-semibold block uppercase">Contas a Pagar</span>
            <span class="text-xl font-bold text-amber-500 mt-1 block">R$ {{ number_format($despesasPendentes, 2, ',', '.') }}</span>
        </div>
        <div class="bg-white dark:bg-slate-900 p-4 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm">
            <span class="text-slate-400 text-xs font-semibold block uppercase">Saldo Caixa Interno</span>
            <span class="text-xl font-bold text-emerald-500 mt-1 block">R$ {{ number_format($saldoLiquido, 2, ',', '.') }}</span>
        </div>
    </div>

    <!-- Seção de Alertas Operacionais (Requisito 10) -->
    <div class="bg-white dark:bg-slate-900 p-4 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm space-y-3">
        <h3 class="text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wide">🚨 Alertas e Pendências Contábeis</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-[11px]">
            <div class="p-2 border border-slate-100 dark:border-slate-800 rounded-lg">
                <span class="text-slate-400 block">Aguardando Classificação</span>
                <span class="font-bold text-slate-850 dark:text-slate-200">{{ $alertas['receitas_sem_classificacao'] }} receitas</span>
            </div>
            <div class="p-2 border border-slate-100 dark:border-slate-800 rounded-lg">
                <span class="text-slate-400 block">Sem Exigência de Recibo Definida</span>
                <span class="font-bold text-slate-850 dark:text-slate-200">{{ $alertas['aguardando_decisao_recibo'] }} receitas</span>
            </div>
            <div class="p-2 border border-slate-100 dark:border-slate-800 rounded-lg">
                <span class="text-slate-400 block">Recibos Oficiais Pendentes no TSE</span>
                <span class="font-bold text-slate-850 dark:text-slate-200">{{ $alertas['recibos_oficiais_aguardando_emissao'] }}</span>
            </div>
            <div class="p-2 border border-slate-100 dark:border-slate-800 rounded-lg">
                <span class="text-slate-400 block">Lançamentos sob Conferência</span>
                <span class="font-bold text-slate-850 dark:text-slate-200">{{ $alertas['aguardando_conferencia'] }} pendentes</span>
            </div>
            <div class="p-2 border border-slate-100 dark:border-slate-800 rounded-lg">
                <span class="text-slate-400 block">Divergências de Conciliação</span>
                <span class="font-bold text-red-500">{{ $alertas['divergencias_conciliacao'] }} ocorrências</span>
            </div>
            <div class="p-2 border border-slate-100 dark:border-slate-800 rounded-lg">
                <span class="text-slate-400 block">Despesas Sem Comprovante</span>
                <span class="font-bold text-red-500">{{ $alertas['despesas_sem_comprovante'] }} registros</span>
            </div>
            <div class="p-2 border border-slate-100 dark:border-slate-800 rounded-lg">
                <span class="text-slate-400 block">Retificações Solicitadas</span>
                <span class="font-bold text-amber-500">{{ $alertas['retificacoes_pendentes'] }} pendentes</span>
            </div>
        </div>
    </div>

    <!-- Divisão de Ações de Criação e Filtros -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Formulário de Registro -->
        @can('financeiro.criar')
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-xl shadow-sm space-y-4">
            <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">➕ Novo Registro Interno</h3>

            <form action="{{ route('financeiro.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-[10px] font-semibold text-slate-500 uppercase mb-1">Tipo</label>
                    <select name="tipo" id="lancamento_tipo" onchange="alternarCampos()" class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg focus:outline-none">
                        <option value="receita">Receita (Entrada)</option>
                        <option value="despesa">Despesa (Saída)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-semibold text-slate-500 uppercase mb-1">Classificação Contábil / Categoria</label>
                    <select name="categoria" id="lancamento_categoria" class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg focus:outline-none">
                        <!-- Categorias de receita por padrão (Requisito 4) -->
                        <option value="doacao_pf">Doação financeira de pessoa física</option>
                        <option value="doacao_estimavel">Doação estimável em dinheiro</option>
                        <option value="recursos_proprios">Recursos próprios do candidato</option>
                        <option value="fundo_partidario">Repasse de Fundo Partidário</option>
                        <option value="fundo_especial">Repasse do Fundo Especial de Financiamento de Campanha</option>
                        <option value="transferência_candidato">Transferência de outro candidato</option>
                        <option value="transferencia_partido">Transferência de partido</option>
                        <option value="financiamento_coletivo">Financiamento coletivo</option>
                        <option value="rendimento_aplicacao">Rendimento de aplicação financeira</option>
                        <option value="sobra_campanha">Sobra financeira de campanha anterior</option>
                        <option value="outro_autorizado">Outro tipo autorizado e validado</option>
                    </select>
                    <span class="text-[9px] text-slate-400 block mt-1">A classificação, documentação e admissibilidade da receita devem ser validadas pela contabilidade eleitoral.</span>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 uppercase mb-1">Valor (R$)</label>
                        <input type="number" step="0.01" name="valor" required class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 uppercase mb-1">Data</label>
                        <input type="date" name="data_lancamento" value="{{ date('Y-m-d') }}" required class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-semibold text-slate-500 uppercase mb-1" id="nome_label">Nome do Doador</label>
                    <input type="text" name="nome_cadastrado" required class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg focus:outline-none">
                </div>

                <div>
                    <label class="block text-[10px] font-semibold text-slate-500 uppercase mb-1" id="doc_label">CPF do Doador</label>
                    <input type="text" name="cpf_cnpj" required placeholder="Somente números" class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg focus:outline-none">
                </div>

                <div>
                    <label class="block text-[10px] font-semibold text-slate-500 uppercase mb-1">Meio de Movimentação</label>
                    <select name="meio_pagamento" class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg focus:outline-none">
                        <option value="Pix">Pix</option>
                        <option value="Transferência Bancária">Transferência Bancária</option>
                        <option value="Depósito Identificado">Depósito Identificado</option>
                        <option value="Boleto">Boleto</option>
                        <option value="Dinheiro Espécie">Dinheiro Espécie (Checar Limites)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-semibold text-slate-500 uppercase mb-1">Comprovante de Operação</label>
                    <input type="file" name="comprovante" class="w-full text-xs text-slate-500 file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-semibold file:bg-slate-100 dark:file:bg-slate-800 file:text-slate-700 dark:file:text-slate-300 hover:file:bg-slate-200">
                </div>

                <div>
                    <label class="block text-[10px] font-semibold text-slate-500 uppercase mb-1">Observações Internas</label>
                    <textarea name="observacoes" rows="2" class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg focus:outline-none"></textarea>
                </div>

                <button type="submit" class="w-full py-2 bg-primary hover:bg-blue-650 text-white font-bold text-xs rounded-lg transition uppercase tracking-wider">
                    Registrar Lançamento
                </button>
            </form>
        </div>
        @endcan

        <!-- Listagem e Filtros -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-xl shadow-sm lg:col-span-2 space-y-4">
            <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">📋 Registros Financeiros Cadastrados</h3>

            <!-- Formulário de Filtro -->
            <form action="{{ route('financeiro.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-2 pb-3 border-b border-slate-200 dark:border-slate-800">
                <input type="text" name="busca" value="{{ request('busca') }}" placeholder="Nome, CPF ou CNPJ..." class="px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg focus:outline-none">
                
                <select name="tipo" class="px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg focus:outline-none">
                    <option value="">-- Todos os Tipos --</option>
                    <option value="receita" {{ request('tipo') === 'receita' ? 'selected' : '' }}>Receitas</option>
                    <option value="despesa" {{ request('tipo') === 'despesa' ? 'selected' : '' }}>Despesas</option>
                </select>

                <div class="flex gap-2">
                    <select name="status" class="flex-1 px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg focus:outline-none">
                        <option value="">-- Status Interno --</option>
                        <option value="rascunho" {{ request('status') === 'rascunho' ? 'selected' : '' }}>Rascunho</option>
                        <option value="pendente_conferencia" {{ request('status') === 'pendente_conferencia' ? 'selected' : '' }}>Pendente Conferencia</option>
                        <option value="conferido" {{ request('status') === 'conferido' ? 'selected' : '' }}>Conferido</option>
                        <option value="retificacao_solicitada" {{ request('status') === 'retificacao_solicitada' ? 'selected' : '' }}>Retificação Solicitada</option>
                        <option value="retificado" {{ request('status') === 'retificado' ? 'selected' : '' }}>Retificado</option>
                        <option value="cancelado" {{ request('status') === 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                    <button type="submit" class="px-3 bg-secondary hover:bg-emerald-600 text-white font-bold text-xs rounded-lg transition">Filtrar</button>
                </div>
            </form>

            <!-- Tabela de Lançamentos -->
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-400 uppercase text-[10px]">
                            <th class="py-2">Identificação / Protocolo</th>
                            <th class="py-2">Tipo / Categoria</th>
                            <th class="py-2 text-right">Valor</th>
                            <th class="py-2">Status</th>
                            <th class="py-2 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lancamentos as $item)
                            <tr class="border-b border-slate-100 dark:border-slate-850 hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                <td class="py-2.5">
                                    <div class="font-bold text-slate-800 dark:text-white">{{ $item->nome_cadastrado }}</div>
                                    <div class="text-[9px] text-slate-400 flex items-center space-x-1">
                                        <span>
                                            @can('financeiro.visualizar_dados_fiscais')
                                                {{ $item->cpf_cnpj }}
                                            @else
                                                ***.***.***-**
                                            @endcan
                                        </span>
                                        <span>•</span>
                                        <strong>{{ $item->protocolo_interno ?? 'Sem protocolo' }}</strong>
                                    </div>
                                </td>
                                <td class="py-2.5">
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase {{ $item->tipo === 'receita' ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/10 dark:text-blue-300' : 'bg-red-50 text-red-700 dark:bg-red-900/10 dark:text-red-300' }}">
                                        {{ $item->categoria }}
                                    </span>
                                    <div class="text-[9px] text-slate-400 mt-0.5">{{ $item->data_lancamento->format('d/m/Y') }}</div>
                                </td>
                                <td class="py-2.5 text-right font-bold {{ $item->tipo === 'receita' ? 'text-blue-500' : 'text-red-500' }}">
                                    R$ {{ number_format($item->valor, 2, ',', '.') }}
                                </td>
                                <td class="py-2.5">
                                    <span class="px-2 py-0.5 rounded-full font-bold text-[9px] uppercase {{ $item->status === 'conferido' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : ($item->status === 'cancelado' ? 'bg-slate-100 text-slate-500' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300') }}">
                                        {{ $item->status }}
                                    </span>
                                </td>
                                <td class="py-2.5 text-right space-y-1">
                                    <div class="flex items-center justify-end gap-1 flex-wrap">
                                        <!-- Protocolo Interno Impresso (só p/ receitas que não sejam fundos/repasses) -->
                                        @if($item->tipo === 'receita' && !in_array($item->categoria, ['fundo_partidario', 'fundo_especial', 'recursos_proprios', 'rendimento_aplicacao', 'sobra_campanha']))
                                            <a href="{{ route('financeiro.registro_interno', $item->id) }}" target="_blank" class="px-1.5 py-0.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded font-semibold text-[9px]">
                                                📄 Protocolo
                                            </a>
                                        @endif

                                        <!-- Ações de Conformidade e Fluxo -->
                                        @if($item->status === 'rascunho')
                                            @can('financeiro.editar_rascunho')
                                                <button onclick="abrirModalStatus({{ $item->id }}, 'pendente_conferencia')" class="px-1.5 py-0.5 bg-blue-500 hover:bg-blue-600 text-white rounded font-bold text-[9px]">
                                                    Enviar p/ Conferência
                                                </button>
                                            @endcan
                                        @elseif($item->status === 'pendente_conferencia')
                                            @can('financeiro.conferir')
                                                <form action="{{ route('financeiro.conferir', $item->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="px-1.5 py-0.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded font-bold text-[9px]">
                                                        Conferir & Bloquear
                                                    </button>
                                                </form>
                                            @endcan
                                        @elseif($item->status === 'conferido')
                                            @can('financeiro.solicitar_retificacao')
                                                <button onclick="abrirModalRetificacaoSolicitar({{ $item->id }})" class="px-1.5 py-0.5 bg-amber-500 hover:bg-amber-600 text-white rounded font-bold text-[9px]">
                                                    Solicitar Retificação
                                                </button>
                                            @endcan
                                        @elseif($item->status === 'retificacao_solicitada')
                                            @can('financeiro.retificar')
                                                <button onclick="abrirModalRetificar({{ $item->id }}, {{ json_encode($item) }})" class="px-1.5 py-0.5 bg-red-500 hover:bg-red-650 text-white rounded font-bold text-[9px]">
                                                    Retificar
                                                </button>
                                            @endcan
                                        @endif

                                        <!-- Conciliação Bancária -->
                                        @can('financeiro.conciliar')
                                            <button onclick="abrirModalConciliacao({{ $item->id }}, {{ json_encode($item) }})" class="px-1.5 py-0.5 bg-indigo-500 hover:bg-indigo-600 text-white rounded font-bold text-[9px]">
                                                Conciliar
                                            </button>
                                        @endcan

                                        <!-- Definir Exigência de Recibo -->
                                        @if($item->tipo === 'receita')
                                            @can('financeiro.definir_exigencia_recibo')
                                                <button onclick="abrirModalExigencia({{ $item->id }}, {{ json_encode($item) }})" class="px-1.5 py-0.5 bg-purple-500 hover:bg-purple-600 text-white rounded font-bold text-[9px]">
                                                    Exigência
                                                </button>
                                            @endcan
                                            @can('financeiro.registrar_recibo_oficial')
                                                @if($item->recibo_oficial_necessario && $item->recibo_oficial_status !== 'emitido')
                                                    <button onclick="abrirModalRegistroReciboOficial({{ $item->id }})" class="px-1.5 py-0.5 bg-pink-500 hover:bg-pink-600 text-white rounded font-bold text-[9px]">
                                                        Reg. Recibo Oficial
                                                    </button>
                                                @endif
                                            @endcan
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-400 italic">Nenhum registro encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pt-2">
                {{ $lancamentos->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal Definir Exigência de Recibo -->
<div id="modal-exigencia" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center hidden">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-xl shadow-xl w-full max-w-md space-y-4">
        <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase">Decisão de Exigência Contábil</h3>
        <form id="form-exigencia" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-[10px] font-semibold text-slate-500 mb-1">A doação necessita de recibo oficial no sistema do TSE?</label>
                <select name="exigencia_decisao" required class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg">
                    <option value="necessario">Sim, necessário gerar no TSE</option>
                    <option value="dispensado">Não, dispensado/outro tipo de recurso</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-semibold text-slate-500 mb-1">Justificativa da Decisão</label>
                <textarea name="exigencia_justificativa" required rows="2" class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg"></textarea>
            </div>
            <div>
                <label class="block text-[10px] font-semibold text-slate-500 mb-1">Orientação Contábil Associada</label>
                <textarea name="exigencia_orientacao_contabil" rows="2" class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg"></textarea>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="fecharModais()" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold">Cancelar</button>
                <button type="submit" class="px-3 py-1.5 bg-primary text-white rounded-lg text-xs font-bold">Salvar Decisão</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Conciliação Bancária -->
<div id="modal-conciliacao" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center hidden">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-xl shadow-xl w-full max-w-md space-y-4">
        <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase">Conciliação Bancária Manual</h3>
        <form id="form-conciliacao" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-[10px] font-semibold text-slate-500 mb-1">Conta Bancária da Campanha</label>
                <input type="text" name="conta_bancaria_campanha" required class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg">
            </div>
            <div>
                <label class="block text-[10px] font-semibold text-slate-500 mb-1">Data da Transação no Extrato</label>
                <input type="date" name="data_transacao_bancaria" required class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg">
            </div>
            <div>
                <label class="block text-[10px] font-semibold text-slate-500 mb-1">Identificador da Transação (DOC/TED/Pix)</label>
                <input type="text" name="identificador_bancario" required class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg">
            </div>
            <div>
                <label class="block text-[10px] font-semibold text-slate-500 mb-1">Valor no Extrato (R$)</label>
                <input type="number" step="0.01" name="valor_bancario" required class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg">
            </div>
            <div>
                <label class="block text-[10px] font-semibold text-slate-500 mb-1">Situação da Conciliação</label>
                <select name="situacao_conciliacao" required class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg">
                    <option value="conciliado">Conciliado (Valores Batem)</option>
                    <option value="divergente">Divergente</option>
                    <option value="aguardando_documento">Aguardando Documento Complementar</option>
                    <option value="estornado">Estornado</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-semibold text-slate-500 mb-1">Divergência Identificada (se houver)</label>
                <textarea name="divergencia_identificada" rows="2" class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg"></textarea>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="fecharModais()" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold">Cancelar</button>
                <button type="submit" class="px-3 py-1.5 bg-primary text-white rounded-lg text-xs font-bold">Conciliar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Solicitar Retificação -->
<div id="modal-retificacao-solicitar" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center hidden">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-xl shadow-xl w-full max-w-md space-y-4">
        <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase">Solicitar Retificação Contábil</h3>
        <form id="form-retificacao-solicitar" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-[10px] font-semibold text-slate-500 mb-1">Motivo/Justificativa da Retificação</label>
                <textarea name="motivo" required rows="3" class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg"></textarea>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="fecharModais()" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold">Cancelar</button>
                <button type="submit" class="px-3 py-1.5 bg-red-500 text-white rounded-lg text-xs font-bold">Solicitar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Registrar Recibo Oficial (TSE) -->
<div id="modal-recibo-oficial" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center hidden">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-xl shadow-xl w-full max-w-md space-y-4">
        <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase">Registrar Recibo Oficial do TSE</h3>
        <form id="form-recibo-oficial" method="POST" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <div>
                <label class="block text-[10px] font-semibold text-slate-500 mb-1">Número do Recibo Oficial (TSE)</label>
                <input type="text" name="recibo_oficial_numero" required placeholder="Ex: 12345/2026" class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg">
            </div>
            <div>
                <label class="block text-[10px] font-semibold text-slate-500 mb-1">Data de Emissão no TSE</label>
                <input type="date" name="recibo_oficial_data_emissao" required class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg">
            </div>
            <div>
                <label class="block text-[10px] font-semibold text-slate-500 mb-1">Upload do Arquivo do Recibo Oficial</label>
                <input type="file" name="recibo_oficial_arquivo" class="w-full text-xs text-slate-500 file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:bg-slate-100 dark:file:bg-slate-800 hover:file:bg-slate-200">
            </div>
            <div>
                <label class="block text-[10px] font-semibold text-slate-500 mb-1">Observações do Recibo</label>
                <textarea name="recibo_oficial_observacao" rows="2" class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg"></textarea>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="fecharModais()" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold">Cancelar</button>
                <button type="submit" class="px-3 py-1.5 bg-primary text-white rounded-lg text-xs font-bold">Registrar</button>
            </div>
        </form>
    </div>
</div>

<script>
    function alternarCampos() {
        const tipo = document.getElementById('lancamento_tipo').value;
        const categoria = document.getElementById('lancamento_categoria');
        const docLabel = document.getElementById('doc_label');
        const nomeLabel = document.getElementById('nome_label');

        categoria.innerHTML = '';

        if (tipo === 'receita') {
            docLabel.innerText = 'CPF/CNPJ do Ofertante';
            nomeLabel.innerText = 'Nome do Ofertante';
            categoria.innerHTML = `
                <option value="doacao_pf">Doação financeira de pessoa física</option>
                <option value="doacao_estimavel">Doação estimável em dinheiro</option>
                <option value="recursos_proprios">Recursos próprios do candidato</option>
                <option value="fundo_partidario">Repasse de Fundo Partidário</option>
                <option value="fundo_especial">Repasse do Fundo Especial de Financiamento de Campanha</option>
                <option value="transferência_candidato">Transferência de outro candidato</option>
                <option value="transferencia_partido">Transferência de partido</option>
                <option value="financiamento_coletivo">Financiamento coletivo</option>
                <option value="rendimento_aplicacao">Rendimento de aplicação financeira</option>
                <option value="sobra_campanha">Sobra financeira de campanha anterior</option>
                <option value="outro_autorizado">Outro tipo autorizado e validado</option>
            `;
        } else {
            docLabel.innerText = 'CPF/CNPJ do Fornecedor';
            nomeLabel.innerText = 'Nome/Razão Social do Fornecedor';
            categoria.innerHTML = `
                <option value="Pessoal (Militância/Equipe)">Pessoal (Militância/Equipe)</option>
                <option value="Material de Campanha">Material de Campanha</option>
                <option value="Combustível">Combustível</option>
                <option value="Aluguel de Comitê">Aluguel de Comitê</option>
                <option value="Produção de Vídeo">Produção de Vídeo</option>
                <option value="Publicidade">Publicidade</option>
                <option value="Serviços Advocatícios">Serviços Advocatícios</option>
                <option value="Serviços Contábeis">Serviços Contábeis</option>
                <option value="Outra">Outra</option>
            `;
        }
    }

    function abrirModalExigencia(id, item) {
        document.getElementById('form-exigencia').action = `/financeiro/${id}/exigencia`;
        document.getElementById('modal-exigencia').classList.remove('hidden');
    }

    function abrirModalConciliacao(id, item) {
        document.getElementById('form-conciliacao').action = `/financeiro/${id}/conciliar`;
        document.getElementById('modal-conciliacao').classList.remove('hidden');
    }

    function abrirModalRetificacaoSolicitar(id) {
        document.getElementById('form-retificacao-solicitar').action = `/financeiro/${id}/solicitar-retificacao`;
        document.getElementById('modal-retificacao-solicitar').classList.remove('hidden');
    }

    function abrirModalRegistroReciboOficial(id) {
        document.getElementById('form-recibo-oficial').action = `/financeiro/${id}/recibo-oficial`;
        document.getElementById('modal-recibo-oficial').classList.remove('hidden');
    }

    function fecharModais() {
        document.getElementById('modal-exigencia').classList.add('hidden');
        document.getElementById('modal-conciliacao').classList.add('hidden');
        document.getElementById('modal-retificacao-solicitar').classList.add('hidden');
        document.getElementById('modal-recibo-oficial').classList.add('hidden');
    }
</script>
@endsection
