<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WarRoomController;
use App\Http\Controllers\TarefaController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\MuralController;
use App\Http\Controllers\ConfiguracaoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\RelacionamentoController;
use App\Http\Controllers\TerritorioController;
use App\Http\Controllers\DemandaController;

use App\Http\Controllers\MarketingController;
use App\Http\Controllers\ImprensaController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\ArquivoController;
use App\Http\Controllers\TransversalController;
use App\Http\Controllers\FinanceiroController;

// Rota inicial / Redireciona para o login ou war room
Route::get('/', function () {
    return redirect()->route('warroom');
});

// Autenticação nativa
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rotas protegidas por login
Route::middleware(['auth'])->group(function () {
    Route::get('/warroom', [WarRoomController::class, 'index'])->name('warroom');
    
    // Prioridades do War Room
    Route::post('/warroom/prioridades', [WarRoomController::class, 'adicionarPrioridade'])->name('warroom.prioridades.adicionar');
    Route::post('/warroom/prioridades/{id}/toggle', [WarRoomController::class, 'atualizarPrioridade'])->name('warroom.prioridades.toggle');
    Route::post('/warroom/prioridades/{id}/deletar', [WarRoomController::class, 'removerPrioridade'])->name('warroom.prioridades.remover');

    // Módulo 7: Tarefas
    Route::get('/tarefas', [TarefaController::class, 'index'])->name('tarefas.index');
    Route::post('/tarefas', [TarefaController::class, 'store'])->name('tarefas.store');
    Route::post('/tarefas/{id}/status', [TarefaController::class, 'updateStatus']);
    Route::post('/tarefas/{id}/checklist/{itemIndex}', [TarefaController::class, 'toggleChecklistItem']);

    // Módulo 4: Agenda e Eventos
    Route::get('/eventos', [EventoController::class, 'index'])->name('eventos.index');
    Route::post('/eventos', [EventoController::class, 'store'])->name('eventos.store');
    Route::post('/eventos/{id}/status', [EventoController::class, 'updateStatus']);
    Route::post('/eventos/{id}/checklist/{itemIndex}', [EventoController::class, 'toggleChecklistItem']);
    Route::post('/eventos/{id}/deletar', [EventoController::class, 'remover'])->name('eventos.destroy');

    // Módulo 11: Mural
    Route::get('/mural', [MuralController::class, 'index'])->name('mural.index');
    Route::post('/mural', [MuralController::class, 'store'])->name('mural.store');
    Route::post('/mural/{id}/deletar', [MuralController::class, 'destroy'])->name('mural.destroy');
    Route::get('/mural/anexo/download', [MuralController::class, 'baixarAnexo'])->name('anexos.download');

    // Módulo Configurações
    Route::get('/configuracoes', [ConfiguracaoController::class, 'index'])->name('configuracoes.index');
    Route::post('/configuracoes', [ConfiguracaoController::class, 'update'])->name('configuracoes.update');

    // Módulo Usuários (RBAC)
    Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
    Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
    Route::post('/usuarios/{id}', [UsuarioController::class, 'update'])->name('usuarios.update');
    Route::post('/usuarios/{id}/status', [UsuarioController::class, 'toggleStatus'])->name('usuarios.status.toggle');
    Route::post('/usuarios/{id}/senha', [UsuarioController::class, 'redefinirSenha'])->name('usuarios.senha.redefinir');
    Route::post('/usuarios/{id}/permissoes', [UsuarioController::class, 'gerenciarPermissoes'])->name('usuarios.permissoes.gerenciar');
    Route::get('/usuarios/{id}/logs', [UsuarioController::class, 'logs'])->name('usuarios.logs');

    // Módulo 2 e 3: Relacionamentos e Lideranças
    Route::get('/relacionamentos', [RelacionamentoController::class, 'index'])->name('relacionamentos.index');
    Route::post('/relacionamentos', [RelacionamentoController::class, 'store'])->name('relacionamentos.store');
    Route::post('/relacionamentos/{id}', [RelacionamentoController::class, 'update'])->name('relacionamentos.update');
    Route::post('/relacionamentos/{id}/deletar', [RelacionamentoController::class, 'destroy'])->name('relacionamentos.destroy');
    Route::post('/relacionamentos/{id}/interacoes', [RelacionamentoController::class, 'adicionarInteracao'])->name('relacionamentos.interacao.adicionar');
    Route::post('/relacionamentos/importar/preview', [RelacionamentoController::class, 'previsualizarImportacao'])->name('relacionamentos.importar.preview');
    Route::post('/relacionamentos/importar/confirmar', [RelacionamentoController::class, 'confirmarImportacao'])->name('relacionamentos.importar.confirmar');

    // Módulo 5: Território
    Route::get('/territorio', [TerritorioController::class, 'index'])->name('territorio.index');
    Route::post('/territorio/municipios', [TerritorioController::class, 'storeMunicipio'])->name('territorio.municipio.store');
    Route::post('/territorio/bairros', [TerritorioController::class, 'storeBairro'])->name('territorio.bairro.store');
    Route::get('/territorio/bairros/{id}', [TerritorioController::class, 'showBairro'])->name('territorio.ficha');
    Route::post('/territorio/bairros/{id}/status', [TerritorioController::class, 'updateStatus'])->name('territorio.bairro.status');
    Route::post('/territorio/bairros/{id}/meta', [TerritorioController::class, 'storeMeta'])->name('territorio.bairro.meta');
    Route::post('/territorio/bairros/{id}/local', [TerritorioController::class, 'storeLocalEstrategico'])->name('territorio.bairro.local.store');
    Route::post('/territorio/bairros/{id}/deletar', [TerritorioController::class, 'remover'])->name('territorio.bairro.destroy');

    // Módulo 6: Demandas e Compromissos
    Route::get('/demandas', [DemandaController::class, 'index'])->name('demandas.index');
    Route::post('/demandas', [DemandaController::class, 'store'])->name('demandas.store');
    Route::post('/demandas/{id}/aprovar', [DemandaController::class, 'aprovarCompromisso'])->name('demandas.aprovar');
    Route::post('/demandas/{id}/status', [DemandaController::class, 'updateStatus'])->name('demandas.status');
    Route::post('/demandas/{id}/deletar', [DemandaController::class, 'remover'])->name('demandas.destroy');

    // Módulo Marketing e Conteúdos
    Route::get('/marketing', [MarketingController::class, 'index'])->name('marketing.index');
    Route::post('/marketing/conteudos', [MarketingController::class, 'store'])->name('marketing.store');
    Route::post('/marketing/conteudos/{id}', [MarketingController::class, 'update'])->name('marketing.update');
    Route::post('/marketing/conteudos/{id}/aprovar', [MarketingController::class, 'aprovar'])->name('marketing.conteudo.aprovar');
    Route::post('/marketing/conteudos/{id}/resultados', [MarketingController::class, 'registrarResultados'])->name('marketing.conteudo.resultados');
    Route::post('/marketing/pautas', [MarketingController::class, 'storePauta'])->name('marketing.pautas.store');
    Route::post('/marketing/pautas/{id}/transformar', [MarketingController::class, 'transformarPauta'])->name('marketing.pautas.transformar');

    // Módulo Imprensa
    Route::get('/imprensa', [ImprensaController::class, 'index'])->name('imprensa.index');
    Route::post('/imprensa/veiculos', [ImprensaController::class, 'storeVeiculo'])->name('imprensa.veiculos.store');
    Route::post('/imprensa/solicitacoes', [ImprensaController::class, 'storeSolicitacao'])->name('imprensa.solicitacoes.store');
    Route::post('/imprensa/entrevistas', [ImprensaController::class, 'storeEntrevista'])->name('imprensa.entrevistas.store');
    Route::get('/imprensa/entrevistas/{id}/briefing', [ImprensaController::class, 'verBriefing'])->name('imprensa.entrevistas.briefing');

    // Módulo Materiais e Estoque
    Route::get('/materiais', [MaterialController::class, 'index'])->name('materiais.index');
    Route::post('/materiais', [MaterialController::class, 'store'])->name('materiais.store');
    Route::post('/materiais/{id}/movimentar', [MaterialController::class, 'movimentar'])->name('materiais.movimentar');
    Route::post('/materiais/kits', [MaterialController::class, 'storeKit'])->name('materiais.kits.store');
    Route::post('/materiais/kits/{id}/associar', [MaterialController::class, 'associarKitEvento'])->name('materiais.kits.associar');

    // Módulo Biblioteca Arquivos
    Route::get('/arquivos', [ArquivoController::class, 'index'])->name('arquivos.index');
    Route::post('/arquivos', [ArquivoController::class, 'store'])->name('arquivos.store');
    Route::get('/arquivos/{id}/download', [ArquivoController::class, 'download'])->name('arquivos.download');
    Route::post('/arquivos/{id}/versao', [ArquivoController::class, 'novaVersao'])->name('arquivos.versao');

    // Módulo Financeiro
    Route::get('/financeiro', [FinanceiroController::class, 'index'])->name('financeiro.index');
    Route::post('/financeiro', [FinanceiroController::class, 'store'])->name('financeiro.store');
    Route::post('/financeiro/{id}/rascunho', [FinanceiroController::class, 'atualizarRascunho'])->name('financeiro.rascunho.update');
    Route::post('/financeiro/{id}/exigencia', [FinanceiroController::class, 'definirExigenciaRecibo'])->name('financeiro.exigencia');
    Route::post('/financeiro/{id}/recibo-oficial', [FinanceiroController::class, 'registrarReciboOficial'])->name('financeiro.recibo_oficial');
    Route::post('/financeiro/{id}/conciliar', [FinanceiroController::class, 'conciliar'])->name('financeiro.conciliar');
    Route::post('/financeiro/{id}/conferir', [FinanceiroController::class, 'conferir'])->name('financeiro.conferir');
    Route::post('/financeiro/{id}/solicitar-retificacao', [FinanceiroController::class, 'solicitarRetificacao'])->name('financeiro.solicitar_retificacao');
    Route::post('/financeiro/{id}/retificar', [FinanceiroController::class, 'retificar'])->name('financeiro.retificar');
    Route::post('/financeiro/{id}/anexar', [FinanceiroController::class, 'anexarDocumento'])->name('financeiro.anexar');
    Route::get('/financeiro/documento/{id}/baixar', [FinanceiroController::class, 'baixarDocumento'])->name('financeiro.baixar_documento');
    Route::get('/financeiro/{id}/registro-interno', [FinanceiroController::class, 'gerarRegistroInterno'])->name('financeiro.registro_interno');
    Route::post('/financeiro/{id}/deletar', [FinanceiroController::class, 'remover'])->name('financeiro.destroy');

    // Funcionalidades Transversais
    Route::get('/pesquisa', [TransversalController::class, 'pesquisaGlobal'])->name('pesquisa.global');
    Route::post('/favoritos/toggle', [TransversalController::class, 'favoritar'])->name('favoritos.toggle');
    Route::get('/favoritos', [TransversalController::class, 'listarFavoritos'])->name('favoritos.listar');
});

// Rota de teste do middleware role (Admin)
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/usuarios', function () {
        return 'Área administrativa';
    })->name('admin.usuarios');
});
