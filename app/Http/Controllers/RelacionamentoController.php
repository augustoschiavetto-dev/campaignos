<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Relacionamento;
use App\Models\LiderancaDetalhe;
use App\Models\Interacao;
use App\Models\Bairro;
use App\Models\Tag;
use App\Models\User;
use App\Models\LogAuditoria;
use App\Models\Timeline;
use App\Models\TipoRelacionamento;
use Illuminate\Support\Facades\DB;

class RelacionamentoController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('relacionamentos.visualizar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $query = Relacionamento::with(['bairro', 'responsavel', 'tags', 'tipos', 'liderancaDetalhe']);

        // Busca por Nome, Apelido, E-mail ou Telefone
        if ($request->filled('busca')) {
            $busca = $request->busca;
            $query->where(function ($q) use ($busca) {
                $q->where('nome', 'like', "%{$busca}%")
                  ->orWhere('apelido', 'like', "%{$busca}%")
                  ->orWhere('email', 'like', "%{$busca}%")
                  ->orWhere('telefone', 'like', "%{$busca}%");
            });
        }

        // Filtro por Bairro
        if ($request->filled('bairro_id')) {
            $query->where('bairro_id', $request->bairro_id);
        }

        // Filtro por Responsável Interno
        if ($request->filled('responsavel_id')) {
            $query->where('responsavel_id', $request->responsavel_id);
        }

        // Filtro por tipo de relacionamento (Apoiador, Liderança, Voluntário, Equipe - N:N)
        if ($request->filled('tipo_relacionamento')) {
            $tipo = $request->tipo_relacionamento;
            $query->whereHas('tipos', function ($q) use ($tipo) {
                $q->where('tipos_relacionamento.nome', $tipo);
            });
        }

        // Filtro por Tag
        if ($request->filled('tag_id')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('tags.id', $request->tag_id);
            });
        }

        $contatos = $query->orderBy('nome')->paginate(15);
        $bairros = Bairro::orderBy('nome')->get();
        $usuarios = User::where('status', 'ativo')->get();
        $tags = Tag::orderBy('nome')->get();

        return view('relacionamentos.index', compact('contatos', 'bairros', 'usuarios', 'tags'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('relacionamentos.criar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $validated = $request->validate([
            'nome' => 'required|string|max:150',
            'tipo_pessoa' => 'required|in:PF,PJ',
            'apelido' => 'nullable|string|max:100',
            'cpf_cnpj' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'telefone' => 'nullable|string|max:20',
            'genero' => 'nullable|string|max:20',
            'profissao' => 'nullable|string|max:100',
            'endereco' => 'nullable|string|max:255',
            'bairro_id' => 'nullable|exists:bairros,id',
            'responsavel_id' => 'nullable|exists:users,id',
            'is_apoiador' => 'nullable|boolean',
            'is_lideranca' => 'nullable|boolean',
            'is_voluntario' => 'nullable|boolean',
            'is_equipe' => 'nullable|boolean',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            // Próxima Ação
            'data_proxima_acao' => 'nullable|date',
            'descricao_proxima_acao' => 'nullable|string',
            // Extensão Liderança (Avaliação Manual Detalhada)
            'area_influencia' => 'nullable|string|max:255',
            'votos_estimados' => 'nullable|integer|min:0',
            'nivel_confianca' => 'nullable|in:alto,medio,baixo',
            'justificativa' => 'nullable|string',
            'observacoes_influencia' => 'nullable|string',
        ]);

        // Verificação inteligente de duplicidade por e-mail ou telefone normalizado
        $telefoneNormalizado = $request->filled('telefone') ? preg_replace('/\D/', '', $request->telefone) : null;

        if ($request->filled('email')) {
            $duplicadoEmail = Relacionamento::where('email', $request->email)->first();
            if ($duplicadoEmail) {
                return back()->withErrors(['email' => "Conflito: O e-mail informado já pertence ao contato '{$duplicadoEmail->nome}'."])->withInput();
            }
        }

        if ($telefoneNormalizado) {
            $duplicadoTelefone = Relacionamento::where('telefone_normalizado', $telefoneNormalizado)->first();
            if ($duplicadoTelefone) {
                return back()->withErrors(['telefone' => "Conflito: O telefone informado já pertence ao contato '{$duplicadoTelefone->nome}'."])->withInput();
            }
        }

        // Criar contato em transação SQL
        $contato = DB::transaction(function () use ($validated, $request, $telefoneNormalizado) {
            $contato = Relacionamento::create([
                'tipo_pessoa' => $validated['tipo_pessoa'],
                'nome' => $validated['nome'],
                'apelido' => $validated['apelido'] ?? null,
                'cpf_cnpj' => $validated['cpf_cnpj'] ?? null,
                'email' => $validated['email'] ?? null,
                'telefone' => $validated['telefone'] ?? null,
                'telefone_normalizado' => $telefoneNormalizado,
                'data_nascimento' => $request->data_nascimento ?? null,
                'genero' => $validated['genero'] ?? null,
                'profissao' => $validated['profissao'] ?? null,
                'endereco' => $validated['endereco'] ?? null,
                'bairro_id' => $validated['bairro_id'] ?? null,
                'responsavel_id' => $validated['responsavel_id'] ?? null,
                'data_proxima_acao' => $validated['data_proxima_acao'] ?? null,
                'descricao_proxima_acao' => $validated['descricao_proxima_acao'] ?? null,
            ]);

            // Sincronizar tipos N:N
            $tiposIds = [];
            if ($request->boolean('is_apoiador')) {
                $tiposIds[] = TipoRelacionamento::where('nome', 'apoiador')->first()?->id;
            }
            if ($request->boolean('is_voluntario')) {
                $tiposIds[] = TipoRelacionamento::where('nome', 'voluntario')->first()?->id;
            }
            if ($request->boolean('is_lideranca')) {
                $tiposIds[] = TipoRelacionamento::where('nome', 'lideranca')->first()?->id;
            }
            if ($request->boolean('is_equipe')) {
                $tiposIds[] = TipoRelacionamento::where('nome', 'equipe')->first()?->id;
            }
            $contato->tipos()->sync(array_filter($tiposIds));

            // Associa Tags se houver
            if (!empty($validated['tags'])) {
                $contato->tags()->sync($validated['tags']);
            }

            // Se for liderança, salva detalhes com avaliação manual de votos
            if ($request->boolean('is_lideranca')) {
                LiderancaDetalhe::create([
                    'relacionamento_id' => $contato->id,
                    'area_influencia' => $validated['area_influencia'] ?? null,
                    'votos_estimados' => $validated['votos_estimados'] ?? 0,
                    'data_estimativa' => now(),
                    'responsavel_estimativa_id' => auth()->id(),
                    'justificativa' => $validated['justificativa'] ?? null,
                    'nivel_confianca' => $validated['nivel_confianca'] ?? 'medio',
                    'observacoes_influencia' => $validated['observacoes_influencia'] ?? null,
                ]);
            }

            return $contato;
        });

        LogAuditoria::registrar(auth()->id(), 'criacao_relacionamento', 'relacionamentos', $contato->id, null, $contato->toArray());
        
        $isLid = $contato->tipos->contains('nome', 'lideranca');
        $isVol = $contato->tipos->contains('nome', 'voluntario');
        Timeline::registrar(
            'relacionamento.criado',
            "Contato '{$contato->nome}' cadastrado",
            "Segmentação: " . ($isLid ? 'Liderança' : ($isVol ? 'Voluntário' : 'Apoiador')),
            auth()->id(),
            $contato
        );

        return redirect()->route('relacionamentos.index')->with('success', 'Contato cadastrado com sucesso!');
    }

    public function update(Request $request, int $id)
    {
        if (!auth()->user()->can('relacionamentos.editar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $contato = Relacionamento::findOrFail($id);
        $anterior = $contato->toArray();

        $validated = $request->validate([
            'nome' => 'required|string|max:150',
            'tipo_pessoa' => 'required|in:PF,PJ',
            'apelido' => 'nullable|string|max:100',
            'cpf_cnpj' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'telefone' => 'nullable|string|max:20',
            'genero' => 'nullable|string|max:20',
            'profissao' => 'nullable|string|max:100',
            'endereco' => 'nullable|string|max:255',
            'bairro_id' => 'nullable|exists:bairros,id',
            'responsavel_id' => 'nullable|exists:users,id',
            'is_apoiador' => 'nullable|boolean',
            'is_lideranca' => 'nullable|boolean',
            'is_voluntario' => 'nullable|boolean',
            'is_equipe' => 'nullable|boolean',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'data_proxima_acao' => 'nullable|date',
            'descricao_proxima_acao' => 'nullable|string',
            // Liderança
            'area_influencia' => 'nullable|string|max:255',
            'votos_estimados' => 'nullable|integer|min:0',
            'nivel_confianca' => 'nullable|in:alto,medio,baixo',
            'justificativa' => 'nullable|string',
            'observacoes_influencia' => 'nullable|string',
        ]);

        $telefoneNormalizado = $request->filled('telefone') ? preg_replace('/\D/', '', $request->telefone) : null;

        // Duplicidade e-mail
        if ($request->filled('email') && $request->email !== $contato->email) {
            $duplicadoEmail = Relacionamento::where('email', $request->email)->first();
            if ($duplicadoEmail) {
                return back()->withErrors(['email' => "O e-mail informado já está em uso por '{$duplicadoEmail->nome}'."]);
            }
        }

        // Duplicidade telefone
        if ($telefoneNormalizado && $telefoneNormalizado !== $contato->telefone_normalizado) {
            $duplicadoTelefone = Relacionamento::where('telefone_normalizado', $telefoneNormalizado)->first();
            if ($duplicadoTelefone) {
                return back()->withErrors(['telefone' => "O telefone informado já está em uso por '{$duplicadoTelefone->nome}'."]);
            }
        }

        DB::transaction(function () use ($contato, $validated, $request, $telefoneNormalizado) {
            $contato->update([
                'tipo_pessoa' => $validated['tipo_pessoa'],
                'nome' => $validated['nome'],
                'apelido' => $validated['apelido'] ?? null,
                'cpf_cnpj' => $validated['cpf_cnpj'] ?? null,
                'email' => $validated['email'] ?? null,
                'telefone' => $validated['telefone'] ?? null,
                'telefone_normalizado' => $telefoneNormalizado,
                'data_nascimento' => $request->data_nascimento ?? null,
                'genero' => $validated['genero'] ?? null,
                'profissao' => $validated['profissao'] ?? null,
                'endereco' => $validated['endereco'] ?? null,
                'bairro_id' => $validated['bairro_id'] ?? null,
                'responsavel_id' => $validated['responsavel_id'] ?? null,
                'data_proxima_acao' => $validated['data_proxima_acao'] ?? null,
                'descricao_proxima_acao' => $validated['descricao_proxima_acao'] ?? null,
            ]);

            // Sincronizar tipos N:N
            $tiposIds = [];
            if ($request->boolean('is_apoiador')) {
                $tiposIds[] = TipoRelacionamento::where('nome', 'apoiador')->first()?->id;
            }
            if ($request->boolean('is_voluntario')) {
                $tiposIds[] = TipoRelacionamento::where('nome', 'voluntario')->first()?->id;
            }
            if ($request->boolean('is_lideranca')) {
                $tiposIds[] = TipoRelacionamento::where('nome', 'lideranca')->first()?->id;
            }
            if ($request->boolean('is_equipe')) {
                $tiposIds[] = TipoRelacionamento::where('nome', 'equipe')->first()?->id;
            }
            $contato->tipos()->sync(array_filter($tiposIds));

            // Sync Tags
            $contato->tags()->sync($validated['tags'] ?? []);

            // Extensão Liderança
            if ($request->boolean('is_lideranca')) {
                LiderancaDetalhe::updateOrCreate(
                    ['relacionamento_id' => $contato->id],
                    [
                        'area_influencia' => $validated['area_influencia'] ?? null,
                        'votos_estimados' => $validated['votos_estimados'] ?? 0,
                        'data_estimativa' => now(),
                        'responsavel_estimativa_id' => auth()->id(),
                        'justificativa' => $validated['justificativa'] ?? null,
                        'nivel_confianca' => $validated['nivel_confianca'] ?? 'medio',
                        'observacoes_influencia' => $validated['observacoes_influencia'] ?? null,
                    ]
                );
            } else {
                LiderancaDetalhe::where('relacionamento_id', $contato->id)->delete();
            }
        });

        LogAuditoria::registrar(auth()->id(), 'edicao_relacionamento', 'relacionamentos', $contato->id, $anterior, $contato->toArray());

        return redirect()->route('relacionamentos.index')->with('success', 'Contato atualizado com sucesso!');
    }

    public function destroy(int $id)
    {
        if (!auth()->user()->can('relacionamentos.excluir')) {
            abort(403, 'Acesso não autorizado.');
        }

        $contato = Relacionamento::findOrFail($id);
        $anterior = $contato->toArray();

        $contato->delete();

        LogAuditoria::registrar(auth()->id(), 'exclusao_relacionamento', 'relacionamentos', $id, $anterior, null);

        Timeline::registrar('relacionamento.excluido', "Contato '{$anterior['nome']}' removido do CRM", null, auth()->id());

        return redirect()->route('relacionamentos.index')->with('success', 'Contato excluído com sucesso!');
    }

    public function adicionarInteracao(Request $request, int $id)
    {
        $request->validate([
            'tipo' => 'required|in:whatsapp,telefonema,reuniao,visita,email,outro',
            'data_interacao' => 'required|date',
            'descricao' => 'required|string',
        ]);

        $contato = Relacionamento::findOrFail($id);

        $interacao = Interacao::create([
            'relacionamento_id' => $contato->id,
            'user_id' => auth()->id(),
            'tipo' => $request->tipo,
            'data_interacao' => $request->data_interacao,
            'descricao' => $request->descricao,
        ]);

        Timeline::registrar(
            'relacionamento.interacao',
            "Nova interação com '{$contato->nome}'",
            ucfirst($request->tipo) . ": " . substr($request->descricao, 0, 50) . "...",
            auth()->id(),
            $contato
        );

        return back()->with('success', 'Interação registrada com sucesso!');
    }

    public function previsualizarImportacao(Request $request)
    {
        if (!auth()->user()->can('relacionamentos.criar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $request->validate([
            'arquivo_csv' => 'required|file|mimes:csv,txt|max:2048'
        ]);

        $file = $request->file('arquivo_csv');
        $filePath = $file->getRealPath();

        $lines = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = null;
            while (($row = fgetcsv($handle, 1000, ';')) !== false) {
                if (count($row) === 1) {
                    rewind($handle);
                    while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                        $lines[] = $row;
                    }
                    break;
                }
                $lines[] = $row;
            }
            fclose($handle);
        }

        if (count($lines) < 2) {
            return back()->withErrors(['arquivo_csv' => 'O arquivo CSV enviado está vazio ou não possui cabeçalhos válidos.']);
        }

        $cabecalhos = array_map('trim', $lines[0]);
        $registros = [];

        for ($i = 1; $i < count($lines); $i++) {
            $row = $lines[$i];
            if (empty(array_filter($row))) continue;

            $registro = [];
            foreach ($cabecalhos as $idx => $cabecalho) {
                $registro[$cabecalho] = isset($row[$idx]) ? trim($row[$idx]) : '';
            }

            $email = $registro['email'] ?? null;
            $tel = $registro['telefone'] ?? null;
            $telNormal = $tel ? preg_replace('/\D/', '', $tel) : null;

            $duplicado = false;
            $duplicadoNome = '';

            if ($email) {
                $check = Relacionamento::where('email', $email)->first();
                if ($check) {
                    $duplicado = true;
                    $duplicadoNome = $check->nome;
                }
            }

            if ($telNormal && !$duplicado) {
                $check = Relacionamento::where('telefone_normalizado', $telNormal)->first();
                if ($check) {
                    $duplicado = true;
                    $duplicadoNome = $check->nome;
                }
            }

            $registro['duplicado'] = $duplicado;
            $registro['duplicado_nome'] = $duplicadoNome;
            $registros[] = $registro;
        }

        session(['importacao_temporaria' => $registros]);

        $bairros = Bairro::orderBy('nome')->get();

        return view('relacionamentos.importacao_preview', compact('registros', 'bairros'));
    }

    public function confirmarImportacao(Request $request)
    {
        if (!auth()->user()->can('relacionamentos.criar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $registros = session('importacao_temporaria', []);
        if (empty($registros)) {
            return redirect()->route('relacionamentos.index')->withErrors(['importacao' => 'Nenhum registro para importar.']);
        }

        $importadosCount = 0;

        DB::transaction(function () use ($registros, &$importadosCount) {
            foreach ($registros as $reg) {
                if ($reg['duplicado'] ?? false) continue;

                $bairroId = null;
                if (!empty($reg['bairro'])) {
                    $bairroObj = Bairro::where('nome', 'like', "%{$reg['bairro']}%")->first();
                    if ($bairroObj) {
                        $bairroId = $bairroObj->id;
                    }
                }

                $tel = $reg['telefone'] ?? null;
                $telNormal = $tel ? preg_replace('/\D/', '', $tel) : null;

                $contato = Relacionamento::create([
                    'tipo_pessoa' => $reg['tipo_pessoa'] ?? 'PF',
                    'nome' => $reg['nome'] ?? 'Importado',
                    'email' => $reg['email'] ?? null,
                    'telefone' => $tel,
                    'telefone_normalizado' => $telNormal,
                    'genero' => $reg['genero'] ?? null,
                    'profissao' => $reg['profissao'] ?? null,
                    'endereco' => $reg['endereco'] ?? null,
                    'bairro_id' => $bairroId,
                ]);

                // Sincroniza tipos pela importação
                $tiposIds = [];
                if (filter_var($reg['is_apoiador'] ?? true, FILTER_VALIDATE_BOOLEAN)) {
                    $tiposIds[] = TipoRelacionamento::where('nome', 'apoiador')->first()?->id;
                }
                if (filter_var($reg['is_voluntario'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                    $tiposIds[] = TipoRelacionamento::where('nome', 'voluntario')->first()?->id;
                }
                if (filter_var($reg['is_lideranca'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                    $tiposIds[] = TipoRelacionamento::where('nome', 'lideranca')->first()?->id;
                }
                $contato->tipos()->sync(array_filter($tiposIds));

                $importadosCount++;
            }
        });

        session()->forget('importacao_temporaria');

        LogAuditoria::registrar(auth()->id(), 'importacao_lote_csv', 'relacionamentos', 0, null, ['registros_importados' => $importadosCount]);
        
        Timeline::registrar('relacionamento.importado', "Importação em lote de {$importadosCount} contatos via CSV", null, auth()->id());

        return redirect()->route('relacionamentos.index')->with('success', "Importação concluída. {$importadosCount} contatos cadastrados.");
    }
}
