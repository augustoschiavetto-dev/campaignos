<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Bairro;
use App\Models\Municipio;
use App\Models\Regiao;
use App\Models\Relacionamento;
use App\Models\Evento;
use App\Models\Favorito;
use App\Models\HistoricoRecente;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class TransversalTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $operacionalUser;
    protected Bairro $bairro;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = Role::create(['name' => 'admin']);
        $roleOperacional = Role::create(['name' => 'operacional']);

        $permissions = [
            'relacionamentos.visualizar', 'relacionamentos.criar',
            'territorio.visualizar', 'territorio.criar', 'territorio.inativar',
            'eventos.visualizar', 'eventos.criar',
        ];

        foreach ($permissions as $p) {
            Permission::create(['name' => $p]);
        }

        $roleAdmin->syncPermissions(Permission::all());
        $roleOperacional->syncPermissions([
            'relacionamentos.visualizar',
            'territorio.visualizar',
        ]);

        $this->admin = User::factory()->create();
        $this->admin->assignRole($roleAdmin);

        $this->operacionalUser = User::factory()->create();
        $this->operacionalUser->assignRole($roleOperacional);

        // Divisões territoriais
        $muni = Municipio::create(['nome' => 'Limeira', 'estado' => 'SP', 'municipio_principal' => true]);
        $reg = Regiao::create(['municipio_id' => $muni->id, 'nome' => 'Centro']);
        $this->bairro = Bairro::create([
            'municipio_id' => $muni->id,
            'regiao_id' => $reg->id,
            'nome' => 'Centro Histórico',
            'prioridade' => 'alta',
            'status_cobertura' => 'ativo',
        ]);
    }

    /** @test */
    public function pesquisa_global_retorna_resultados_agrupados_por_permissao()
    {
        // Cria contato
        Relacionamento::create([
            'nome' => 'Guto Candidato Teste',
            'tipo_pessoa' => 'PF',
            'status' => 'ativo',
        ]);

        // Cria evento
        Evento::create([
            'titulo' => 'Caminhada com Guto',
            'tipo' => 'caminhada',
            'data_hora_inicio' => '2026-08-06 14:00:00',
            'data_hora_fim' => '2026-08-06 15:00:00',
            'prioridade' => 'importante',
            'status' => 'confirmado',
        ]);

        // Pesquisa pelo admin (permissão a tudo)
        $response = $this->actingAs($this->admin)->get('/pesquisa?q=Guto');
        $response->assertStatus(200);
        $response->assertSee('Guto Candidato Teste');
        $response->assertSee('Caminhada com Guto');

        // Pesquisa pelo operacional (permissão apenas para relacionamentos e bairros, sem agenda)
        $responseOp = $this->actingAs($this->operacionalUser)->get('/pesquisa?q=Guto');
        $responseOp->assertStatus(200);
        $responseOp->assertSee('Guto Candidato Teste');
        // Não deve ver o evento de agenda
        $responseOp->assertDontSee('Caminhada com Guto');
    }

    /** @test */
    public function favoritar_e_desfavoritar_registro_com_sucesso()
    {
        $response = $this->actingAs($this->admin)->post('/favoritos/toggle', [
            'favoritavel_type' => Bairro::class,
            'favoritavel_id' => $this->bairro->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('favoritos', [
            'user_id' => $this->admin->id,
            'favoritavel_type' => Bairro::class,
            'favoritavel_id' => $this->bairro->id,
        ]);

        // Remove ao enviar novamente
        $responseRemove = $this->actingAs($this->admin)->post('/favoritos/toggle', [
            'favoritavel_type' => Bairro::class,
            'favoritavel_id' => $this->bairro->id,
        ]);

        $this->assertDatabaseMissing('favoritos', [
            'user_id' => $this->admin->id,
            'favoritavel_type' => Bairro::class,
            'favoritavel_id' => $this->bairro->id,
        ]);
    }

    /** @test */
    public function historico_recente_registra_acesso_e_exibe_no_war_room()
    {
        // Acessa a ficha do bairro para disparar o log automático
        $response = $this->actingAs($this->admin)->get("/territorio/bairros/{$this->bairro->id}");
        $response->assertStatus(200);

        $this->assertDatabaseHas('historico_recente', [
            'user_id' => $this->admin->id,
            'acessavel_type' => Bairro::class,
            'acessavel_id' => $this->bairro->id,
            'titulo' => "Bairro: " . $this->bairro->nome,
        ]);

        // Acessa War Room e verifica se vê o histórico
        $responseWar = $this->actingAs($this->admin)->get('/warroom');
        $responseWar->assertSee('Bairro: Centro Histórico');
    }
}
