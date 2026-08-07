<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ConfiguracaoCampanha;
use App\Models\LogAuditoria;
use App\Models\Timeline;
use App\Helpers\CampaignStorage;

class ConfiguracaoController extends Controller
{
    public function index()
    {
        // Proteção baseada em permissões
        if (!auth()->user()->can('configuracoes.visualizar')) {
            abort(403, 'Você não tem permissão para visualizar as configurações.');
        }

        $config = ConfiguracaoCampanha::obter();
        return view('configuracoes.index', compact('config'));
    }

    public function update(Request $request)
    {
        // Proteção baseada em permissões
        if (!auth()->user()->can('configuracoes.editar')) {
            abort(403, 'Você não tem permissão para editar as configurações.');
        }

        $config = ConfiguracaoCampanha::obter();
        $anterior = $config->toArray();

        $validated = $request->validate([
            'nome_campanha' => 'required|string|max:150',
            'candidato_nome' => 'required|string|max:150',
            'candidato_nome_politico' => 'required|string|max:100',
            'candidato_cargo' => 'required|string|max:100',
            'candidato_numero' => 'required|string|max:20',
            'partido_sigla' => 'required|string|max:20',
            'partido_coligacao' => 'nullable|string|max:255',
            'campanha_cnpj' => 'nullable|string|max:20',
            'campanha_cidade' => 'required|string|max:100',
            'campanha_uf' => 'required|string|size:2',
            'data_primeiro_turno' => 'required|date',
            'campanha_timezone' => 'required|string|max:50',
            'campanha_telefone' => 'nullable|string|max:20',
            'campanha_email' => 'nullable|email|max:100',
            'campanha_site' => 'nullable|string|max:255',
            'campanha_instagram' => 'nullable|string|max:255',
            'campanha_facebook' => 'nullable|string|max:255',
            'campanha_youtube' => 'nullable|string|max:255',
            'identidade_cor_primaria' => 'required|string|max:20',
            'identidade_cor_secundaria' => 'required|string|max:20',
            'texto_institucional_curto' => 'nullable|string',
            'preferencia_tema' => 'required|in:claro,escuro',
            'logotipo' => 'nullable|image|max:2048', // Max 2MB
        ]);

        // Se houver upload de logotipo, salvar usando o CampaignStorage
        $logoPath = $config->identidade_logo_path;
        if ($request->hasFile('logotipo')) {
            // Remove o antigo se houver
            if ($logoPath) {
                CampaignStorage::remover($logoPath);
            }
            $logoPath = CampaignStorage::salvar($request->file('logotipo'), 'logos');
        }

        $config->update([
            'nome_campanha' => $validated['nome_campanha'],
            'candidato_nome' => $validated['candidato_nome'],
            'candidato_nome_politico' => $validated['candidato_nome_politico'],
            'candidato_cargo' => $validated['candidato_cargo'],
            'candidato_numero' => $validated['candidato_numero'],
            'partido_sigla' => $validated['partido_sigla'],
            'partido_coligacao' => $validated['partido_coligacao'] ?? null,
            'campanha_cnpj' => $validated['campanha_cnpj'] ?? null,
            'campanha_cidade' => $validated['campanha_cidade'],
            'campanha_uf' => $validated['campanha_uf'],
            'data_primeiro_turno' => $validated['data_primeiro_turno'],
            'campanha_timezone' => $validated['campanha_timezone'],
            'campanha_telefone' => $validated['campanha_telefone'] ?? null,
            'campanha_email' => $validated['campanha_email'] ?? null,
            'campanha_site' => $validated['campanha_site'] ?? null,
            'campanha_instagram' => $validated['campanha_instagram'] ?? null,
            'campanha_facebook' => $validated['campanha_facebook'] ?? null,
            'campanha_youtube' => $validated['campanha_youtube'] ?? null,
            'identidade_cor_primaria' => $validated['identidade_cor_primaria'],
            'identidade_cor_secundaria' => $validated['identidade_cor_secundaria'],
            'texto_institucional_curto' => $validated['texto_institucional_curto'] ?? null,
            'preferencia_tema' => $validated['preferencia_tema'],
            'identidade_logo_path' => $logoPath,
        ]);

        // Registrar auditoria
        LogAuditoria::registrar(auth()->id(), 'alteracao_configuracoes', 'configuracoes_campanha', $config->id, $anterior, $config->toArray());

        // Registrar timeline
        Timeline::registrar('configuracoes.alteradas', 'Configurações da campanha atualizadas', null, auth()->id(), $config);

        return redirect()->route('configuracoes.index')->with('success', 'Configurações atualizadas com sucesso!');
    }
}
