# PROJECT_STATUS — Status do Desenvolvimento do CampaignOS MVP

Documento dinâmico de acompanhamento do andamento do projeto. Atualizado ao término de cada etapa técnica.

---

## 1. Etapa Atual
*   **Etapa:** 5 — Marketing, Produção de Conteúdo e Materiais (Módulo 9 e 10)
*   **Status da Etapa:** `A Iniciar`

---

## 2. Checklist de Progresso das Etapas

- [x] **Etapa 1: Fundação & Ajustes Arquiteturais Prévios**
  - [x] Configuração do esqueleto Laravel (SQLite local)
  - [x] Migrations iniciais (users com roles, logs_auditoria, bairros, tags, configuracoes_campanha, timeline e permissões Spatie)
  - [x] Permissões via **Spatie Laravel Permission**
  - [x] Módulo de **Configurações de Campanha**
  - [x] Ampliação do log de **Auditoria**
  - [x] Criação da tabela **Timeline**
  - [x] Helper **CampaignStorage**
  - [x] Suporte a **Tema Claro/Escuro**
  - [x] Sistema de autenticação nativa e layout Blade responsivo
  - [x] Seeders e Testes unitários/funcionais 100% verdes
- [x] **Ajustes Adicionais da Etapa 1 & Gestão de Acessos**
  - [x] Tela administrativa completa de **Usuários e Acessos** (listar, buscar, criar, editar, inativar/reativar, redefinir senha e gerenciar permissões diretas)
  - [x] Travas de segurança (impedir inativar a si mesmo, ou o último admin do sistema)
  - [x] Criação das permissões granulares Spatie e mapeamento no Seeder
  - [x] Rota protegida de download de anexos do mural
  - [x] Testes de acessos e configurações (ArchitecturalAccessTest) 100% verdes
- [x] **Etapa 2: Núcleo Operacional**
  - [x] War Room (Módulo 1) com Prioridades do Dia dinâmicas (CRUD no banco) e Alertas operacionais reais
  - [x] Módulo de **Tarefas** (Módulo 7) com status, checklists, prioridades e comentários
  - [x] Módulo de **Agenda e Eventos** (Módulo 4) com checklists de logística, deslocamento manual e detector de sobreposição de horário
  - [x] Módulo de **Mural de Avisos** (Módulo 11) com anexos persistidos e prioridades estilizadas
  - [x] Testes de tarefas, eventos e sobreposição de horários (CoreOperationalTest) 100% verdes
- [x] **Etapa 3: Relacionamentos (Reestruturada)**
  - [x] Módulo Relacionamentos (Módulo 2) com suporte a contatos PF/PJ, e-mail/telefone e próxima ação
  - [x] Substituição por estrutura de relacionamento muitos-para-muitos (`tipos_relacionamento` e pivot `relacionamento_tipo`)
  - [x] Extensão Lideranças (Módulo 3) integrada com a tabela `liderancas_detalhes` (avaliações manuais, confiança, justificativa e votos estimados)
  - [x] Histórico de interações registradas no CRM
  - [x] Importador de contatos CSV com pré-visualização na tela e validação de duplicidade por e-mail e telefone normalizado
  - [x] Testes de CRM e importação (RelacionamentosTest) 100% verdes
- [x] **Etapa 4: Território**
  - [x] Estrutura territorial hierárquica (Municípios, Regiões, Bairros)
  - [x] Cadastro de **Locais Estratégicos** vinculados a bairros (comércios, praças, igrejas)
  - [x] Painel de Cobertura Territorial com cards por status de cobertura operacional e tabela agregadora
  - [x] Ficha do Bairro consolidada (agregando metas, contatos, líderes, eventos, tarefas e linha do tempo de ações)
  - [x] Módulo de **Demandas e Compromissos** diferenciado
  - [x] Fluxo de aprovação transacional de compromissos oficiais da campanha (exclusivo Coordenador/Admin)
  - [x] Controle de privacidade com observações internas restritas por permissão Spatie
  - [x] Metas de contatos com percentual de atingimento dinâmico
  - [x] Suite de testes territoriais e operacionais (TerritorioTest) 100% verdes
- [x] **Etapa 5: Marketing, Produção de Conteúdo e Materiais**
  - [x] Fila Editorial e Banco de Pautas (pautas digitais, roteiros, frentes temáticas)
  - [x] Ações transacionais de aprovação e reversão de status de roteiros/discursos
  - [x] Registro manual de métricas digitais (visualizações, alcance, novos contatos)
  - [x] Assessoria de imprensa (veículos locais, solicitações, briefings confidenciais e sincronização de agenda)
  - [x] Almoxarifado (estoque de santinhos, panfletos, controle contra saldo negativo, kits e checklists em eventos)
  - [x] Biblioteca de arquivos (uploads de até 5MB, versionamento e download restrito por permissões Spatie)
  - [x] Suíte de testes de Marketing e Estoque (MarketingTest) 100% verdes
- [x] **Funcionalidades Transversais Prévias**
  - [x] Pesquisa Global unificada em 9 módulos com atalho `Ctrl+K` e segurança integrada
  - [x] Favoritos polimórficos com listagem dedicada e toggle ágil
  - [x] Histórico Recente de acessos individuais rastreado e exibido no War Room
  - [x] Suíte de testes transversais (TransversalTest) 100% verdes
- [x] **Etapa 6: Financeiro Simplificado (Concluído)**
  - [x] Lançamentos de receitas e despesas
  - [x] Conciliação e demonstrativos de fluxo de caixa

---

## 3. Decisões de Arquitetura e Engenharia
*   **CRM Muitos para Muitos:** A segmentação de contatos (apoiador, liderança, voluntário, equipe, fornecedor, jornalista) foi migrada de colunas booleanas para tabelas dedicadas (`tipos_relacionamento` e pivot `relacionamento_tipo`), garantindo extensibilidade.
*   **Estimativa Manual de Votos:** Removida qualquer projeção automatizada de votos de lideranças. Modelado como avaliação manual fundamentada, registrando nível de confiança, responsável, justificativa e data.
*   **Hierarquia Territorial Rígida:** Estruturado de forma a permitir múltiplos municípios (Limeira sendo padrão), regiões vinculadas a municípios e bairros a regiões, evitando repetição desorganizada de nomes.
*   **Aprovação Transacional de Promessas:** Criação do campo `aprovado_por_id` e controle transacional de texto final aprovado para garantir que sugestões de voluntários passem pela curadoria da coordenação geral.
*   **Versionamento e Segurança de Roteiros:** Uploads limitados a 5MB, blindados fora do diretório público e versionados na tabela `arquivos_versoes` com suporte a observações de controle de versão.
*   **Pesquisa Global & Acessos baseados em Políticas Spatie:** A pesquisa global e o histórico recente não vazam informações, aplicando regras dinâmicas de permissões a nível de query antes do retorno.
*   **Protocolo de Conformidade Contábil:** O CampaignOS funciona exclusivamente como apoio administrativo interno, não emitindo recibo oficial de prestação de contas do TSE e integrando auditoria/retificação de lançamentos financeiros.

---

## 4. Problemas Encontrados / Bloqueios
*   Nenhum. Todos os testes estão passando.

---

## 5. Próximos Passos
*   Realizar o deploy na VPS Hostinger seguindo os guias de implantação gerados.
