<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\LogAuditoria;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAndRBACTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Criar roles básicas de teste para o Spatie
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'coordenador']);
        Role::create(['name' => 'consulta']);
    }

    /** @test */
    public function usuario_consegue_logar_com_credenciais_validas()
    {
        $user = User::factory()->create([
            'email' => 'teste@campaignos.com',
            'password' => bcrypt('SenhaValida123'),
            'status' => 'ativo',
        ]);
        $user->assignRole('coordenador');

        $response = $this->post('/login', [
            'email' => 'teste@campaignos.com',
            'password' => 'SenhaValida123',
        ]);

        $response->assertRedirect('/warroom');
        $this->assertAuthenticatedAs($user);

        // Verifica log de auditoria
        $this->assertDatabaseHas('logs_auditoria', [
            'user_id' => $user->id,
            'acao' => 'login',
        ]);
    }

    /** @test */
    public function usuario_inativo_nao_consegue_logar()
    {
        $user = User::factory()->create([
            'email' => 'inativo@campaignos.com',
            'password' => bcrypt('SenhaValida123'),
            'status' => 'inativo',
        ]);
        $user->assignRole('consulta');

        $response = $this->post('/login', [
            'email' => 'inativo@campaignos.com',
            'password' => 'SenhaValida123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();

        // Verifica log de auditoria de falha por inatividade
        $this->assertDatabaseHas('logs_auditoria', [
            'user_id' => $user->id,
            'acao' => 'login_falha',
            'tabela' => 'users',
        ]);
    }

    /** @test */
    public function login_com_senha_errada_falha()
    {
        $user = User::factory()->create([
            'email' => 'teste@campaignos.com',
            'password' => bcrypt('SenhaValida123'),
            'status' => 'ativo',
        ]);
        $user->assignRole('consulta');

        $response = $this->post('/login', [
            'email' => 'teste@campaignos.com',
            'password' => 'SenhaInvalida',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();

        // Verifica log de auditoria de falha por credenciais
        $this->assertDatabaseHas('logs_auditoria', [
            'user_id' => $user->id,
            'acao' => 'login_falha',
        ]);
    }

    /** @test */
    public function usuario_nao_autenticado_eh_redirecionado()
    {
        $response = $this->get('/warroom');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function usuario_sem_perfil_admin_nao_acessa_rota_admin()
    {
        $user = User::factory()->create([
            'status' => 'ativo',
        ]);
        $user->assignRole('consulta'); // Não é admin

        $response = $this->actingAs($user)->get('/admin/usuarios');

        $response->assertStatus(403);

        // Verifica registro de acesso não autorizado no log
        $this->assertDatabaseHas('logs_auditoria', [
            'user_id' => $user->id,
            'acao' => 'acesso_nao_autorizado',
        ]);
    }

    /** @test */
    public function usuario_admin_acessa_rota_admin()
    {
        $admin = User::factory()->create([
            'status' => 'ativo',
        ]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get('/admin/usuarios');

        $response->assertStatus(200);
        $response->assertSee('Área administrativa');
    }
}
