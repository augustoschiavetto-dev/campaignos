<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Municipio;
use App\Models\Regiao;
use App\Models\Bairro;
use App\Models\LocalEstrategico;
use App\Models\DemandaCompromisso;
use App\Models\Relacionamento;
use App\Models\Evento;
use App\Models\TipoRelacionamento;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TerritorioTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $coordenadorUser;
    protected $comumUser;
    protected $municipio;
    protected $regiao;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Roles e Permissões Spatie
        $roleAdmin = Role::create(['name' => 'admin']);
        $roleCoordenador = Role::create(['name' => 'coordenador']);
        $roleVoluntario = Role::create(['name' => 'voluntario']);

        $permissoes = [
            'territorio.visualizar',
            'territorio.criar',
            'territorio.editar',
            'territorio.inativar',
            'territorio.gerenciar_metas',
            'locais_estrategicos.visualizar',
            'locais_estrategicos.criar',
            'locais_estrategicos.editar',
            'demandas.visualizar',
            'demandas.criar',
            'demandas.editar',
            'demandas.concluir',
            'demandas.cancelar',
            'compromissos.aprovar',
            'compromissos.visualizar_observacoes_internas',
            'relacionamentos.visualizar',
            'relacionamentos.criar'
        ];

        foreach ($permissoes as $perm) {
            Permission::create(['name' => $perm]);
        }

        // Admin tem tudo
        $roleAdmin->syncPermissions(Permission::all());
        
        // Coordenador tem a maioria das permissões, inclusive aprovar compromissos
        $roleCoordenador->syncPermissions(Permission::all());

        // Comum / Voluntário tem apenas visualizações básicas e criação de demandas, sem aprovar ou ver obs internas
        $roleVoluntario->syncPermissions([
            'territorio.visualizar',
            'demandas.visualizar',
            'demandas.criar',
            'relacionamentos.visualizar'
        ]);

        // 2. Usuários
        $this->adminUser = User::factory()->create(['status' => 'ativo']);
        $this->adminUser->assignRole($roleAdmin);

        $this->coordenadorUser = User::factory()->create(['status' => 'ativo']);
        $this->coordenadorUser->assignRole($roleCoordenador);

        $this->comumUser = User::factory()->create(['status' => 'ativo']);
        $this->comumUser->assignRole($roleVoluntario);

        // 3. Tipos de Relacionamento
        TipoRelacionamento::create(['nome' => 'lideranca', 'descricao' => 'Liderança']);
        TipoRelacionamento::create(['nome' => 'apoiador', 'descricao' => 'Apoiador']);

        // 4. Território Semente
        $this->municipio = Municipio::create([
            'nome' => 'Limeira',
            'estado' => 'SP',
            'codigo_ibge' => '3526902',
            'municipio_principal' => true,
            'ativo' => true
        ]);

        $this->regiao = Regiao::create([
            'municipio_id' => $this->municipio->id,
            'nome' => 'Norte',
            'descricao' => 'Região Norte da Cidade',
            'responsavel_id' => $this->coordenadorUser->id,
            'ativo' => true
        ]);
    }

    /** @test */
    public function criacao_de_municipio_regiao_e_bairro_com_sucesso()
    {
        $response = $this->actingAs($this->adminUser)->post('/territorio/bairros', [
            'nome' => 'Jardim Aeroporto',
            'nome_alternativo' => 'Aeroporto',
            'municipio_id' => $this->municipio->id,
            'regiao_id' => $this->regiao->id,
            'prioridade' => 'alta',
            'responsavel_id' => $this->coordenadorUser->id,
            'populacao_estimada_manual' => 12000,
            'meta_contatos' => 600,
            'observacoes' => 'Próximo à rodovia.'
        ]);

        $response->assertRedirect('/territorio');
        $this->assertDatabaseHas('bairros', [
            'nome' => 'Jardim Aeroporto',
            'municipio_id' => $this->municipio->id,
            'regiao_id' => $this->regiao->id,
            'prioridade' => 'alta',
        ]);
    }

    /** @test */
    public function nomes_de_bairros_duplicados_no_mesmo_municipio_sao_bloqueados()
    {
        // Cria o primeiro bairro
        Bairro::create([
            'nome' => 'Centro',
            'municipio_id' => $this->municipio->id,
            'regiao_id' => $this->regiao->id,
            'prioridade' => 'normal',
            'status_cobertura' => 'nao_iniciado',
            'ativo' => true
        ]);

        // Tenta criar duplicado
        $response = $this->actingAs($this->adminUser)->from('/territorio')->post('/territorio/bairros', [
            'nome' => 'Centro', // Nome duplicado!
            'municipio_id' => $this->municipio->id,
            'regiao_id' => $this->regiao->id,
            'prioridade' => 'normal'
        ]);

        $response->assertRedirect('/territorio');
        $response->assertSessionHasErrors('nome');
    }

    /** @test */
    public function bairros_com_mesmo_nome_em_municipios_diferentes_sao_permitidos()
    {
        $outroMunicipio = Municipio::create([
            'nome' => 'Cordeirópolis',
            'estado' => 'SP',
            'ativo' => true
        ]);

        $outraRegiao = Regiao::create([
            'municipio_id' => $outroMunicipio->id,
            'nome' => 'Geral',
            'ativo' => true
        ]);

        // Bairro 1 em Limeira
        Bairro::create([
            'nome' => 'Centro',
            'municipio_id' => $this->municipio->id,
            'regiao_id' => $this->regiao->id,
            'prioridade' => 'normal',
            'status_cobertura' => 'nao_iniciado',
            'ativo' => true
        ]);

        // Tenta cadastrar Bairro 2 (Centro) em Cordeirópolis
        $response = $this->actingAs($this->adminUser)->post('/territorio/bairros', [
            'nome' => 'Centro',
            'municipio_id' => $outroMunicipio->id,
            'regiao_id' => $outraRegiao->id,
            'prioridade' => 'normal'
        ]);

        $response->assertRedirect('/territorio');
        $this->assertDatabaseHas('bairros', [
            'nome' => 'Centro',
            'municipio_id' => $outroMunicipio->id
        ]);
    }

    /** @test */
    public function cadastro_de_local_estrategico_vinculado_ao_bairro()
    {
        $bairro = Bairro::create([
            'nome' => 'Vila Nova',
            'municipio_id' => $this->municipio->id,
            'regiao_id' => $this->regiao->id,
            'prioridade' => 'normal',
            'status_cobertura' => 'nao_iniciado',
            'ativo' => true
        ]);

        $response = $this->actingAs($this->adminUser)->from("/territorio/bairros/{$bairro->id}")->post("/territorio/bairros/{$bairro->id}/local", [
            'nome' => 'Igreja Presbiteriana',
            'tipo' => 'religioso',
            'endereco' => 'Rua do Culto, 456',
            'contato_responsavel' => 'Presbítero João',
            'telefone' => '19988883333',
            'observacoes' => 'Espaço aberto para conversas de fim de tarde.',
            'nivel_prioridade' => 'alta',
        ]);

        $response->assertRedirect("/territorio/bairros/{$bairro->id}");
        $this->assertDatabaseHas('locais_estrategicos', [
            'nome' => 'Igreja Presbiteriana',
            'bairro_id' => $bairro->id,
            'tipo' => 'religioso',
        ]);

        // Verifica se atualizou a última ação do bairro
        $bairro->refresh();
        $this->assertNotNull($bairro->data_ultima_acao);
    }

    /** @test */
    public function ficha_do_bairro_agrega_relacionamentos_e_eventos()
    {
        $bairro = Bairro::create([
            'nome' => 'Príncipe',
            'municipio_id' => $this->municipio->id,
            'regiao_id' => $this->regiao->id,
            'prioridade' => 'normal',
            'status_cobertura' => 'nao_iniciado',
            'ativo' => true
        ]);

        // Cria contato no bairro
        $contato = Relacionamento::create([
            'nome' => 'Carlos Liderança',
            'tipo_pessoa' => 'PF',
            'bairro_id' => $bairro->id,
        ]);
        $contato->tipos()->sync([TipoRelacionamento::where('nome', 'lideranca')->first()->id]);

        // Cria evento no bairro
        $evento = Evento::create([
            'titulo' => 'Reunião de Lideranças do Príncipe',
            'tipo' => 'reuniao',
            'data_hora_inicio' => '2026-08-06 19:00:00',
            'data_hora_fim' => '2026-08-06 20:30:00',
            'bairro_id' => $bairro->id,
            'status' => 'agendado',
        ]);

        $response = $this->actingAs($this->adminUser)->get("/territorio/bairros/{$bairro->id}");
        
        $response->assertStatus(200);
        $response->assertSee('Carlos Liderança');
        $response->assertSee('Reunião de Lideranças do Príncipe');
    }

    /** @test */
    public function criacao_de_demanda_com_sucesso()
    {
        $bairro = Bairro::create([
            'nome' => 'Cidade Jardim',
            'municipio_id' => $this->municipio->id,
            'regiao_id' => $this->regiao->id,
            'prioridade' => 'normal',
            'status_cobertura' => 'nao_iniciado',
            'ativo' => true
        ]);

        $response = $this->actingAs($this->comumUser)->post('/demandas', [
            'titulo' => 'Falta asfalto na rua 3',
            'descricao' => 'Moradores reclamam de buracos que danificam carros.',
            'tipo' => 'problema_bairro',
            'bairro_relacionado_id' => $bairro->id,
            'data_registro' => '2026-08-06',
            'prioridade' => 'alta',
            'status' => 'novo',
        ]);

        $response->assertRedirect('/demandas');
        $this->assertDatabaseHas('demandas_compromissos', [
            'titulo' => 'Falta asfalto na rua 3',
            'tipo' => 'problema_bairro',
            'bairro_relacionado_id' => $bairro->id,
        ]);
    }

    /** @test */
    public function usuario_comum_nao_consegue_aprovar_compromisso_e_coordenador_consegue()
    {
        $bairro = Bairro::create([
            'nome' => 'Cecap',
            'municipio_id' => $this->municipio->id,
            'regiao_id' => $this->regiao->id,
            'prioridade' => 'normal',
            'status_cobertura' => 'nao_iniciado',
            'ativo' => true
        ]);

        $demanda = DemandaCompromisso::create([
            'titulo' => 'Reforma da quadra de esportes',
            'descricao' => 'Pedem nova pintura e redes.',
            'tipo' => 'demanda',
            'bairro_relacionado_id' => $bairro->id,
            'data_registro' => '2026-08-06',
            'prioridade' => 'normal',
            'status' => 'novo',
        ]);

        // 1. Usuário comum tenta aprovar -> Bloqueado
        $response1 = $this->actingAs($this->comumUser)->post("/demandas/{$demanda->id}/aprovar", [
            'texto_aprovado' => 'Campanha promete reformar se eleito.',
        ]);
        $response1->assertStatus(403);

        // 2. Coordenador aprova -> Sucesso
        $response2 = $this->actingAs($this->coordenadorUser)->post("/demandas/{$demanda->id}/aprovar", [
            'texto_aprovado' => 'Compromisso de lutar por emenda parlamentar para reforma da quadra.',
            'prazo' => '2026-10-01',
        ]);
        
        $response2->assertRedirect('/demandas');
        $this->assertDatabaseHas('demandas_compromissos', [
            'id' => $demanda->id,
            'tipo' => 'compromisso_campanha',
            'status' => 'aprovado',
            'texto_aprovado' => 'Compromisso de lutar por emenda parlamentar para reforma da quadra.',
        ]);

        // Verifica auditoria e timeline
        $this->assertDatabaseHas('logs_auditoria', [
            'acao' => 'aprovacao_compromisso_campanha',
        ]);
    }

    /** @test */
    public function observacao_interna_e_bloqueada_para_perfil_sem_permissao()
    {
        $demanda = DemandaCompromisso::create([
            'titulo' => 'Doação de cadeira de rodas',
            'descricao' => 'Contatou a sede pedindo ajuda.',
            'tipo' => 'demanda',
            'data_registro' => '2026-08-06',
            'prioridade' => 'normal',
            'status' => 'novo',
            'observacoes_internas' => 'Esta é uma observação estritamente confidencial.'
        ]);

        // Usuário comum não vê
        $response1 = $this->actingAs($this->comumUser)->get('/demandas');
        $response1->assertStatus(200);
        $response1->assertDontSee('Esta é uma observação estritamente confidencial.');

        // Coordenador/Admin vê
        $response2 = $this->actingAs($this->coordenadorUser)->get('/demandas');
        $response2->assertStatus(200);
        $response2->assertSee('Esta é uma observação estritamente confidencial.');
    }

    /** @test */
    public function alteracao_de_status_de_cobertura_gera_log()
    {
        $bairro = Bairro::create([
            'nome' => 'Vista Alegre',
            'municipio_id' => $this->municipio->id,
            'regiao_id' => $this->regiao->id,
            'prioridade' => 'normal',
            'status_cobertura' => 'nao_iniciado',
            'ativo' => true
        ]);

        $response = $this->actingAs($this->coordenadorUser)->post("/territorio/bairros/{$bairro->id}/status", [
            'status_cobertura' => 'consolidado',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('bairros', [
            'id' => $bairro->id,
            'status_cobertura' => 'consolidado',
        ]);

        $this->assertDatabaseHas('logs_auditoria', [
            'acao' => 'alteracao_status_cobertura',
        ]);
    }

    /** @test */
    public function metas_exibem_percentual_correto()
    {
        $bairro = Bairro::create([
            'nome' => 'Belinha Ometto',
            'municipio_id' => $this->municipio->id,
            'regiao_id' => $this->regiao->id,
            'prioridade' => 'normal',
            'meta_contatos' => 10,
            'status_cobertura' => 'nao_iniciado',
            'ativo' => true
        ]);

        // Adiciona 5 contatos
        for ($i = 0; $i < 5; $i++) {
            Relacionamento::create([
                'nome' => "Apoiador {$i}",
                'tipo_pessoa' => 'PF',
                'bairro_id' => $bairro->id
            ]);
        }

        $response = $this->actingAs($this->adminUser)->get("/territorio/bairros/{$bairro->id}");
        $response->assertStatus(200);
        $response->assertSee('50%'); // 5 de 10 contatos = 50%
    }

    /** @test */
    public function acesso_direto_por_url_sem_permissao_eh_bloqueado()
    {
        $semPermissoesUser = User::factory()->create(['status' => 'ativo']);
        // Sem roles/permissoes associadas

        $response = $this->actingAs($semPermissoesUser)->get('/territorio');
        $response->assertStatus(403);
    }

    /** @test */
    public function criacao_de_municipio_com_sucesso_via_post()
    {
        $response = $this->actingAs($this->adminUser)->post('/territorio/municipios', [
            'nome' => 'Piracicaba',
            'estado' => 'SP',
            'codigo_ibge' => '3538709',
        ]);

        $response->assertRedirect('/territorio');
        $this->assertDatabaseHas('municipios', [
            'nome' => 'Piracicaba',
            'estado' => 'SP',
        ]);

        // Também deve ter criado a Região Geral automaticamente
        $this->assertDatabaseHas('regioes', [
            'nome' => 'Região Geral Piracicaba',
        ]);
    }

    /** @test */
    public function exclusao_de_bairro_sem_vinculos_com_sucesso()
    {
        $bairro = Bairro::create([
            'nome' => 'Bairro Para Excluir',
            'municipio_id' => $this->municipio->id,
            'regiao_id' => $this->regiao->id,
            'prioridade' => 'normal',
            'status_cobertura' => 'nao_iniciado',
            'ativo' => true
        ]);

        $response = $this->actingAs($this->adminUser)->post("/territorio/bairros/{$bairro->id}/deletar");
        $response->assertRedirect('/territorio');
        $this->assertDatabaseMissing('bairros', ['id' => $bairro->id]);
    }

    /** @test */
    public function exclusao_de_bairro_com_vinculos_retorna_erro()
    {
        $bairro = Bairro::create([
            'nome' => 'Bairro Com Vinculos',
            'municipio_id' => $this->municipio->id,
            'regiao_id' => $this->regiao->id,
            'prioridade' => 'normal',
            'status_cobertura' => 'nao_iniciado',
            'ativo' => true
        ]);

        DemandaCompromisso::create([
            'titulo' => 'Demanda Bairro',
            'descricao' => 'Teste',
            'bairro_relacionado_id' => $bairro->id,
            'tipo' => 'demanda_sugestao',
            'status' => 'novo',
            'prioridade' => 'normal',
            'data_registro' => now()
        ]);

        $response = $this->actingAs($this->adminUser)->post("/territorio/bairros/{$bairro->id}/deletar");
        $response->assertSessionHasErrors('exclusao');
        $this->assertDatabaseHas('bairros', ['id' => $bairro->id]);
    }

    /** @test */
    public function exclusao_de_demanda_com_sucesso()
    {
        $demanda = DemandaCompromisso::create([
            'titulo' => 'Demanda Para Excluir',
            'descricao' => 'Teste',
            'tipo' => 'demanda_sugestao',
            'status' => 'novo',
            'prioridade' => 'normal',
            'data_registro' => now()
        ]);

        $response = $this->actingAs($this->adminUser)->post("/demandas/{$demanda->id}/deletar");
        $response->assertRedirect('/demandas');
        $this->assertDatabaseMissing('demandas_compromissos', ['id' => $demanda->id]);
    }
}
