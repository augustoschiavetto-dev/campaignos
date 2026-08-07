# IMPLEMENTATION_PLAN — Plano de Implementação Técnico

Plano estruturado para guiar o desenvolvimento ordenado do CampaignOS MVP Enxuto.

---

## 1. Etapas de Desenvolvimento

O desenvolvimento será executado estritamente na ordem obrigatória definida pelo Prompt Mestre.

### Etapa 1 — Fundação (Status: A Iniciar)
*   **Ações:**
    1.  Iniciar o esqueleto do projeto Laravel utilizando a versão estável compatível com PHP 8.2+.
    2.  Configurar banco de dados local (MySQL/PostgreSQL) no arquivo `.env`.
    3.  Modificar a migration padrão `users` para adicionar campos `role`, `status`, `telefone` e `criado_por`.
    4.  Criar a migration de `logs_auditoria`.
    5.  Implementar o sistema de login nativo (utilizando Laravel Breeze ou customizado via controladores simples para evitar excesso de pacotes).
    6.  Criar o Middleware de Controle de Acesso (RBAC) baseado em Roles e Policies.
    7.  Implementar Layout Blade responsivo (Tailwind CSS, menu lateral retrátil no desktop, menu sanduíche para mobile).
    8.  Criar seeders essenciais: Usuários iniciais de exemplo (Admin, Coordenador, Agenda, Marketing, Financeiro, Operacional, Consulta) com senhas padrão seguras, bairros fictícios de Limeira, e categorias básicas.
    9.  Criar testes automatizados de login, controle de perfis (bloqueio de rotas indevidas) e logs de auditoria correspondentes.

### Etapa 2 — Núcleo Operacional
*   **Ações:**
    1.  Desenvolver o painel **War Room** (Módulo 1) com todos os contadores operacionais dinâmicos, seção de "Prioridades do Dia" gerenciada por Coordenador, e "Alertas" de 3 níveis.
    2.  Criar o módulo de **Tarefas** (Módulo 7) com checklist dinâmico, prioridades e listagem agrupada por status.
    3.  Criar o módulo de **Agenda e Eventos** (Módulo 4) com visualizações em lista/mensal, alertas de sobreposição de horário, checklists por evento e tempo de deslocamento manual.
    4.  Criar o **Mural e Prioridades** (Módulo 11) com avisos e expiração automática.
    5.  Implementar testes de conflito de agenda, criação de tarefas e visualização das tarefas atribuídas.

### Etapa 3 — Relacionamentos
*   **Ações:**
    1.  Criar módulo de **Relacionamentos** (Módulo 2) com filtros combinados de Tags, Bairro e Níveis de Apoio.
    2.  Implementar detecção de duplicidade (mesmo e-mail ou WhatsApp).
    3.  Criar recurso de registro de Interações (Ligações, Visitas, Reuniões, Mensagens) associado a cada contato.
    4.  Criar a extensão **Lideranças** (Módulo 3) estendendo dados de contatos, com visões exclusivas para lideranças estratégicas ou sem contato recente.
    5.  Implementar funcionalidade de importação de contatos via planilha CSV.
    6.  Gravar obrigatoriamente logs de auditoria em toda exportação de relatórios.
    7.  Implementar testes de duplicidade, importação de CSV e logs de exportação.

### Etapa 4 — Território
*   **Ações:**
    1.  Criar o módulo de **Território e Bairros** (Módulo 5) com dados demográficos manuais, status de cobertura e metas.
    2.  Criar a visão em cards coloridos por status de cobertura.
    3.  Criar o Módulo de **Demandas, Promessas e Compromissos** (Módulo 6) integrado com contatos e bairros.
    4.  Implementar testes para fluxo de registro de demandas e vinculações territoriais.

### Etapa 5 — Operação
*   **Ações:**
    1.  Criar o Módulo de **Marketing e Fila Editorial** (Módulo 8) para controle de etapas de criação de conteúdo.
    2.  Criar área de controle de solicitações de **Imprensa**.
    3.  Criar Módulo de **Estoque e Materiais** (Módulo 9) com controle de inventário simples e movimentações (Entrada/Saída/Perda).
    4.  Criar biblioteca de **Arquivos e Links** (Módulo 12) aceitando links do Google Drive e arquivos físicos de identidade visual com limite de upload.
    5.  Implementar testes de controle de movimentação de material e alertas de estoque crítico.

### Etapa 6 — Financeiro Simplificado
*   **Ações:**
    1.  Desenvolver Módulo **Financeiro** (Módulo 10): receitas, despesas, categorias, upload de comprovantes fiscais.
    2.  Implementar alertas para despesas vencidas e falta de comprovante.
    3.  Criar relatórios financeiros sintéticos (Receitas x Despesas).
    4.  Implementar testes de lançamento de receitas, despesas e bloqueio de visualização financeira por perfis não autorizados (ex: Marketing, Operacional).

### Etapa 7 — Produção
*   **Ações:**
    1.  Revisão geral do código, limpeza de dados temporários de seeds locais.
    2.  Otimização de consultas do banco de dados (índices nas tabelas).
    3.  Ajustes finos no Tailwind para mobile.
    4.  Criação dos scripts de backup e restauro automatizados.
    5.  Validação do checklist de deploy e homologação em produção na VPS Hostinger.
