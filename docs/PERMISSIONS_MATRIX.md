# PERMISSIONS_MATRIX — Matriz de Papéis e Permissões do CampaignOS MVP

Este documento define e rastreia o modelo de controle de acesso baseado em papéis e permissões (RBAC) implementado através do pacote **Spatie Laravel Permission**.

---

## 1. Definição das Permissões Granulares

Abaixo estão listadas todas as permissões granulares registradas no sistema, organizadas por módulo:

### Usuários e Acessos
*   `usuarios.visualizar`: Visualizar a lista de usuários, logins e histórico administrativo.
*   `usuarios.criar`: Criar novos usuários.
*   `usuarios.editar`: Editar dados e redefinir senhas administrativamente.
*   `usuarios.inativar`: Inativar ou reativar contas de usuários.
*   `usuarios.gerenciar_permissoes`: Atribuir permissões específicas directas a usuários.

### Configurações da Campanha
*   `configuracoes.visualizar`: Visualizar parâmetros de candidato, coligação, timezone e tema.
*   `configuracoes.editar`: Editar parâmetros, cores da identidade visual e logotipo.

### CRM & Relacionamentos (Módulo 2 e 3)
*   `relacionamentos.visualizar`: Visualizar listagem de contatos e histórico de interações.
*   `relacionamentos.criar`: Cadastrar contatos individuais ou importar via planilha CSV.
*   `relacionamentos.editar`: Editar dados de contatos e metadados de lideranças.
*   `relacionamentos.excluir`: Remover contatos do CRM.
*   `relacionamentos.exportar`: Exportar contatos para outros formatos.

### Território (Módulo 5)
*   `territorio.visualizar`: Acessar o painel territorial e a ficha de bairros.
*   `territorio.criar`: Cadastrar novos bairros e regiões.
*   `territorio.editar`: Editar informações geográficas e de reduto.
*   `territorio.inativar`: Alterar o status de cobertura operacional dos bairros.
*   `territorio.gerenciar_metas`: Cadastrar e atualizar metas de contatos por bairro.
*   `locais_estrategicos.visualizar`: Visualizar comitês, praças e comércios estratégicos.
*   `locais_estrategicos.criar`: Cadastrar novos locais de circulação nos bairros.
*   `locais_estrategicos.editar`: Alterar dados de locais de ação.

### Demandas e Compromissos (Módulo 6)
*   `demandas.visualizar`: Visualizar listagem unificada de demandas e compromissos.
*   `demandas.criar`: Cadastrar demandas, oportunidades e problemas.
*   `demandas.editar`: Editar informações operacionais de solicitações.
*   `demandas.concluir`: Marcar uma demanda como concluída.
*   `demandas.cancelar`: Cancelar solicitações operacionais.
*   `compromissos.aprovar`: Transformar uma demanda em Compromisso Formal assumido pela campanha (exclusivo Admin/Coordenador).
*   `compromissos.visualizar_observacoes_internas`: Acesso restrito a observações e anotações internas confidenciais.

### Marketing, Fila Editorial & Imprensa (Etapa 5)
*   `marketing.visualizar`: Acessar fila editorial de conteúdo e banco de pautas.
*   `marketing.criar`: Criar e planejar pautas e ideias de conteúdo.
*   `marketing.editar`: Editar roteiros, chamadas e detalhes de conteúdo.
*   `marketing.aprovar`: Aprovar discursos e roteiros de conteúdo para gravação (Coordenador/Admin).
*   `marketing.registrar_resultados`: Lançar métricas manuais de alcance, visualizações e contatos.
*   `imprensa.visualizar`: Visualizar veículos e solicitações de imprensa local.
*   `imprensa.criar`: Cadastrar solicitações de imprensa e entrevistas.
*   `imprensa.editar`: Editar dados de entrevistas e veículos.
*   `imprensa.responder`: Registrar resposta oficial enviada aos veículos de imprensa.
*   `imprensa.visualizar_briefing`: Acesso restrito a briefings e roteiros de perguntas estratégicas confidenciais.

### Almoxarifado, Kits & Estoque (Etapa 5)
*   `materiais.visualizar`: Acessar inventário de materiais físicos da campanha.
*   `materiais.criar`: Cadastrar novos materiais no almoxarifado.
*   `materiais.editar`: Editar dados cadastrais de materiais e kits.
*   `materiais.movimentar`: Lançar entradas, saídas, perdas e retiradas na quantidade em estoque.
*   `materiais.ajustar_estoque`: Permitir ajustes arbitrários de saldo físico (forçar estoque negativo).

### Biblioteca de Arquivos & Links (Etapa 5)
*   `arquivos.visualizar`: Acessar listagem de arquivos e links externos.
*   `arquivos.enviar`: Fazer upload de novos arquivos (< 5MB) ou cadastrar links.
*   `arquivos.baixar`: Baixar cópia de documentos locais protegidos no servidor.

---

## 2. Atribuição de Permissões aos Papéis (Roles)

| Módulo / Permissão | Admin | Coordenador Geral | Assessor Agenda | Assessor Mkt | Assessor Fin | Coordenador Operacional | Usuário Consulta |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: | :---: |
| `usuarios.*` | Sim | Sim (exceto gerenciar_permissoes) | Não | Não | Não | Não | Não |
| `configuracoes.*` | Sim | Visualizar apenas | Não | Não | Não | Não | Não |
| `relacionamentos.*` | Sim | Sim | Apenas visualizar | Apenas visualizar | Apenas visualizar | Apenas visualizar | Apenas visualizar |
| `territorio.*` | Sim | Sim | Sim (Visualizar) | Não | Não | Sim | Sim (Visualizar) |
| `demandas.criar` | Sim | Sim | Não | Não | Não | Sim | Não |
| `compromissos.aprovar` | Sim | Sim | Não | Não | Não | Não | Não |
| `marketing.*` | Sim | Sim | Não | Sim (exceto aprovar) | Não | Não | Visualizar apenas |
| `imprensa.visualizar_briefing` | Sim | Sim | Não | Sim | Não | Não | Não |
| `materiais.ajustar_estoque` | Sim | Sim | Não | Não | Não | Não | Não |
| `materiais.movimentar` | Sim | Sim | Não | Não | Não | Sim | Não |
| `arquivos.baixar` | Sim | Sim | Não | Sim | Não | Sim | Não |

---

## 3. Segurança das Funcionalidades Transversais

*   **Pesquisa Global:** Os resultados da busca global utilizam síncronamente o método `can()` correspondente ao módulo do registro localizado. Usuários sem permissão de visualização do módulo (ex: Agenda) não receberão esses resultados nos blocos agregados de busca.
*   **Favoritos:** Apenas exibe itens cujos registros originais correspondam a permissões concedidas de visualização do usuário.
*   **Histórico Recente:** O registro de visitas a fichas individuais e briefings estratégicos só é ativado após a aprovação da permissão Spatie de visualização (`territorio.visualizar` ou `imprensa.visualizar_briefing`). O histórico na Dashboard do War Room é pessoal e intransferível de cada operador.
