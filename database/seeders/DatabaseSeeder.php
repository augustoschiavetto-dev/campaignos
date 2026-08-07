<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Bairro;
use App\Models\Municipio;
use App\Models\Regiao;
use App\Models\Tag;
use App\Models\ConfiguracaoCampanha;
use App\Models\TipoRelacionamento;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Limpar cache de permissões do Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Criar Permissões Granulares
        $permissoes = [
            // Usuários e Acessos
            'usuarios.visualizar', 'usuarios.criar', 'usuarios.editar', 'usuarios.inativar', 'usuarios.gerenciar_permissoes',
            // Configurações
            'configuracoes.visualizar', 'configuracoes.editar',
            // Tarefas
            'tarefas.visualizar', 'tarefas.criar', 'tarefas.editar', 'tarefas.excluir',
            // Eventos / Agenda
            'eventos.visualizar', 'eventos.criar', 'eventos.editar', 'eventos.cancelar',
            // Mural
            'mural.visualizar', 'mural.publicar', 'mural.editar', 'mural.remover',
            
            // CRM
            'relacionamentos.visualizar', 'relacionamentos.criar', 'relacionamentos.editar', 'relacionamentos.excluir', 'relacionamentos.exportar',
            'liderancas.visualizar', 'liderancas.criar', 'liderancas.editar',
            
            // Território
            'territorio.visualizar', 'territorio.criar', 'territorio.editar', 'territorio.inativar', 'territorio.gerenciar_metas',
            'locais_estrategicos.visualizar', 'locais_estrategicos.criar', 'locais_estrategicos.editar',
            
            // Demandas e Compromissos
            'demandas.visualizar', 'demandas.criar', 'demandas.editar', 'demandas.concluir', 'demandas.cancelar',
            'compromissos.aprovar', 'compromissos.visualizar_observacoes_internas',
            
            // Marketing (Módulo 5)
            'marketing.visualizar', 'marketing.criar', 'marketing.editar', 'marketing.excluir', 'marketing.aprovar', 'marketing.registrar_resultados', 'marketing.visualizar_observacoes_internas',
            'pautas.visualizar', 'pautas.criar', 'pautas.editar', 'pautas.transformar_em_conteudo', 'pautas.arquivar',
            'imprensa.visualizar', 'imprensa.criar', 'imprensa.editar', 'imprensa.responder', 'imprensa.visualizar_briefing',
            'materiais.visualizar', 'materiais.criar', 'materiais.editar', 'materiais.movimentar', 'materiais.ajustar_estoque', 'materiais.baixar', 'materiais.descartar',
            'arquivos.visualizar', 'arquivos.enviar', 'arquivos.editar', 'arquivos.baixar', 'arquivos.inativar',
            
            'financeiro.visualizar', 'financeiro.criar', 'financeiro.editar_rascunho', 'financeiro.conferir',
            'financeiro.solicitar_retificacao', 'financeiro.retificar', 'financeiro.conciliar',
            'financeiro.classificar_receita', 'financeiro.definir_exigencia_recibo',
            'financeiro.registrar_recibo_oficial', 'financeiro.baixar_documentos',
            'financeiro.visualizar_dados_fiscais', 'financeiro.visualizar_auditoria',
            'exportacoes.executar', 'relatorios.visualizar'
        ];

        foreach ($permissoes as $permissao) {
            Permission::findOrCreate($permissao);
        }

        // 3. Criar Roles (Papéis)
        $roleAdmin = Role::findOrCreate('admin');
        $roleCoordenador = Role::findOrCreate('coordenador');
        $roleAgenda = Role::findOrCreate('agenda');
        $roleMarketing = Role::findOrCreate('marketing');
        $roleFinanceiro = Role::findOrCreate('financeiro');
        $roleOperacional = Role::findOrCreate('operacional');
        $roleConsulta = Role::findOrCreate('consulta');

        // 4. Sincronizar Permissões Padrão para cada Papel
        $roleAdmin->syncPermissions(Permission::all());

        $roleCoordenador->syncPermissions(Permission::whereNotIn('name', [
            'usuarios.gerenciar_permissoes',
            'configuracoes.editar'
        ])->get());

        $roleAgenda->syncPermissions([
            'eventos.visualizar', 'eventos.criar', 'eventos.editar', 'eventos.cancelar',
            'tarefas.visualizar', 'tarefas.criar', 'tarefas.editar',
            'mural.visualizar',
            'relacionamentos.visualizar', 'liderancas.visualizar', 'territorio.visualizar'
        ]);

        $roleMarketing->syncPermissions([
            'marketing.visualizar', 'marketing.criar', 'marketing.editar', 'marketing.excluir', 'marketing.registrar_resultados',
            'pautas.visualizar', 'pautas.criar', 'pautas.editar', 'pautas.transformar_em_conteudo',
            'imprensa.visualizar', 'imprensa.criar', 'imprensa.editar', 'imprensa.responder', 'imprensa.visualizar_briefing',
            'arquivos.visualizar', 'arquivos.enviar', 'arquivos.editar', 'arquivos.baixar',
            'mural.visualizar', 'mural.publicar', 'mural.editar', 'mural.remover',
            'tarefas.visualizar', 'tarefas.criar', 'tarefas.editar',
            'relacionamentos.visualizar'
        ]);

        $roleFinanceiro->syncPermissions([
            'financeiro.visualizar', 'financeiro.criar', 'financeiro.editar_rascunho', 'financeiro.conferir',
            'financeiro.solicitar_retificacao', 'financeiro.retificar', 'financeiro.conciliar',
            'financeiro.classificar_receita', 'financeiro.definir_exigencia_recibo',
            'financeiro.registrar_recibo_oficial', 'financeiro.baixar_documentos',
            'financeiro.visualizar_dados_fiscais', 'financeiro.visualizar_auditoria',
            'tarefas.visualizar', 'mural.visualizar', 'relacionamentos.visualizar'
        ]);

        $roleOperacional->syncPermissions([
            'territorio.visualizar', 'territorio.criar', 'territorio.editar', 'territorio.inativar', 'territorio.gerenciar_metas',
            'locais_estrategicos.visualizar', 'locais_estrategicos.criar', 'locais_estrategicos.editar',
            'demandas.visualizar', 'demandas.criar', 'demandas.editar', 'demandas.concluir',
            'materiais.visualizar', 'materiais.criar', 'materiais.editar', 'materiais.movimentar',
            'tarefas.visualizar', 'tarefas.criar', 'tarefas.editar',
            'eventos.visualizar', 'eventos.criar', 'eventos.editar', 'eventos.cancelar',
            'mural.visualizar', 'relacionamentos.visualizar', 'liderancas.visualizar'
        ]);

        $roleConsulta->syncPermissions(Permission::where('name', 'like', '%.visualizar')->get());

        // 5. Criar Tipos de Relacionamento Padrão no CRM
        $tiposSemente = [
            ['nome' => 'apoiador', 'descricao' => 'Simpatizante e apoiador da candidatura'],
            ['nome' => 'voluntario', 'descricao' => 'Voluntário ativo em panfletagem e ações'],
            ['nome' => 'lideranca', 'descricao' => 'Liderança local, comunitária ou setorial'],
            ['nome' => 'equipe', 'descricao' => 'Membro da equipe interna remunerada ou direta'],
            ['nome' => 'jornalista', 'descricao' => 'Contato de imprensa e mídia'],
            ['nome' => 'fornecedor', 'descricao' => 'Fornecedor de produtos/serviços de campanha'],
        ];

        foreach ($tiposSemente as $tipo) {
            TipoRelacionamento::updateOrCreate(['nome' => $tipo['nome']], $tipo);
        }

        // 6. Criar Usuários
        $admin = User::create([
            'name' => 'Administrador CampaignOS',
            'email' => 'admin@campaignos.com',
            'password' => Hash::make('Admin@CampaignOS2026'),
            'status' => 'ativo',
            'telefone' => '19999999991',
        ]);
        $admin->assignRole($roleAdmin);

        $coordenador = User::create([
            'name' => 'Coordenador Geral',
            'email' => 'coordenador@campaignos.com',
            'password' => Hash::make('Coord@CampaignOS2026'),
            'status' => 'ativo',
            'telefone' => '19999999992',
            'criado_por' => $admin->id
        ]);
        $coordenador->assignRole($roleCoordenador);

        $agenda = User::create([
            'name' => 'Assessor de Agenda',
            'email' => 'agenda@campaignos.com',
            'password' => Hash::make('Agenda@CampaignOS2026'),
            'status' => 'ativo',
            'telefone' => '19999999993',
            'criado_por' => $admin->id
        ]);
        $agenda->assignRole($roleAgenda);

        User::create([
            'name' => 'Assessor de Marketing',
            'email' => 'marketing@campaignos.com',
            'password' => Hash::make('Mkt@CampaignOS2026'),
            'status' => 'ativo',
            'telefone' => '19999999994',
            'criado_por' => $admin->id
        ])->assignRole($roleMarketing);

        User::create([
            'name' => 'Assessor Financeiro',
            'email' => 'financeiro@campaignos.com',
            'password' => Hash::make('Fin@CampaignOS2026'),
            'status' => 'ativo',
            'telefone' => '19999999995',
            'criado_por' => $admin->id
        ])->assignRole($roleFinanceiro);

        $operacional = User::create([
            'name' => 'Coordenador Operacional',
            'email' => 'operacional@campaignos.com',
            'password' => Hash::make('Oper@CampaignOS2026'),
            'status' => 'ativo',
            'telefone' => '19999999996',
            'criado_por' => $admin->id
        ]);
        $operacional->assignRole($roleOperacional);

        User::create([
            'name' => 'Usuário Consulta',
            'email' => 'consulta@campaignos.com',
            'password' => Hash::make('Cons@CampaignOS2026'),
            'status' => 'ativo',
            'telefone' => '19999999997',
            'criado_por' => $admin->id
        ])->assignRole($roleConsulta);

        $inativo = User::create([
            'name' => 'Voluntário Desativado',
            'email' => 'inativo@campaignos.com',
            'password' => Hash::make('Inativo@CampaignOS2026'),
            'status' => 'inativo',
            'telefone' => '19999999998',
            'criado_por' => $admin->id
        ]);
        $inativo->assignRole($roleConsulta);

        // 7. Inicializar Configurações Gerais
        ConfiguracaoCampanha::obter();

        // 8. Criar Municipio e Região padrão para associar bairros
        $muni = Municipio::create([
            'nome' => 'Limeira',
            'estado' => 'SP',
            'municipio_principal' => true,
        ]);

        $regiaoCentro = Regiao::create([
            'municipio_id' => $muni->id,
            'nome' => 'Centro',
        ]);
        
        $regiaoNorte = Regiao::create([
            'municipio_id' => $muni->id,
            'nome' => 'Norte',
        ]);
        
        $regiaoSul = Regiao::create([
            'municipio_id' => $muni->id,
            'nome' => 'Sul',
        ]);
        
        $regiaoRural = Regiao::create([
            'municipio_id' => $muni->id,
            'nome' => 'Rural',
        ]);

        // 9. Criar Bairros fictícios de Limeira-SP
        Bairro::create([
            'municipio_id' => $muni->id,
            'regiao_id' => $regiaoCentro->id,
            'nome' => 'Centro',
            'prioridade' => 'alta',
            'responsavel_id' => $coordenador->id,
            'populacao_estimada_manual' => 15000,
            'meta_contatos' => 1000,
            'status_cobertura' => 'ativo',
            'observacoes' => 'Reduto comercial e histórico de Limeira.'
        ]);

        Bairro::create([
            'municipio_id' => $muni->id,
            'regiao_id' => $regiaoNorte->id,
            'nome' => 'Vila Nova',
            'prioridade' => 'alta',
            'responsavel_id' => $operacional->id,
            'populacao_estimada_manual' => 22000,
            'meta_contatos' => 1500,
            'status_cobertura' => 'em_aproximacao',
            'observacoes' => 'Região norte com grande densidade de residências operárias.'
        ]);

        Bairro::create([
            'municipio_id' => $muni->id,
            'regiao_id' => $regiaoSul->id,
            'nome' => 'Jardim Aeroporto',
            'prioridade' => 'normal',
            'populacao_estimada_manual' => 18000,
            'meta_contatos' => 800,
            'status_cobertura' => 'em_mapeamento',
            'observacoes' => 'Região sul residencial.'
        ]);

        Bairro::create([
            'municipio_id' => $muni->id,
            'regiao_id' => $regiaoRural->id,
            'nome' => 'Bairro dos Pires',
            'prioridade' => 'baixa',
            'populacao_estimada_manual' => 3000,
            'meta_contatos' => 100,
            'status_cobertura' => 'nao_iniciado',
            'observacoes' => 'Comunidade rural produtora.'
        ]);

        // 10. Criar Tags iniciais de demonstração
        $tags = [
            'Liderança Bairro',
            'Voluntário Ativo',
            'Simpatizante',
            'Comércio',
            'Saúde',
            'Educação',
            'Segurança'
        ];

        foreach ($tags as $tagNome) {
            Tag::create([
                'nome' => $tagNome
            ]);
        }
    }
}
