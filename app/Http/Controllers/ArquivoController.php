<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Arquivo;
use App\Models\ArquivoVersao;
use App\Models\User;
use App\Models\LogAuditoria;
use App\Models\Timeline;
use App\Helpers\CampaignStorage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ArquivoController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('arquivos.visualizar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $query = Arquivo::with(['responsavel', 'versoes']);

        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        $arquivos = $query->orderBy('nome')->get();
        $usuarios = User::where('status', 'ativo')->get();

        return view('arquivos.index', compact('arquivos', 'usuarios'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('arquivos.enviar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $request->validate([
            'nome' => 'required|string|max:150',
            'categoria' => 'required|string|max:50',
            'descricao' => 'nullable|string',
            'is_link_externo' => 'required|boolean',
            'link_externo' => 'nullable|string|url|max:255',
            // Arquivo local max 5MB (limite configurável)
            'arquivo_local' => 'nullable|file|max:5120|mimes:pdf,jpg,png,doc,docx,xls,xlsx,csv,txt,zip,mp3',
            'tags' => 'nullable|string|max:255',
            'observacoes' => 'nullable|string',
        ]);

        $isLink = $request->boolean('is_link_externo');
        $pathOrLink = '';

        if ($isLink) {
            if (empty($request->link_externo)) {
                return back()->withErrors(['link_externo' => 'O link externo é obrigatório quando a opção estiver marcada.']);
            }
            $pathOrLink = $request->link_externo;
        } else {
            if (!$request->hasFile('arquivo_local')) {
                return back()->withErrors(['arquivo_local' => 'O arquivo local é obrigatório.']);
            }
            // Salvar no storage privado via helper
            $pathOrLink = CampaignStorage::salvar($request->file('arquivo_local'), $request->categoria);
        }

        $arquivo = DB::transaction(function () use ($request, $isLink, $pathOrLink) {
            $arquivo = Arquivo::create([
                'nome' => $request->nome,
                'categoria' => $request->categoria,
                'descricao' => $request->descricao ?? null,
                'path_ou_link' => $pathOrLink,
                'is_link_externo' => $isLink,
                'responsavel_id' => auth()->id(),
                'tags' => $request->tags ?? null,
                'data' => now(),
                'versao' => 1,
                'status' => 'ativo',
                'observacoes' => $request->observacoes ?? null,
            ]);

            // Se for arquivo local tipo documento/roteiro, cria primeira versão versionada
            if (!$isLink) {
                ArquivoVersao::create([
                    'arquivo_id' => $arquivo->id,
                    'versao' => 1,
                    'data' => now(),
                    'autor_id' => auth()->id(),
                    'status' => 'aprovado',
                    'observacao' => 'Versão inicial do upload.',
                    'file_path' => $pathOrLink,
                ]);
            }

            return $arquivo;
        });

        LogAuditoria::registrar(auth()->id(), 'upload_arquivo', 'arquivos', $arquivo->id, null, $arquivo->toArray());

        Timeline::registrar(
            'arquivos.criado',
            "Arquivo '{$arquivo->nome}' adicionado à biblioteca",
            "Categoria: " . ucfirst($arquivo->categoria) . " | Tipo: " . ($isLink ? 'Link Externo' : 'Arquivo Local'),
            auth()->id()
        );

        return redirect()->route('arquivos.index')->with('success', 'Arquivo/Link cadastrado com sucesso!');
    }

    public function download(int $id)
    {
        if (!auth()->user()->can('arquivos.baixar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $arquivo = Arquivo::findOrFail($id);

        if ($arquivo->is_link_externo) {
            return redirect()->away($arquivo->path_ou_link);
        }

        if (!CampaignStorage::existe($arquivo->path_ou_link)) {
            abort(404, 'Arquivo não encontrado no servidor.');
        }

        LogAuditoria::registrar(auth()->id(), 'download_arquivo_restrito', 'arquivos', $arquivo->id, null, ['nome' => $arquivo->nome]);

        return CampaignStorage::baixar($arquivo->path_ou_link);
    }

    public function novaVersao(Request $request, int $id)
    {
        if (!auth()->user()->can('arquivos.enviar')) {
            abort(403, 'Acesso não autorizado.');
        }

        $request->validate([
            'observacao' => 'required|string',
            'conteudo_texto' => 'nullable|string',
            'arquivo_local' => 'nullable|file|max:5120|mimes:pdf,jpg,png,doc,docx,xls,xlsx,csv,txt,zip,mp3',
        ]);

        $arquivo = Arquivo::findOrFail($id);
        $anterior = $arquivo->toArray();

        $novaVersaoNum = $arquivo->versao + 1;
        $path = $arquivo->path_ou_link;

        if ($request->hasFile('arquivo_local')) {
            $path = CampaignStorage::salvar($request->file('arquivo_local'), $arquivo->categoria);
        }

        DB::transaction(function () use ($arquivo, $novaVersaoNum, $path, $request) {
            $arquivo->update([
                'versao' => $novaVersaoNum,
                'path_ou_link' => $path,
            ]);

            ArquivoVersao::create([
                'arquivo_id' => $arquivo->id,
                'versao' => $novaVersaoNum,
                'data' => now(),
                'autor_id' => auth()->id(),
                'status' => 'rascunho',
                'observacao' => $request->observacao,
                'conteudo_texto' => $request->conteudo_texto ?? null,
                'file_path' => $request->hasFile('arquivo_local') ? $path : null,
            ]);
        });

        LogAuditoria::registrar(auth()->id(), 'criacao_nova_versao_arquivo', 'arquivos', $arquivo->id, $anterior, $arquivo->toArray());

        Timeline::registrar(
            'arquivos.nova_versao',
            "Nova versão v{$novaVersaoNum} criada para '{$arquivo->nome}'",
            "Anotação: {$request->observacao}",
            auth()->id()
        );

        return back()->with('success', 'Nova versão do documento cadastrada com sucesso!');
    }
}
