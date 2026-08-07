<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Bairro;
use App\Models\Relacionamento;
use App\Models\LiderancaDetalhe;
use App\Models\Interacao;
use App\Models\TipoRelacionamento;
use App\Models\Municipio;
use App\Models\Regiao;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class RelacionamentosTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $bairro;

    protected function setUp(): void
    {
        parent::setUp();

        // Configuração de Roles e Permissões
        Role::create(['name' => 'admin']);
        $roleCoordenador = Role::create(['name' => 'coordenador']);
        
        Permission::create(['name' => 'relacionamentos.visualizar']);
        Permission::create(['name' => 'relacionamentos.criar']);
        Permission::create(['name' => 'relacionamentos.editar']);
        Permission::create(['name' => 'relacionamentos.excluir']);

        $roleCoordenador->syncPermissions(Permission::all());

        // Seeding de Tipos de Relacionamento
        TipoRelacionamento::create(['nome' => 'apoiador', 'descricao' => 'Apoiador']);
        TipoRelacionamento::create(['nome' => 'voluntario', 'descricao' => 'Voluntário']);
        TipoRelacionamento::create(['nome' => 'lideranca', 'descricao' => 'Liderança']);
        TipoRelacionamento::create(['nome' => 'equipe', 'descricao' => 'Equipe']);

        $this->user = User::factory()->create(['status' => 'ativo']);
        $this->user->assignRole($roleCoordenador);

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
    public function coordenador_cria_contato_pf_com_sucesso()
    {
        $response = $this->actingAs($this->user)->post('/relacionamentos', [
            'nome' => 'José da Silva',
            'tipo_pessoa' => 'PF',
            'apelido' => 'Zé',
            'email' => 'jose@silva.com',
            'telefone' => '(19) 98888-7777',
            'genero' => 'masculino',
            'profissao' => 'Metalúrgico',
            'endereco' => 'Rua das Flores, 123',
            'bairro_id' => $this->bairro->id,
            'is_apoiador' => 1,
            'is_voluntario' => 1,
        ]);

        $response->assertRedirect('/relacionamentos');
        
        $this->assertDatabaseHas('relacionamentos', [
            'nome' => 'José da Silva',
            'telefone_normalizado' => '19988887777',
        ]);

        $contato = Relacionamento::where('nome', 'José da Silva')->first();
        $this->assertTrue($contato->is_apoiador);
        $this->assertTrue($contato->is_voluntario);
        $this->assertFalse($contato->is_lideranca);

        // Verifica log de auditoria
        $this->assertDatabaseHas('logs_auditoria', [
            'user_id' => $this->user->id,
            'acao' => 'criacao_relacionamento',
            'tabela' => 'relacionamentos',
        ]);
    }

    /** @test */
    public function sistema_detecta_duplicidade_de_telefone_ou_email_ao_salvar()
    {
        // Cria primeiro contato
        Relacionamento::create([
            'nome' => 'Maria Souza',
            'tipo_pessoa' => 'PF',
            'email' => 'maria@souza.com',
            'telefone' => '(19) 98765-4321',
            'telefone_normalizado' => '19987654321',
        ]);

        // Tenta cadastrar segundo contato com mesmo telefone normalizado
        $response1 = $this->actingAs($this->user)->from('/relacionamentos')->post('/relacionamentos', [
            'nome' => 'Maria Silva',
            'tipo_pessoa' => 'PF',
            'telefone' => '(19) 9-8765-4321', // Mesma normalização!
            'is_apoiador' => 1,
        ]);

        $response1->assertRedirect('/relacionamentos');
        $response1->assertSessionHasErrors('telefone');

        // Tenta cadastrar terceiro contato com mesmo email
        $response2 = $this->actingAs($this->user)->from('/relacionamentos')->post('/relacionamentos', [
            'nome' => 'Maria Oliveira',
            'tipo_pessoa' => 'PF',
            'email' => 'maria@souza.com', // Mesmo e-mail!
            'is_apoiador' => 1,
        ]);

        $response2->assertRedirect('/relacionamentos');
        $response2->assertSessionHasErrors('email');
    }

    /** @test */
    public function sistema_nao_bloqueia_contatos_com_nomes_iguais()
    {
        // Cria primeiro contato
        Relacionamento::create([
            'nome' => 'João Silva',
            'tipo_pessoa' => 'PF',
            'email' => 'joao1@silva.com',
        ]);

        // Cadastra segundo contato com o mesmo nome mas dados de contato diferentes
        $response = $this->actingAs($this->user)->post('/relacionamentos', [
            'nome' => 'João Silva',
            'tipo_pessoa' => 'PF',
            'email' => 'joao2@silva.com',
            'telefone' => '(19) 99999-1111',
            'is_apoiador' => 1,
        ]);

        $response->assertRedirect('/relacionamentos');
        $this->assertDatabaseHas('relacionamentos', [
            'email' => 'joao2@silva.com',
            'nome' => 'João Silva'
        ]);
    }

    /** @test */
    public function coordenador_adiciona_historico_de_interacoes()
    {
        $contato = Relacionamento::create([
            'nome' => 'Carlos Gomes',
            'tipo_pessoa' => 'PF',
        ]);

        $response = $this->actingAs($this->user)->from('/relacionamentos')->post("/relacionamentos/{$contato->id}/interacoes", [
            'tipo' => 'whatsapp',
            'data_interacao' => '2026-08-06',
            'descricao' => 'Carlos confirmou que vai no evento no Centro.',
        ]);

        $response->assertRedirect('/relacionamentos');
        $this->assertDatabaseHas('interacoes', [
            'relacionamento_id' => $contato->id,
            'user_id' => $this->user->id,
            'tipo' => 'whatsapp',
            'descricao' => 'Carlos confirmou que vai no evento no Centro.',
        ]);
    }

    /** @test */
    public function criacao_de_lideranca_salva_dados_na_tabela_liderancas_detalhes()
    {
        $response = $this->actingAs($this->user)->post('/relacionamentos', [
            'nome' => 'Pastor Marcos',
            'tipo_pessoa' => 'PF',
            'is_lideranca' => 1,
            'area_influencia' => 'Igreja Evangélica do Bairro Centro',
            'votos_estimados' => 150,
            'nivel_confianca' => 'alto',
            'justificativa' => 'Estimativa baseada na frequência do culto de domingo.',
            'observacoes_influencia' => 'Liderança religiosa ativa.',
        ]);

        $response->assertRedirect('/relacionamentos');
        
        $contato = Relacionamento::where('nome', 'Pastor Marcos')->first();
        $this->assertTrue($contato->is_lideranca);

        $this->assertDatabaseHas('liderancas_detalhes', [
            'relacionamento_id' => $contato->id,
            'area_influencia' => 'Igreja Evangélica do Bairro Centro',
            'votos_estimados' => 150,
            'nivel_confianca' => 'alto',
            'justificativa' => 'Estimativa baseada na frequência do culto de domingo.',
        ]);
    }

    /** @test */
    public function importacao_csv_faz_previsualizacao_e_confirmacao()
    {
        // 1. Criar planilha CSV fake na memória
        $csvContent = "tipo_pessoa,nome,email,telefone,is_apoiador,is_voluntario,is_lideranca,bairro\n" .
                      "PF,Importado 1,imp1@teste.com,19988881111,true,false,false,Centro\n" .
                      "PF,Importado 2,imp2@teste.com,19988882222,true,true,false,Centro\n";

        $file = UploadedFile::fake()->createWithContent('contatos.csv', $csvContent);

        // 2. Faz o post de upload para pré-visualização
        $response1 = $this->actingAs($this->user)->post('/relacionamentos/importar/preview', [
            'arquivo_csv' => $file
        ]);

        $response1->assertStatus(200);
        $response1->assertViewIs('relacionamentos.importacao_preview');

        // Verifica se os dados foram salvos temporariamente na sessão
        $this->assertTrue(session()->has('importacao_temporaria'));

        // 3. Confirma a gravação dos dados lidos
        $response2 = $this->actingAs($this->user)->post('/relacionamentos/importar/confirmar');

        $response2->assertRedirect('/relacionamentos');
        
        $this->assertDatabaseHas('relacionamentos', ['email' => 'imp1@teste.com']);
        $this->assertDatabaseHas('relacionamentos', ['email' => 'imp2@teste.com']);

        // Verifica log de importação em lote e timeline
        $this->assertDatabaseHas('logs_auditoria', [
            'acao' => 'importacao_lote_csv',
        ]);
    }
}
