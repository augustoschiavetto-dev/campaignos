<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\FinanceiroLancamento;
use App\Models\FinanceiroDocumento;
use App\Models\LogAuditoria;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class FinanceiroTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $financeiroUser;
    protected User $consultaUser;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Papéis e Permissões
        $roleAdmin = Role::create(['name' => 'admin']);
        $roleFinanceiro = Role::create(['name' => 'financeiro']);
        $roleConsulta = Role::create(['name' => 'consulta']);

        $permissions = [
            'financeiro.visualizar', 'financeiro.criar', 'financeiro.editar_rascunho', 'financeiro.conferir',
            'financeiro.solicitar_retificacao', 'financeiro.retificar', 'financeiro.conciliar',
            'financeiro.classificar_receita', 'financeiro.definir_exigencia_recibo',
            'financeiro.registrar_recibo_oficial', 'financeiro.baixar_documentos',
            'financeiro.visualizar_dados_fiscais', 'financeiro.visualizar_auditoria'
        ];

        foreach ($permissions as $p) {
            Permission::create(['name' => $p]);
        }

        $roleAdmin->syncPermissions(Permission::all());
        $roleFinanceiro->syncPermissions([
            'financeiro.visualizar', 'financeiro.criar', 'financeiro.editar_rascunho', 'financeiro.conferir',
            'financeiro.solicitar_retificacao', 'financeiro.retificar', 'financeiro.conciliar',
            'financeiro.classificar_receita', 'financeiro.definir_exigencia_recibo',
            'financeiro.registrar_recibo_oficial', 'financeiro.baixar_documentos',
            'financeiro.visualizar_dados_fiscais', 'financeiro.visualizar_auditoria'
        ]);
        $roleConsulta->syncPermissions([
            'financeiro.visualizar'
        ]);

        $this->admin = User::factory()->create();
        $this->admin->assignRole($roleAdmin);

        $this->financeiroUser = User::factory()->create();
        $this->financeiroUser->assignRole($roleFinanceiro);

        $this->consultaUser = User::factory()->create();
        $this->consultaUser->assignRole($roleConsulta);

        Storage::fake('public');
    }

    /** @test */
    public function receita_confirmada_gera_somente_protocolo_interno_e_nao_recibo_oficial()
    {
        $this->actingAs($this->financeiroUser)->post('/financeiro', [
            'tipo' => 'receita',
            'categoria' => 'doacao_pf',
            'valor' => 1500.00,
            'data_lancamento' => '2026-08-07',
            'nome_cadastrado' => 'Doador Exemplo',
            'cpf_cnpj' => '12345678901',
            'meio_pagamento' => 'Pix',
        ]);

        $this->assertDatabaseHas('financeiro_lancamentos', [
            'nome_cadastrado' => 'Doador Exemplo',
            'recibo_oficial_numero' => null, // Não gera número oficial automaticamente
        ]);

        $lancamento = FinanceiroLancamento::first();
        $this->assertNotNull($lancamento->protocolo_interno);
        $this->assertStringStartsWith('FIN-2026-', $lancamento->protocolo_interno);
    }

    /** @test */
    public function pagina_de_protocolo_interno_contem_aviso_de_ausencia_de_validade_oficial()
    {
        $lancamento = FinanceiroLancamento::create([
            'tipo' => 'receita',
            'categoria' => 'doacao_pf',
            'valor' => 1500.00,
            'data_lancamento' => '2026-08-07',
            'nome_cadastrado' => 'Doador Exemplo',
            'cpf_cnpj' => '12345678901',
            'meio_pagamento' => 'Pix',
            'protocolo_interno' => 'FIN-2026-000001',
            'criado_por_id' => $this->financeiroUser->id,
            'status' => 'rascunho',
        ]);

        $response = $this->actingAs($this->financeiroUser)->get("/financeiro/{$lancamento->id}/registro-interno");
        
        $response->assertStatus(200);
        $response->assertSee('REGISTRO INTERNO — SEM VALIDADE COMO RECIBO ELEITORAL OFICIAL');
        $response->assertSee('Este documento destina-se exclusivamente ao controle administrativo interno.');
    }

    /** @test */
    public function usuario_autorizado_pode_registrar_o_numero_oficial()
    {
        $lancamento = FinanceiroLancamento::create([
            'tipo' => 'receita',
            'categoria' => 'doacao_pf',
            'valor' => 1500.00,
            'data_lancamento' => '2026-08-07',
            'nome_cadastrado' => 'Doador Exemplo',
            'cpf_cnpj' => '12345678901',
            'meio_pagamento' => 'Pix',
            'criado_por_id' => $this->financeiroUser->id,
            'status' => 'rascunho',
        ]);

        $response = $this->actingAs($this->financeiroUser)->post("/financeiro/{$lancamento->id}/recibo-oficial", [
            'recibo_oficial_numero' => 'TSE-9988-2026',
            'recibo_oficial_data_emissao' => '2026-08-07',
        ]);

        $response->assertRedirect('/financeiro');
        $this->assertEquals('TSE-9988-2026', $lancamento->fresh()->recibo_oficial_numero);
    }

    /** @test */
    public function usuario_nao_autorizado_nao_pode_registrar_o_numero_oficial()
    {
        $lancamento = FinanceiroLancamento::create([
            'tipo' => 'receita',
            'categoria' => 'doacao_pf',
            'valor' => 1500.00,
            'data_lancamento' => '2026-08-07',
            'nome_cadastrado' => 'Doador Exemplo',
            'cpf_cnpj' => '12345678901',
            'meio_pagamento' => 'Pix',
            'criado_por_id' => $this->financeiroUser->id,
            'status' => 'rascunho',
        ]);

        $response = $this->actingAs($this->consultaUser)->post("/financeiro/{$lancamento->id}/recibo-oficial", [
            'recibo_oficial_numero' => 'TSE-9988-2026',
            'recibo_oficial_data_emissao' => '2026-08-07',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function confirmacao_de_dispensa_de_recibo_exige_justificativa()
    {
        $lancamento = FinanceiroLancamento::create([
            'tipo' => 'receita',
            'categoria' => 'rendimento_aplicacao',
            'valor' => 50.00,
            'data_lancamento' => '2026-08-07',
            'nome_cadastrado' => 'Banco de Limeira',
            'cpf_cnpj' => '12345678000100',
            'meio_pagamento' => 'Transferência Bancária',
            'criado_por_id' => $this->financeiroUser->id,
            'status' => 'rascunho',
        ]);

        $response = $this->actingAs($this->financeiroUser)->post("/financeiro/{$lancamento->id}/exigencia", [
            'exigencia_decisao' => 'dispensado',
            'exigencia_justificativa' => '', // Faltando justificativa
        ]);

        $response->assertSessionHasErrors('exigencia_justificativa');
    }

    /** @test */
    public function lancamento_conferido_nao_pode_ser_alterado_normalmente()
    {
        $lancamento = FinanceiroLancamento::create([
            'tipo' => 'receita',
            'categoria' => 'doacao_pf',
            'valor' => 1500.00,
            'data_lancamento' => '2026-08-07',
            'nome_cadastrado' => 'Doador Exemplo',
            'cpf_cnpj' => '12345678901',
            'meio_pagamento' => 'Pix',
            'criado_por_id' => $this->financeiroUser->id,
            'status' => 'conferido',
        ]);

        $response = $this->actingAs($this->financeiroUser)->post("/financeiro/{$lancamento->id}/rascunho", [
            'valor' => 2000.00,
            'categoria' => 'doacao_pf',
            'data_lancamento' => '2026-08-07',
            'nome_cadastrado' => 'Doador Exemplo',
            'cpf_cnpj' => '12345678901',
            'meio_pagamento' => 'Pix',
            'status' => 'conferido',
        ]);

        $response->assertStatus(400); // Bloqueado
    }

    /** @test */
    public function retificacao_preserva_valores_anteriores()
    {
        $lancamento = FinanceiroLancamento::create([
            'tipo' => 'receita',
            'categoria' => 'doacao_pf',
            'valor' => 1500.00,
            'data_lancamento' => '2026-08-07',
            'nome_cadastrado' => 'Doador Exemplo',
            'cpf_cnpj' => '12345678901',
            'meio_pagamento' => 'Pix',
            'criado_por_id' => $this->financeiroUser->id,
            'status' => 'retificacao_solicitada',
        ]);

        $this->actingAs($this->financeiroUser)->post("/financeiro/{$lancamento->id}/retificar", [
            'valor' => 1800.00,
            'categoria' => 'doacao_pf',
            'data_lancamento' => '2026-08-07',
            'nome_cadastrado' => 'Doador Exemplo',
            'cpf_cnpj' => '12345678901',
            'meio_pagamento' => 'Pix',
            'motivo_retificacao' => 'Ajuste de valor conforme extrato',
        ]);

        $lancamento = $lancamento->fresh();
        $this->assertEquals('retificado', $lancamento->status);
        $this->assertEquals(1500.00, $lancamento->valor_anterior);
        $this->assertEquals(1800.00, $lancamento->valor_novo);
        $this->assertEquals('Ajuste de valor conforme extrato', $lancamento->retificado_motivo);
    }

    /** @test */
    public function exclusao_fisica_de_lancamento_financeiro_eh_bloqueada()
    {
        $lancamento = FinanceiroLancamento::create([
            'tipo' => 'receita',
            'categoria' => 'doacao_pf',
            'valor' => 1500.00,
            'data_lancamento' => '2026-08-07',
            'nome_cadastrado' => 'Doador Exemplo',
            'cpf_cnpj' => '12345678901',
            'meio_pagamento' => 'Pix',
            'criado_por_id' => $this->financeiroUser->id,
            'status' => 'rascunho',
        ]);

        $response = $this->actingAs($this->financeiroUser)->post("/financeiro/{$lancamento->id}/deletar");

        $response->assertStatus(400); // Proibido
        $this->assertDatabaseHas('financeiro_lancamentos', ['id' => $lancamento->id]);
    }

    /** @test */
    public function documentos_financeiros_possuem_download_protegido()
    {
        $lancamento = FinanceiroLancamento::create([
            'tipo' => 'receita',
            'categoria' => 'doacao_pf',
            'valor' => 1500.00,
            'data_lancamento' => '2026-08-07',
            'nome_cadastrado' => 'Doador Exemplo',
            'cpf_cnpj' => '12345678901',
            'meio_pagamento' => 'Pix',
            'criado_por_id' => $this->financeiroUser->id,
            'status' => 'rascunho',
        ]);

        $doc = FinanceiroDocumento::create([
            'lancamento_id' => $lancamento->id,
            'tipo_documento' => 'documento_doador',
            'arquivo_path' => 'financeiro/exemplo.pdf',
            'criado_por_id' => $this->financeiroUser->id,
        ]);

        // Usuario consulta tenta baixar sem permissao
        $response = $this->actingAs($this->consultaUser)->get("/financeiro/documento/{$doc->id}/baixar");
        $response->assertStatus(403);
    }
}
