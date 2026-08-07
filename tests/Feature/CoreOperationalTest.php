<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Bairro;
use App\Models\Evento;
use App\Models\Tarefa;
use App\Models\MuralAviso;
use App\Models\Municipio;
use App\Models\Regiao;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoreOperationalTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $bairro;

    protected function setUp(): void
    {
        parent::setUp();

        // Configuração das roles e permissões do Spatie Permission para o teste
        $roleAdmin = Role::create(['name' => 'admin']);
        $roleCoordenador = Role::create(['name' => 'coordenador']);
        $roleConsulta = Role::create(['name' => 'consulta']);

        \Spatie\Permission\Models\Permission::create(['name' => 'mural.publicar']);
        \Spatie\Permission\Models\Permission::create(['name' => 'mural.visualizar']);
        \Spatie\Permission\Models\Permission::create(['name' => 'tarefas.criar']);
        \Spatie\Permission\Models\Permission::create(['name' => 'tarefas.visualizar']);
        \Spatie\Permission\Models\Permission::create(['name' => 'eventos.criar']);
        \Spatie\Permission\Models\Permission::create(['name' => 'eventos.visualizar']);
        \Spatie\Permission\Models\Permission::create(['name' => 'eventos.cancelar']);

        $roleCoordenador->syncPermissions([
            'mural.publicar', 'mural.visualizar',
            'tarefas.criar', 'tarefas.visualizar',
            'eventos.criar', 'eventos.visualizar',
            'eventos.cancelar'
        ]);

        $this->user = User::factory()->create([
            'status' => 'ativo'
        ]);
        $this->user->assignRole($roleCoordenador);

        // Criação de Municipio e Regiao obrigatórios
        $municipio = Municipio::create([
            'nome' => 'Limeira',
            'estado' => 'SP',
            'ativo' => true
        ]);

        $regiao = Regiao::create([
            'municipio_id' => $municipio->id,
            'nome' => 'Centro',
            'ativo' => true
        ]);

        $this->bairro = Bairro::create([
            'nome' => 'Centro',
            'municipio_id' => $municipio->id,
            'regiao_id' => $regiao->id,
            'prioridade' => 'alta',
            'populacao_estimada_manual' => 10000,
            'meta_contatos' => 500,
            'status_cobertura' => 'ativo',
        ]);
    }

    /** @test */
    public function coordenador_consegue_criar_tarefa_com_checklist()
    {
        $response = $this->actingAs($this->user)->post('/tarefas', [
            'titulo' => 'Distribuir panfletos no Centro',
            'descricao' => 'Levar material gráfico para o calçadão.',
            'responsavel_id' => $this->user->id,
            'data_inicio' => '2026-08-06',
            'prazo' => '2026-08-10',
            'prioridade' => 'alta',
            'status' => 'pendente',
            'checklist_itens' => ['Separar santinhos', 'Pegar camisetas', 'Verificar som']
        ]);

        $response->assertRedirect('/tarefas');
        $this->assertDatabaseHas('tarefas', [
            'titulo' => 'Distribuir panfletos no Centro',
            'prioridade' => 'alta',
            'responsavel_id' => $this->user->id,
        ]);

        // Verifica log de auditoria
        $this->assertDatabaseHas('logs_auditoria', [
            'user_id' => $this->user->id,
            'acao' => 'criacao',
            'tabela' => 'tarefas',
        ]);

        // Verifica timeline
        $this->assertDatabaseHas('timeline', [
            'tipo_evento' => 'tarefa.criada',
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function coordenador_consegue_criar_evento_com_checklist()
    {
        $response = $this->actingAs($this->user)->post('/eventos', [
            'titulo' => 'Caminhada no Calçadão',
            'tipo' => 'caminhada',
            'descricao' => 'Caminhada com apoiadores.',
            'data_hora_inicio' => '2026-08-06 14:00:00',
            'data_hora_end' => '2026-08-06 16:30:00', // Campo no request pode ser data_hora_fim
            'data_hora_fim' => '2026-08-06 16:30:00',
            'bairro_id' => $this->bairro->id,
            'responsavel_id' => $this->user->id,
            'status' => 'confirmado',
            'prioridade' => 'importante',
            'checklist_itens' => ['Carro de som', 'Água mineral', 'Bandeiras']
        ]);

        $response->assertRedirect('/eventos');
        $this->assertDatabaseHas('eventos', [
            'titulo' => 'Caminhada no Calçadão',
            'responsavel_id' => $this->user->id,
            'bairro_id' => $this->bairro->id,
        ]);
    }

    /** @test */
    public function sistema_bloqueia_sobreposicao_de_horario_em_evento_confirmado()
    {
        // Evento existente
        Evento::create([
            'titulo' => 'Comício Principal',
            'tipo' => 'comicio',
            'descricao' => 'Grande ato de campanha.',
            'data_hora_inicio' => '2026-08-06 18:00:00',
            'data_hora_fim' => '2026-08-06 20:00:00',
            'bairro_id' => $this->bairro->id,
            'responsavel_id' => $this->user->id,
            'status' => 'confirmado',
            'prioridade' => 'importante',
        ]);

        // Novo evento no mesmo horário
        $response = $this->actingAs($this->user)->from('/eventos')->post('/eventos', [
            'titulo' => 'Palestra em Auditório',
            'tipo' => 'palestra',
            'descricao' => 'Apresentação de propostas.',
            'data_hora_inicio' => '2026-08-06 18:30:00', // Conflito!
            'data_hora_fim' => '2026-08-06 19:30:00',
            'bairro_id' => $this->bairro->id,
            'responsavel_id' => $this->user->id,
            'status' => 'confirmado',
            'prioridade' => 'importante',
        ]);

        $response->assertRedirect('/eventos');
        $response->assertSessionHasErrors('data_hora_inicio');
    }

    /** @test */
    public function coordenador_consegue_publicar_aviso_no_mural()
    {
        $response = $this->actingAs($this->user)->post('/mural', [
            'titulo' => 'Nova meta de Limeira',
            'mensagem' => 'Precisamos atingir 5 mil contatos nesta semana.',
            'prioridade' => 'critico',
            'data_inicio' => '2026-08-06',
        ]);

        $response->assertRedirect('/mural');
        $this->assertDatabaseHas('mural_avisos', [
            'titulo' => 'Nova meta de Limeira',
            'prioridade' => 'critico',
        ]);
    }

    /** @test */
    public function coordenador_consegue_excluir_evento_com_sucesso()
    {
        $evento = Evento::create([
            'titulo' => 'Evento Para Excluir',
            'tipo' => 'reuniao',
            'descricao' => 'Teste de exclusão.',
            'data_hora_inicio' => '2026-08-07 10:00:00',
            'data_hora_fim' => '2026-08-07 11:00:00',
            'bairro_id' => $this->bairro->id,
            'responsavel_id' => $this->user->id,
            'status' => 'confirmado',
            'prioridade' => 'normal',
        ]);

        $response = $this->actingAs($this->user)->post("/eventos/{$evento->id}/deletar");
        $response->assertRedirect('/eventos');
        $this->assertDatabaseMissing('eventos', ['id' => $evento->id]);
    }
}
