# MVP_SCOPE — CampaignOS MVP Enxuto

**Candidato:** Guto Schiavetto (Deputado Federal)  
**Foco Geográfico:** Limeira-SP  
**Objetivo:** Sistema de gestão interno, simples, responsivo, seguro e de baixo custo de manutenção para publicação em VPS Hostinger.

---

## 1. Mapeamento de Mudanças em Relação ao PRD Original

| Funcionalidade / Módulo | Escopo PRD Original | Escopo MVP Enxuto | Status |
| :--- | :--- | :--- | :--- |
| **PWA Mobile Offline** | Suporte offline total com cache local (IndexedDB) e sincronização. | Aplicativo Web responsivo. Sem modo offline. | **Adiado / Removido** |
| **Integração WhatsApp/SMS** | Disparos automáticos e chatbot receptivo de campanha (Z-API/Evolution). | Sem integrações automáticas. Cadastro manual de contatos e links rápidos de redirecionamento para o app do WhatsApp Web/Desktop. | **Adiado / Removido** |
| **Arrecadação Pix & Recibo** | Gateway de pagamentos integrado, Pix dinâmico, validação automática na Receita Federal e geração automática de recibo eleitoral em PDF. | Controle financeiro interno manual. Lançamento de receitas e despesas com upload de anexos de comprovante/Nota Fiscal. Sem emissão e sem integração com Receita. | **Simplificado** |
| **Geomapeamento e GPS** | Rastreamento em tempo real de voluntários, cálculo automático de rotas e mapa de calor baseado em geolocalização do GPS. | Cadastro manual de bairros de Limeira e associação de contatos/eventos a estes bairros. Visão tabular/cards coloridos de status de cobertura do território. Sem APIs de mapa. | **Simplificado** |
| **Módulo 1: War Room** | Painel executivo com gráficos de analytics integrados e IA para projeções. | Dashboard simplificado baseado em cards, contagem regressiva, indicadores de contatos e atividades pendentes. "Prioridades do Dia" cadastradas manualmente e controle de Alertas de 3 níveis. | **Simplificado** |
| **Módulo 2: CRM** | CRM de Eleitores focado apenas no cidadão eleitor e opt-in complexo de consentimento. | Renomeado para **Relacionamentos**. Cadastro unificado de apoiadores, voluntários, jornalistas, fornecedores, etc. Sem obrigatoriedade de CPF. Consentimento simplificado. Detecção de duplicidade por e-mail/telefone. | **Simplificado** |
| **Módulo 3: Lideranças** | Pontuação complexa e dinâmica de lideranças. | Especialização simples de relacionamento (sem duplicar tabela), com filtros para lideranças estratégicas, sem contato recente e demandas mapeadas. | **Simplificado** |
| **Módulo 4: Agenda & Eventos** | Integração a calendários externos (Google Calendar), cálculo automático de tempo de deslocamento. | Calendário simplificado Web/Mobile (lista, semana, mês), com alerta básico de sobreposição e checklist simples para tarefas do evento. Tempo de deslocamento definido manualmente. | **Simplificado** |
| **Módulo 5: Território** | Integração geográfica via mapas digitais interativos. | Tabela operacional de bairros de Limeira e suas prioridades, metas e coberturas. | **Simplificado** |
| **Módulo 6: Demandas** | Encaminhamento para órgãos e acompanhamento por IA. | Módulo interno simples de controle de pendências para evitar perdas no WhatsApp (demandas, promessas, ofertas de ajuda). | **Simplificado** |
| **Módulo 7: Tarefas** | Visualização em Kanban interativo arrastável complexo. | Gerenciador simples de tarefas com prioridades, checklist, comentários e visualização de lista agrupada por status. | **Simplificado** |
| **Módulo 8: Marketing** | Integração nativa e postagem direta em redes sociais. | Fila editorial de controle de etapas de conteúdo e resultados de engajamento informados manualmente. Módulo de relacionamento básico com a imprensa. | **Simplificado** |
| **Módulo 9: Operações** | Integração com códigos de barra e RFID para estoque. | Tabela básica de estoque de materiais de campanha (santinhos, camisetas, etc.) com movimentação de entrada/saída e alertas de nível mínimo. | **Simplificado** |
| **Módulo 10: Financeiro** | Integração bancária automática (Open Finance). | Controle interno simplificado de fluxo de caixa (receitas/despesas), aprovação administrativa e anexação de comprovantes. Apoio ao contador. | **Simplificado** |
| **Módulo 11: Mural** | Chat interno em tempo real via WebSockets. | Mural estático simples de avisos operacionais da campanha criados por coordenadores. Sem chat. | **Simplificado** |
| **Módulo 12: Arquivos** | Cloud storage complexo integrado e player interno de mídia. | Biblioteca de links e arquivos carregados com limite de tamanho de upload, aceitando links externos do Google Drive. | **Simplificado** |

---

## 2. Estrutura Operacional

### Perfis de Usuários (RBAC Estrito):
1.  **Administrador:** Acesso técnico irrestrito, logs de sistema e controle de usuários.
2.  **Coordenador Geral:** Controle de toda a operação (War Room, Relacionamentos, Agenda, Financeiro).
3.  **Agenda:** Gerenciamento exclusivo do Módulo 4 (Agenda e Eventos).
4.  **Marketing:** Gerenciamento exclusivo do Módulo 8 (Marketing, Conteúdo e Imprensa) e Biblioteca de Arquivos.
5.  **Financeiro:** Acesso de edição exclusivo ao Módulo 10 (Financeiro).
6.  **Operacional:** Gerenciamento de tarefas (Módulo 7), materiais (Módulo 9) e bairros (Módulo 5).
7.  **Consulta:** Visualização de leitura dos módulos permitidos, sem permissão de alteração ou exclusão.
