<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Bairro;
use App\Models\Municipio;
use App\Models\Regiao;
use App\Models\Evento;
use App\Models\BancoPauta;
use App\Models\ConteudoMarketing;
use App\Models\VeiculoImprensa;
use App\Models\SolicitacaoImprensa;
use App\Models\Entrevista;
use App\Models\Material;
use App\Models\MaterialMovimentacao;
use App\Models\Kit;
use App\Models\Arquivo;
use App\Models\ArquivoVersao;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class MarketingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $marketingUser;
    protected User $operacionalUser;
    protected User $consultaUser;
    
    protected Bairro $bairro;
    protected VeiculoImprensa $veiculo;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Criar papéis e permissões
        $roleAdmin = Role::create(['name' => 'admin']);
        $roleMarketing = Role::create(['name' => 'marketing']);
        $roleOperacional = Role::create(['name' => 'operacional']);
        $roleConsulta = Role::create(['name' => 'consulta']);

        $permissions = [
            'marketing.visualizar', 'marketing.criar', 'marketing.editar', 'marketing.excluir', 'marketing.aprovar', 'marketing.registrar_resultados',
            'pautas.visualizar', 'pautas.criar', 'pautas.editar', 'pautas.transformar_em_conteudo', 'pautas.arquivar',
            'imprensa.visualizar', 'imprensa.criar', 'imprensa.editar', 'imprensa.responder', 'imprensa.visualizar_briefing',
            'materiais.visualizar', 'materiais.criar', 'materiais.editar', 'materiais.movimentar', 'materiais.ajustar_estoque',
            'arquivos.visualizar', 'arquivos.enviar', 'arquivos.baixar',
        ];

        foreach ($permissions as $p) {
            Permission::create(['name' => $p]);
        }

        $roleAdmin->syncPermissions(Permission::all());
        $roleMarketing->syncPermissions([
            'marketing.visualizar', 'marketing.criar', 'marketing.editar', 'marketing.registrar_resultados',
            'pautas.visualizar', 'pautas.criar', 'pautas.editar', 'pautas.transformar_em_conteudo',
            'imprensa.visualizar', 'imprensa.criar', 'imprensa.editar', 'imprensa.responder', 'imprensa.visualizar_briefing',
            'arquivos.visualizar', 'arquivos.enviar', 'arquivos.baixar',
        ]);
        $roleOperacional->syncPermissions([
            'materiais.visualizar', 'materiais.criar', 'materiais.editar', 'materiais.movimentar',
        ]);
        $roleConsulta->syncPermissions([
            'marketing.visualizar', 'imprensa.visualizar', 'materiais.visualizar', 'arquivos.visualizar'
        ]);

        // 2. Criar Usuários
        $this->admin = User::factory()->create();
        $this->admin->assignRole($roleAdmin);

        $this->marketingUser = User::factory()->create();
        $this->marketingUser->assignRole($roleMarketing);

        $this->operacionalUser = User::factory()->create();
        $this->operacionalUser->assignRole($roleOperacional);

        $this->consultaUser = User::factory()->create();
        $this->consultaUser->assignRole($roleConsulta);

        // 3. Criar divisões territoriais
        $muni = Municipio::create(['nome' => 'Limeira', 'estado' => 'SP', 'municipio_principal' => true]);
        $regiao = Regiao::create(['municipio_id' => $muni->id, 'nome' => 'Centro']);
        $this->bairro = Bairro::create([
            'municipio_id' => $muni->id,
            'regiao_id' => $regiao->id,
            'nome' => 'Centro',
            'prioridade' => 'alta',
            'populacao_estimada_manual' => 15000,
            'status_cobertura' => 'ativo',
        ]);

        // 4. Criar Veículo de Imprensa
        $this->veiculo = VeiculoImprensa::create([
            'nome' => 'Gazeta de Limeira',
            'tipo' => 'jornal',
            'cidade' => 'Limeira',
            'ativo' => true,
        ]);

        Storage::fake('local');
    }

    /** @test */
    public function criacao_de_pauta_com_sucesso()
    {
        $response = $this->actingAs($this->marketingUser)->post('/marketing/pautas', [
            'titulo' => 'Entrevista com feirantes da Vila Nova',
            'descricao' => 'Gravar com o feirante mais antigo sobre alta dos preços.',
            'origem' => 'Rua',
            'tema' => 'Economia',
            'prioridade' => 'alta',
            'responsavel_id' => $this->marketingUser->id,
            'prazo' => '2026-08-10',
        ]);

        $response->assertRedirect('/marketing');
        $this->assertDatabaseHas('banco_pautas', [
            'titulo' => 'Entrevista com feirantes da Vila Nova',
            'origem' => 'Rua',
            'status' => 'nova',
        ]);

        // Verifica auditoria
        $this->assertDatabaseHas('logs_auditoria', [
            'user_id' => $this->marketingUser->id,
            'acao' => 'criacao_pauta',
            'tabela' => 'banco_pautas',
        ]);
    }

    /** @test */
    public function pauta_transformada_em_conteudo_com_sucesso()
    {
        $pauta = BancoPauta::create([
            'titulo' => 'Pauta de Infraestrutura',
            'descricao' => 'Escrever pauta sobre buracos na Avenida Costa e Silva.',
            'prioridade' => 'normal',
            'status' => 'nova',
        ]);

        $response = $this->actingAs($this->marketingUser)->post("/marketing/pautas/{$pauta->id}/transformar");

        $response->assertRedirect('/marketing');
        $this->assertDatabaseHas('banco_pautas', [
            'id' => $pauta->id,
            'status' => 'transformada_conteudo',
        ]);

        $this->assertDatabaseHas('conteudos_marketing', [
            'titulo' => 'Pauta de Infraestrutura',
            'status' => 'pauta',
            'pauta_origem_id' => $pauta->id,
        ]);
    }

    /** @test */
    public function criacao_de_conteudo_com_sucesso()
    {
        $response = $this->actingAs($this->marketingUser)->post('/marketing/conteudos', [
            'titulo' => 'Vídeo sobre Segurança no Centro',
            'tipo' => 'Reel',
            'prioridade' => 'critica',
            'data_criacao' => '2026-08-06',
            'prazo' => '2026-08-10',
            'status' => 'ideia',
            'canais' => ['Instagram', 'TikTok'],
        ]);

        $response->assertRedirect('/marketing');
        $this->assertDatabaseHas('conteudos_marketing', [
            'titulo' => 'Vídeo sobre Segurança no Centro',
            'tipo' => 'Reel',
            'status' => 'ideia',
        ]);
    }

    /** @test */
    public function usuario_sem_permissao_nao_aprova_conteudo()
    {
        $conteudo = ConteudoMarketing::create([
            'titulo' => 'Post de Biografia',
            'tipo' => 'Foto',
            'data_criacao' => '2026-08-06',
            'status' => 'roteiro',
            'prioridade' => 'normal',
        ]);

        $response = $this->actingAs($this->consultaUser)->post("/marketing/conteudos/{$conteudo->id}/aprovar", [
            'texto_aprovado' => 'Texto final do post de biografia.',
        ]);

        $response->assertStatus(403);
        $this->assertEquals('roteiro', $conteudo->fresh()->status);
    }

    /** @test */
    public function coordenador_aprova_conteudo_com_sucesso()
    {
        $conteudo = ConteudoMarketing::create([
            'titulo' => 'Post de Biografia',
            'tipo' => 'Foto',
            'data_criacao' => '2026-08-06',
            'status' => 'roteiro',
            'prioridade' => 'normal',
        ]);

        $response = $this->actingAs($this->admin)->post("/marketing/conteudos/{$conteudo->id}/aprovar", [
            'texto_aprovado' => 'Texto final aprovado pela coordenação.',
        ]);

        $response->assertRedirect('/marketing');
        $this->assertDatabaseHas('conteudos_marketing', [
            'id' => $conteudo->id,
            'status' => 'aprovado',
            'aprovado_por_id' => $this->admin->id,
            'texto_approved' => 'Texto final aprovado pela coordenação.',
        ]);
    }

    /** @test */
    public function alteracao_apos_aprovacao_retorna_para_revisao()
    {
        $conteudo = ConteudoMarketing::create([
            'titulo' => 'Peça de Despedida',
            'tipo' => 'Foto',
            'data_criacao' => '2026-08-06',
            'status' => 'aprovado',
            'prioridade' => 'normal',
            'roteiro_texto' => 'Obrigado Limeira!',
            'chamada_principal' => 'Até breve',
        ]);

        // Edita o texto
        $response = $this->actingAs($this->marketingUser)->post("/marketing/conteudos/{$conteudo->id}", [
            'titulo' => 'Peça de Despedida Alterada', // Mudança no texto principal
            'tipo' => 'Foto',
            'prioridade' => 'normal',
            'roteiro_texto' => 'Obrigado Limeira e Região!', // Mudou!
            'chamada_principal' => 'Até breve',
            'status' => 'aprovado', // Tenta manter aprovado
        ]);

        $response->assertRedirect('/marketing');
        $this->assertEquals('aguardando_aprovação', $conteudo->fresh()->status);
    }

    /** @test */
    public function registro_manual_de_metricas_com_sucesso()
    {
        $conteudo = ConteudoMarketing::create([
            'titulo' => 'Vídeo de Campanha 01',
            'tipo' => 'Reel',
            'data_criacao' => '2026-08-06',
            'status' => 'aprovado',
            'prioridade' => 'normal',
        ]);

        $response = $this->actingAs($this->marketingUser)->post("/marketing/conteudos/{$conteudo->id}/resultados", [
            'metricas_visualizacoes' => 12500,
            'metricas_alcance' => 10000,
            'metricas_curtidas' => 1200,
            'metricas_comentarios' => 150,
            'metricas_compartilhamentos' => 45,
            'metricas_salvamentos' => 30,
            'metricas_cliques' => 200,
            'metricas_mensagens' => 15,
            'metricas_contatos_gerados' => 12,
            'metricas_desempenho_obs' => 'Desempenho excelente no orgânico.',
        ]);

        $response->assertRedirect('/marketing');
        $this->assertDatabaseHas('conteudos_marketing', [
            'id' => $conteudo->id,
            'status' => 'publicado',
            'metricas_visualizacoes' => 12500,
            'metricas_contatos_gerados' => 12,
        ]);
    }

    /** @test */
    public function criacao_de_solicitacao_de_imprensa()
    {
        $response = $this->actingAs($this->marketingUser)->post('/imprensa/solicitacoes', [
            'veiculo_id' => $this->veiculo->id,
            'pauta' => 'Solicitação de posicionamento sobre segurança nas escolas.',
            'data_recebida' => '2026-08-06',
            'prazo_resposta' => '2026-08-07',
            'responsavel_id' => $this->marketingUser->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('solicitacoes_imprensa', [
            'veiculo_id' => $this->veiculo->id,
            'pauta' => 'Solicitação de posicionamento sobre segurança nas escolas.',
            'status' => 'recebida',
        ]);
    }

    /** @test */
    public function criacao_de_entrevista_e_sinc_com_agenda()
    {
        $response = $this->actingAs($this->marketingUser)->post('/imprensa/entrevistas', [
            'veiculo_id' => $this->veiculo->id,
            'pauta' => 'Sabatinas do Município',
            'data' => '2026-08-15',
            'horario' => '14:00',
            'local_link' => 'Estúdio Central',
            'responsavel_id' => $this->marketingUser->id,
            'porta_voz' => 'Guto Schiavetto',
            'status' => 'confirmada',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('entrevistas', [
            'pauta' => 'Sabatinas do Município',
            'status' => 'confirmada',
        ]);

        // Regra: Entrevista confirmada cria evento na agenda de forma sincronizada
        $this->assertDatabaseHas('eventos', [
            'tipo' => 'entrevista',
            'status' => 'confirmado',
            'prioridade' => 'obrigatoria',
        ]);
    }

    /** @test */
    public function briefing_protegido_por_permissao()
    {
        $entrevista = Entrevista::create([
            'veiculo_id' => $this->veiculo->id,
            'pauta' => 'Entrevista Rádio',
            'data' => '2026-08-06',
            'horario' => '08:00',
            'status' => 'confirmada',
            'briefing' => 'Diretrizes ultraconfidenciadas de posicionamento.',
        ]);

        // Consulta sem permissão de briefing recebe 403
        $response = $this->actingAs($this->consultaUser)->get("/imprensa/entrevistas/{$entrevista->id}/briefing");
        $response->assertStatus(403);

        // Marketing com acesso consegue ver
        $responseOk = $this->actingAs($this->marketingUser)->get("/imprensa/entrevistas/{$entrevista->id}/briefing");
        $responseOk->assertStatus(200);
        $responseOk->assertSee('Diretrizes ultraconfidenciadas');
    }

    /** @test */
    public function criacao_de_material()
    {
        $response = $this->actingAs($this->operacionalUser)->post('/materiais', [
            'nome' => 'Bandeiras 1x1.5m Guto',
            'categoria' => 'bandeiras',
            'quantidade_atual' => 50,
            'quantidade_minima' => 10,
            'unidade' => 'un',
            'estado_conservacao' => 'novo',
            'valor_estimado' => 15.00,
        ]);

        $response->assertRedirect('/materiais');
        $this->assertDatabaseHas('materiais', [
            'nome' => 'Bandeiras 1x1.5m Guto',
            'quantidade_atual' => 50,
        ]);
    }

    /** @test */
    public function entrada_e_saida_de_estoque_com_auditoria()
    {
        $material = Material::create([
            'nome' => 'Panfletos Saúde',
            'categoria' => 'santinhos',
            'quantidade_atual' => 1000,
            'quantidade_minima' => 200,
            'unidade' => 'milheiro',
            'estado_conservacao' => 'novo',
        ]);

        // Entrada
        $this->actingAs($this->operacionalUser)->post("/materiais/{$material->id}/movimentar", [
            'tipo_movimentacao' => 'entrada',
            'quantidade' => 500,
        ]);
        $this->assertEquals(1500, $material->fresh()->quantidade_atual);

        // Saída
        $this->actingAs($this->operacionalUser)->post("/materiais/{$material->id}/movimentar", [
            'tipo_movimentacao' => 'saida',
            'quantidade' => 300,
        ]);
        $this->assertEquals(1200, $material->fresh()->quantidade_atual);
    }

    /** @test */
    public function bloqueio_de_estoque_negativo()
    {
        $material = Material::create([
            'nome' => 'Caixa de Som Grande',
            'categoria' => 'equipamento_audio',
            'quantidade_atual' => 1,
            'quantidade_minima' => 0,
            'unidade' => 'un',
            'estado_conservacao' => 'bom',
        ]);

        // Tenta retirar 2 (usuário operacional comum)
        $response = $this->actingAs($this->operacionalUser)->post("/materiais/{$material->id}/movimentar", [
            'tipo_movimentacao' => 'saida',
            'quantidade' => 2,
        ]);

        $response->assertSessionHasErrors('quantidade');
        $this->assertEquals(1, $material->fresh()->quantidade_atual);
    }

    /** @test */
    public function ajuste_de_estoque_por_usuario_autorizado()
    {
        $material = Material::create([
            'nome' => 'Tripé Alumínio',
            'categoria' => 'outro',
            'quantidade_atual' => 1,
            'quantidade_minima' => 0,
            'unidade' => 'un',
            'estado_conservacao' => 'bom',
        ]);

        // Admin (tem permissão materiais.ajustar_estoque) consegue forçar saldo negativo se necessário
        $response = $this->actingAs($this->admin)->post("/materiais/{$material->id}/movimentar", [
            'tipo_movimentacao' => 'saida',
            'quantidade' => 3,
        ]);

        $response->assertRedirect('/materiais');
        $this->assertEquals(-2, $material->fresh()->quantidade_atual);
    }

    /** @test */
    public function alerta_de_estoque_minimo()
    {
        $material = Material::create([
            'nome' => 'Praguinha Adesiva',
            'categoria' => 'adesivos',
            'quantidade_atual' => 500,
            'quantidade_minima' => 100,
            'unidade' => 'un',
            'estado_conservacao' => 'novo',
        ]);

        // Retira 450, sobrando 50 (abaixo do estoque mínimo de 100)
        $this->actingAs($this->operacionalUser)->post("/materiais/{$material->id}/movimentar", [
            'tipo_movimentacao' => 'saida',
            'quantidade' => 450,
        ]);

        $this->assertDatabaseHas('timeline', [
            'tipo_evento' => 'materiais.estoque_critico',
        ]);
    }

    /** @test */
    public function criacao_de_kit_e_associacao_a_evento()
    {
        $material = Material::create([
            'nome' => 'Bandeiras com Cabo',
            'categoria' => 'bandeiras',
            'quantidade_atual' => 20,
            'quantidade_minima' => 5,
            'unidade' => 'un',
            'estado_conservacao' => 'bom',
        ]);

        // Cria Kit
        $response = $this->actingAs($this->admin)->post('/materiais/kits', [
            'nome' => 'Kit Mobilização Esquinas',
            'descricao' => 'Materiais essenciais para cruzamentos.',
            'materiais' => [$material->id],
            'quantidades' => [10],
        ]);

        $response->assertRedirect('/materiais');
        $this->assertDatabaseHas('kits', ['nome' => 'Kit Mobilização Esquinas']);

        // Associa a Evento
        $evento = Evento::create([
            'titulo' => 'Ato da Tarde',
            'tipo' => 'caminhada',
            'data_hora_inicio' => '2026-08-06 17:00:00',
            'data_hora_fim' => '2026-08-06 18:00:00',
            'prioridade' => 'importante',
            'status' => 'confirmado',
        ]);

        $kit = Kit::first();
        $responseSinc = $this->actingAs($this->admin)->post("/materiais/kits/{$kit->id}/associar", [
            'evento_id' => $evento->id,
        ]);

        $responseSinc->assertRedirect();
        $this->assertDatabaseHas('kit_evento', [
            'kit_id' => $kit->id,
            'evento_id' => $evento->id,
        ]);
    }

    /** @test */
    public function upload_valido_e_bloqueio_de_extensao_proibida()
    {
        // Upload PDF válido
        $pdf = UploadedFile::fake()->create('documento.pdf', 500);
        $response = $this->actingAs($this->marketingUser)->post('/arquivos', [
            'nome' => 'Identidade Visual PDF',
            'categoria' => 'identidade_visual',
            'is_link_externo' => 0,
            'arquivo_local' => $pdf,
        ]);

        $response->assertRedirect('/arquivos');
        $this->assertDatabaseHas('arquivos', [
            'nome' => 'Identidade Visual PDF',
            'is_link_externo' => false,
        ]);

        // Bloqueio de extensão perigosa (ex: .exe, .sh)
        $exe = UploadedFile::fake()->create('script_malicioso.exe', 100);
        $responseFail = $this->actingAs($this->marketingUser)->post('/arquivos', [
            'nome' => 'Script Perigoso',
            'categoria' => 'identidade_visual',
            'is_link_externo' => 0,
            'arquivo_local' => $exe,
        ]);

        $responseFail->assertSessionHasErrors('arquivo_local');
    }

    /** @test */
    public function download_protegido()
    {
        $pdf = UploadedFile::fake()->create('roteiro_confidencial.pdf', 200);
        
        $this->actingAs($this->marketingUser)->post('/arquivos', [
            'nome' => 'Roteiro Confidencial',
            'categoria' => 'roteiro',
            'is_link_externo' => 0,
            'arquivo_local' => $pdf,
        ]);

        $arquivo = Arquivo::first();

        // Usuário não logado ou sem permissão de baixar arquivos restritos
        $response = $this->actingAs($this->consultaUser)->get("/arquivos/{$arquivo->id}/download");
        // ConsultaUser não possui a permissão 'arquivos.baixar'
        $response->assertStatus(403);
    }

    /** @test */
    public function nova_versao_de_roteiro_cria_historico()
    {
        $pdfv1 = UploadedFile::fake()->create('versao1.pdf', 200);
        $this->actingAs($this->marketingUser)->post('/arquivos', [
            'nome' => 'Discurso Final',
            'categoria' => 'roteiro',
            'is_link_externo' => 0,
            'arquivo_local' => $pdfv1,
        ]);

        $arquivo = Arquivo::first();

        // Adiciona v2
        $pdfv2 = UploadedFile::fake()->create('versao2.pdf', 300);
        $response = $this->actingAs($this->marketingUser)->post("/arquivos/{$arquivo->id}/versao", [
            'observacao' => 'Ajuste v2 do discurso',
            'arquivo_local' => $pdfv2,
        ]);

        $response->assertRedirect();
        $this->assertEquals(2, $arquivo->fresh()->versao);
        $this->assertDatabaseHas('arquivos_versoes', [
            'arquivo_id' => $arquivo->id,
            'versao' => 2,
            'observacao' => 'Ajuste v2 do discurso',
        ]);
    }
}
