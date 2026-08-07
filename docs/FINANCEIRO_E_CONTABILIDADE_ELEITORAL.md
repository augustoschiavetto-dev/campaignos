# Finanças e Contabilidade Eleitoral — Diretrizes do CampaignOS

Este documento estabelece o escopo e as diretrizes regulatórias do CampaignOS em relação à gestão financeira e contábil da campanha.

## 1. Escopo e Limitação do Sistema
O CampaignOS é uma ferramenta de **controle e gestão administrativa interna**. Ele foi desenhado para apoiar a equipe de campanha e voluntários na organização operacional do fluxo de caixa diário.

> [!IMPORTANT]
> O CampaignOS **NÃO** é um sistema oficial de prestação de contas eleitorais.

*   **Sem Validade Legal:** Os protocolos internos (`FIN-2026-XXXXXX`) emitidos pelo sistema são recibos de controle administrativo interno de tesouraria de campanha e não possuem validade jurídica perante a Justiça Eleitoral.
*   **Emissão Oficial:** A emissão oficial de recibos eleitorais válidos deve ser executada exclusivamente no sistema oficial da Justiça Eleitoral (SPCE/Conta+JE ou similar vigente).
*   **Responsabilidade Contábil:** Toda classificação de receita, documentação anexada e admissibilidade jurídica de recursos deve ser revisada e validada pelo contador credenciado da campanha.

## 2. Fluxo de Conformidade do Módulo Financeiro
Para garantir a fidelidade dos dados de controle interno com as regras de prestação de contas oficiais, o módulo segue as etapas:

1.  **Rascunho (`rascunho`):** Permite correções e lançamentos preliminares das entradas e saídas.
2.  **Pendente de Conferência (`pendente_conferencia`):** Período de validação interna da tesouraria do comitê de campanha.
3.  **Conferido (`conferido`):** Estado de bloqueio contábil. Dados críticos do lançamento não podem ser alterados silenciosamente.
4.  **Retificações (`retificacao_solicitada` & `retificado`):** Mudanças em lançamentos conferidos necessitam de justificativa expressa e preservam em histórico contábil os valores originais, usuário responsável e data.

---
*Nota: A legislação eleitoral e as resoluções do TSE sobre arrecadação e despesas de campanha podem ser atualizadas a cada pleito. Consulte sempre a assessoria jurídica e a contabilidade da campanha antes de tomar decisões contábeis.*
