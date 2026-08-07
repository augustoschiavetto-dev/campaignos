<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MuralAviso;
use App\Models\LogAuditoria;
use App\Models\Timeline;
use Illuminate\Support\Facades\Storage;

class MuralController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('mural.visualizar')) {
            abort(403, 'Você não tem permissão para visualizar o mural.');
        }

        $avisos = MuralAviso::with('autor')
            ->orderBy('fixado', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('mural.index', compact('avisos'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('mural.publicar')) {
            abort(403, 'Você não tem permissão para publicar avisos.');
        }

        $validated = $request->validate([
            'titulo' => 'required|string|max:150',
            'mensagem' => 'required|string',
            'prioridade' => 'required|in:critico,atencao,informativo',
            'data_inicio' => 'required|date',
            'data_expiracao' => 'nullable|date|after_or_equal:data_inicio',
            'fixado' => 'nullable|boolean',
            'anexo' => 'nullable|file|max:5120',
        ]);

        $anexoPath = null;
        if ($request->hasFile('anexo')) {
            $anexoPath = \App\Helpers\CampaignStorage::salvar($request->file('anexo'), 'documentos');
        }

        $aviso = MuralAviso::create([
            'titulo' => $validated['titulo'],
            'mensagem' => $validated['mensagem'],
            'autor_id' => auth()->id(),
            'prioridade' => $validated['prioridade'],
            'data_inicio' => $validated['data_inicio'],
            'data_expiracao' => $validated['data_expiracao'] ?? null,
            'fixado' => $request->boolean('fixado'),
            'anexo_path' => $anexoPath,
        ]);

        LogAuditoria::registrar(auth()->id(), 'criacao', 'mural_avisos', $aviso->id, null, $aviso->toArray());

        Timeline::registrar(
            'mural.aviso_criado',
            "Aviso '{$aviso->titulo}' publicado no mural",
            "Mensagem: " . substr($aviso->mensagem, 0, 50) . "...",
            auth()->id(),
            $aviso
        );

        return redirect()->route('mural.index')->with('success', 'Aviso publicado com sucesso!');
    }

    public function destroy(int $id)
    {
        if (!auth()->user()->can('mural.remover')) {
            abort(403, 'Você não tem permissão para remover avisos.');
        }

        $aviso = MuralAviso::findOrFail($id);
        
        if ($aviso->autor_id !== auth()->id() && !auth()->user()->isCoordenador()) {
            abort(403, 'Você não tem permissão para excluir este aviso.');
        }

        $anterior = $aviso->toArray();

        if ($aviso->anexo_path) {
            \App\Helpers\CampaignStorage::remover($aviso->anexo_path);
        }

        $aviso->delete();

        LogAuditoria::registrar(auth()->id(), 'exclusao', 'mural_avisos', $id, $anterior, null);

        Timeline::registrar('mural.aviso_excluido', "Aviso '{$anterior['titulo']}' removido do mural", null, auth()->id());

        return redirect()->route('mural.index')->with('success', 'Aviso excluído com sucesso!');
    }

    /**
     * Rota controlada e protegida para download de anexos do mural.
     */
    public function baixarAnexo(Request $request)
    {
        $request->validate([
            'path' => 'required|string'
        ]);

        // Proteção de segurança baseada em permissão granular
        if (!auth()->user()->can('mural.visualizar')) {
            abort(403, 'Você não tem permissão para acessar este anexo.');
        }

        $path = $request->path;

        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'Arquivo não encontrado no servidor.');
        }

        return Storage::disk('public')->download($path);
    }
}
