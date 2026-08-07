<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ConfiguracaoCampanha;
use App\Models\LogAuditoria;
use App\Helpers\CampaignStorage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArchitecturalAccessTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $coordenadorUser;
    protected $consultaUser;
    protected $inativoUser;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Criar Permissões e Roles
        Permission::create(['name' => 'usuarios.visualizar']);
        Permission::create(['name' => 'usuarios.criar']);
        Permission::create(['name' => 'usuarios.editar']);
        Permission::create(['name' => 'usuarios.inativar']);
        Permission::create(['name' => 'usuarios.gerenciar_permissoes']);
        
        Permission::create(['name' => 'configuracoes.visualizar']);
        Permission::create(['name' => 'configuracoes.editar']);

        Permission::create(['name' => 'mural.visualizar']);
        Permission::create(['name' => 'mural.publicar']);

        $roleAdmin = Role::create(['name' => 'admin']);
        $roleCoordenador = Role::create(['name' => 'coordenador']);
        $roleConsulta = Role::create(['name' => 'consulta']);

        $roleAdmin->syncPermissions(Permission::all());
        $roleCoordenador->syncPermissions([
            'usuarios.visualizar', 'usuarios.criar', 'usuarios.editar', 'usuarios.inativar',
            'configuracoes.visualizar', 'mural.visualizar'
        ]);
        $roleConsulta->syncPermissions(['mural.visualizar']);

        // 2. Criar Usuários
        $this->adminUser = User::factory()->create(['status' => 'ativo']);
        $this->adminUser->assignRole($roleAdmin);

        $this->coordenadorUser = User::factory()->create(['status' => 'ativo']);
        $this->coordenadorUser->assignRole($roleCoordenador);

        $this->consultaUser = User::factory()->create(['status' => 'ativo']);
        $this->consultaUser->assignRole($roleConsulta);

        $this->inativoUser = User::factory()->create(['status' => 'inativo']);
        $this->inativoUser->assignRole($roleConsulta);

        // 3. Inicializar Configurações
        ConfiguracaoCampanha::obter();
    }

    /** @test */
    public function administrador_cria_um_usuario()
    {
        $response = $this->actingAs($this->adminUser)->post('/usuarios', [
            'name' => 'Novo Assessor',
            'email' => 'assessor@teste.com',
            'password' => 'SenhaSegura123',
            'telefone' => '19999999999',
            'role' => 'consulta',
        ]);

        $response->assertRedirect('/usuarios');
        $this->assertDatabaseHas('users', [
            'email' => 'assessor@teste.com',
            'name' => 'Novo Assessor',
        ]);

        $usuarioCriado = User::where('email', 'assessor@teste.com')->first();
        $this->assertTrue($usuarioCriado->hasRole('consulta'));
    }

    /** @test */
    public function administrador_atribui_papel()
    {
        $usuario = User::factory()->create(['status' => 'ativo']);
        $usuario->assignRole('consulta');

        $response = $this->actingAs($this->adminUser)->post("/usuarios/{$usuario->id}", [
            'name' => $usuario->name,
            'email' => $usuario->email,
            'telefone' => $usuario->telefone,
            'role' => 'coordenador',
        ]);

        $response->assertRedirect('/usuarios');
        $this->assertTrue($usuario->fresh()->hasRole('coordenador'));
        
        // Verifica log de alteração de papel
        $this->assertDatabaseHas('logs_auditoria', [
            'user_id' => $this->adminUser->id,
            'acao' => 'alteracao_papel',
            'tabela' => 'users',
            'registro_id' => $usuario->id,
        ]);
    }

    /** @test */
    public function administrador_atribui_permissao_especifica()
    {
        $usuario = User::factory()->create(['status' => 'ativo']);
        $usuario->assignRole('consulta'); // Consulta por padrão não tem permissão para editar configs

        $response = $this->actingAs($this->adminUser)->post("/usuarios/{$usuario->id}/permissoes", [
            'permissoes' => ['configuracoes.editar']
        ]);

        $response->assertRedirect('/usuarios');
        $this->assertTrue($usuario->fresh()->hasDirectPermission('configuracoes.editar'));
        
        // Verifica log de alteração de permissão específica
        $this->assertDatabaseHas('logs_auditoria', [
            'user_id' => $this->adminUser->id,
            'acao' => 'alteracao_permissoes_especificas',
        ]);
    }

    /** @test */
    public function usuario_inativo_nao_consegue_entrar()
    {
        $response = $this->post('/login', [
            'email' => $this->inativoUser->email,
            'password' => 'password', // Senha padrão da factory do Laravel
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** @test */
    public function usuario_sem_permissao_recebe_bloqueio_ao_acessar_url_diretamente()
    {
        // Consulta tenta acessar a rota de configurações administrativas
        $response = $this->actingAs($this->consultaUser)->get('/configuracoes');

        $response->assertStatus(403);
    }

    /** @test */
    public function menu_nao_mostra_modulo_sem_permissao()
    {
        $response = $this->actingAs($this->consultaUser)->get('/warroom');

        // Usuário Consulta não tem permissão configuracoes.visualizar,
        // então não deve ter o link correspondente no HTML renderizado.
        $response->assertDontSee('/configuracoes');
        $response->assertDontSee('/usuarios');
    }

    /** @test */
    public function administrador_nao_consegue_inativar_a_propria_conta()
    {
        $response = $this->actingAs($this->adminUser)->post("/usuarios/{$this->adminUser->id}/status");

        $response->assertStatus(422);
        $this->assertEquals('ativo', $this->adminUser->fresh()->status);
    }

    /** @test */
    public function ultimo_administrador_ativo_nao_pode_ser_inativado()
    {
        // Tentativa de inativar o adminUser sendo ele o único administrador ativo
        $response = $this->actingAs($this->adminUser)->post("/usuarios/{$this->adminUser->id}/status");
        $response->assertStatus(422);

        // Se tentarmos inativá-lo logado como Coordenador, também deve bloquear
        $response2 = $this->actingAs($this->coordenadorUser)->post("/usuarios/{$this->adminUser->id}/status");
        $response2->assertStatus(422);
        $this->assertEquals('ativo', $this->adminUser->fresh()->status);
    }

    /** @test */
    public function alteracao_de_configuracao_gera_log()
    {
        $response = $this->actingAs($this->adminUser)->post('/configuracoes', [
            'nome_campanha' => 'Novo Nome Campanha',
            'candidato_nome' => 'Novo Guto',
            'candidato_nome_politico' => 'Guto',
            'candidato_cargo' => 'Deputado Federal',
            'candidato_numero' => '9999',
            'partido_sigla' => 'SIGLA',
            'partido_coligacao' => 'Limeira Forte',
            'campanha_cnpj' => '00.000.000/0001-00',
            'campanha_cidade' => 'Limeira',
            'campanha_uf' => 'SP',
            'data_primeiro_turno' => '2026-10-04',
            'campanha_timezone' => 'America/Sao_Paulo',
            'campanha_telefone' => '19999999999',
            'campanha_email' => 'contato@guto.com.br',
            'campanha_site' => 'www.guto.com.br',
            'campanha_instagram' => 'https://instagram.com/guto',
            'campanha_facebook' => 'https://facebook.com/guto',
            'campanha_youtube' => 'https://youtube.com/guto',
            'identidade_cor_primaria' => '#000000',
            'identidade_cor_secundaria' => '#ffffff',
            'texto_institucional_curto' => 'Novo texto curto',
            'preferencia_tema' => 'claro',
        ]);

        $response->assertRedirect('/configuracoes');
        
        $this->assertDatabaseHas('configuracoes_campanha', [
            'nome_campanha' => 'Novo Nome Campanha',
            'candidato_nome' => 'Novo Guto',
        ]);

        // Verifica log de alteração de configurações
        $this->assertDatabaseHas('logs_auditoria', [
            'user_id' => $this->adminUser->id,
            'acao' => 'alteracao_configuracoes',
            'tabela' => 'configuracoes_campanha',
        ]);
    }

    /** @test */
    public function data_da_eleicao_configurada_alimenta_a_contagem_regressiva()
    {
        $config = ConfiguracaoCampanha::obter();
        
        // Configura a data da eleição para 10 dias a partir de hoje
        $dataFutura = now()->addDays(10);
        $config->update([
            'data_primeiro_turno' => $dataFutura->format('Y-m-d'),
            'campanha_timezone' => 'America/Sao_Paulo'
        ]);

        $response = $this->actingAs($this->adminUser)->get('/warroom');
        
        // Verifica se o número 10 (dias restantes) está contido no HTML
        $response->assertSee('10');
    }

    /** @test */
    public function tema_claro_e_escuro_mantem_a_preferencia()
    {
        $config = ConfiguracaoCampanha::obter();
        
        // Altera preferência nas configurações
        $config->update(['preferencia_tema' => 'claro']);
        
        $this->assertEquals('claro', ConfiguracaoCampanha::obter()->preferencia_tema);
    }

    /** @test */
    public function upload_de_logotipo_utiliza_o_campaign_storage()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('logo.png');

        $path = CampaignStorage::salvar($file, 'logos');

        Storage::disk('public')->assertExists($path);
        $this->assertStringContainsString('logos/', $path);
    }

    /** @test */
    public function usuario_sem_permissao_nao_consegue_baixar_anexo_protegido()
    {
        // Criar usuário sem nenhuma permissão do mural
        $usuarioSemPermissão = User::factory()->create(['status' => 'ativo']);
        // Sem roles e sem permissões

        $response = $this->actingAs($usuarioSemPermissão)->get('/mural/anexo/download?path=documentos/anexo.pdf');

        $response->assertStatus(403);
    }
}
