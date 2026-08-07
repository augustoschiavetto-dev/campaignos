# Checklist de Validação em Produção — CampaignOS

Este checklist deve ser executado integralmente logo após a implantação do sistema na VPS para garantir que todos os módulos e permissões funcionem sem falhas no ambiente de produção.

## 1. Verificações de Infraestrutura e Segurança
- [ ] **HTTPS e Certificado SSL:** O site carrega sob `https://` e o redirecionamento de `http://` para `https://` está funcionando.
- [ ] **Headers de Segurança:** Presença dos headers `X-Frame-Options`, `X-Content-Type-Options` e `Content-Security-Policy` nas respostas HTTP.
- [ ] **APP_DEBUG Desativado:** A variável `APP_DEBUG` no `.env` está setada como `false` e erros não expõem stack traces do Laravel.
- [ ] **Armazenamento Privado:** A pasta definida em `CAMPAIGN_STORAGE_PATH` está fora do diretório público (`public`) e o Nginx não serve arquivos diretamente dela.

## 2. Validação Operacional dos Módulos (Como Admin)
- [ ] **Login:** Acessar com a credencial padrão de administrador.
- [ ] **War Room:** O painel inicial carrega com o contador de dias para a eleição, estatísticas consolidadas e mural de avisos recentes.
- [ ] **Pesquisa Global (Ctrl+K):** A pesquisa exibe resultados agrupados por módulo.
- [ ] **Histórico Recente:** Os registros visitados aparecem no rodapé do War Room.
- [ ] **Favoritos:** Favoritar um registro e conferir se ele aparece na aba "Meus Favoritos".

## 3. Validação do CRM (Contatos e Lideranças)
- [ ] **Cadastro de Contato:** Cadastrar uma pessoa física e vincular a tags/tipos de relacionamento.
- [ ] **Duplicidade:** Tentar cadastrar outro contato com o mesmo telefone ou e-mail e verificar se o sistema bloqueia.
- [ ] **Histórico de Interação:** Adicionar uma interação de contato.
- [ ] **Importação de CSV:** Subir uma pré-visualização de CSV de contatos e confirmar importação.

## 4. Validação de Território, Demandas e Compromissos
- [ ] **Cadastro de Município/Bairro:** Criar uma nova cidade e bairro associado.
- [ ] **Demanda/Sugestão:** Criar uma demanda.
- [ ] **Aprovação de Compromisso:** Aprovar a demanda como compromisso de campanha oficial (Curadoria).
- [ ] **Exclusão com Vínculos:** Tentar excluir um bairro que possui uma demanda vinculada e garantir que o sistema bloqueia a exclusão física com a devida mensagem explicativa.

## 5. Validação Financeira e Contábil
- [ ] **Lançamento de Receita:** Criar um lançamento financeiro em rascunho.
- [ ] **Conferência:** Enviar para conferência e marcar como `Conferido` (o registro deve ficar bloqueado para edição normal).
- [ ] **Retificação:** Solicitar retificação em lançamento conferido, enviar novos dados e validar se os dados antigos foram arquivados no log de retificações.
- [ ] **Máscara Fiscal:** O CPF/CNPJ deve estar mascarado para usuários comuns e visível apenas para usuários com permissão `financeiro.visualizar_dados_fiscais`.
- [ ] **Upload e Download Protegidos:** Anexar um arquivo de comprovante e tentar baixá-lo (garantir que apenas usuários com `financeiro.baixar_documentos` consigam fazer o download).

## 6. Validação do Marketing e Materiais
- [ ] **Fila Editorial:** Criar uma pauta no banco de pautas e transformá-la em conteúdo.
- [ ] **Aprovação de Conteúdo:** Tentar aprovar conteúdo usando perfil sem permissão (deve falhar) e aprovar usando coordenador.
- [ ] **Controle de Roteiros:** Versionar um arquivo de roteiro de vídeo e conferir o histórico de versões.
- [ ] **Inventário de Materiais:** Dar entrada em santinhos no estoque e validar alertas de estoque mínimo e bloqueio de saldo negativo.
