# Coordenação de agentes e trabalho por issues

Este contrato organiza a execução do novo portal NERUDS. Leia também [AGENTS.md](../../AGENTS.md). A orientação explícita do usuário prevalece sobre convenções locais; aprovação já concedida para trabalho reversível não precisa ser repetida.

## Fonte operacional de verdade

Use [issues](https://github.com/rafaloct/neruds-portal/issues) para objetivos, escopo, dependências e aceite; [PRs](https://github.com/rafaloct/neruds-portal/pulls) para código, revisão e evidências; e [milestones](https://github.com/rafaloct/neruds-portal/milestones) para os oito agrupamentos do [roadmap](../planning/ROADMAP.md).

O planejamento inicial tem 32 issues. Chaves como `NERUDS-013` são identificadores do plano; o número real do GitHub e o link da issue devem ser usados quando disponíveis. Não deduza o número remoto pela ordem do documento.

GitHub Projects é uma visualização adicional **ainda pendente da permissão `project`**. Não afirmar que um quadro foi criado nem deixar de trabalhar numa issue atribuída somente por essa pendência. Documentos de planejamento não devem manter uma segunda fila divergente.

## Bootstrap e primeira execução

Criar e preencher o repositório novo foi autorizado. Os commits iniciais em `main` podem registrar código selecionado, documentação e preparação do ambiente, sem PR retroativo. A regra de branch/PR aplica-se à evolução após esse baseline.

As issues NERUDS-001, NERUDS-004 e NERUDS-005 podem registrar evidências entregues no bootstrap. Sua existência não encerra automaticamente essas issues: inventário, saneamento, reprodução, checks e limites ainda precisam corresponder aos critérios de cada uma.

A primeira execução futura deve conferir a origem e o estado do que foi preparado, incluindo o que ainda falta validar no ambiente e na restauração. Começar da evidência disponível, sem repetir uma investigação já comprovada nem presumir que um arquivo `compose.yaml` demonstra isolamento funcional.

## Preparação e estados

| Estado/rótulo | Significado | Condição de transição |
|---|---|---|
| `agent:backlog` | Escopo proposto, aguardando condições de execução. | Dependências e caminhos verificados; critérios e testes claros. |
| `agent:ready` | Tarefa preparada para atribuição. | Coordenação atribui a issue a uma execução autorizada. |
| `agent:working` | Agente responsável está executando o escopo declarado. | Mudança pronta e evidências entregues em PR. |
| `agent:review` | PR concreto aguarda revisão e decisão de integração. | Correções focais e checks pertinentes concluídos; merge somente com autorização. |
| Encerrada | Critérios cumpridos e resultado integrado conforme a decisão autorizada. | Não inicia outra issue automaticamente. |

Usar um estado operacional de agente por vez. Documentar bloqueios na issue com motivo, dependência e trabalho independente que ainda pode avançar. A issue NERUDS-032 tem `human-gate` obrigatório por alteração de produção; as demais não ganham gates humanos automáticos por interpretação genérica.

`agent:ready` não é um agendamento, um serviço de execução nem autorização para consumir a fila. Sem execução atribuída, nada roda por conta própria.

## Contrato de uma execução

1. Ler a issue, dependências e PRs abertos; confirmar que a tarefa está preparada ou registrar o que precisa ser resolvido dentro do escopo.
2. Assumir **uma issue** e registrar responsável, branch e caminhos. Trabalhar em checkout/worktree próprio quando necessário.
3. Verificar sobreposição de código e de configuração; combinar a ordem antes de editar áreas compartilhadas.
4. Implementar a mudança autorizada e reversível sem confirmações repetidas. Se faltar decisão essencial, preparar a escolha concreta e continuar partes independentes.
5. Executar testes focais e preparar **um PR** com critérios, evidências e limites; atualizar a issue para revisão.
6. Entregar o próximo passo explícito. Não fazer merge, publicar ou iniciar outra issue automaticamente.

## Sobreposição e configuração Drupal

| Área compartilhada | Como coordenar |
|---|---|
| `composer.json` / `composer.lock` | Uma resolução de dependências por vez; outro PR reavalia compatibilidade sobre o resultado integrado. |
| `config/sync` | Reservar entidades/arquivos concretos e o banco usado para exportação; não usar duas worktrees como prova de isolamento do mesmo banco. |
| Tipos/campos/displays/Views | Integrar alterações relacionadas atomicamente, incluindo dependências e testes do comportamento. |
| Papéis/permissões/workflows | Serializar mudanças que afetem a mesma política e revalidar acessos permitidos/negados após a integração. |
| Tema e componentes globais | Informar templates, bibliotecas e tokens tocados; dividir por componente real, não apenas por página. |
| Migração e taxonomias | Compartilhar mapa de IDs e equivalências; não executar duas migrações concorrentes sobre a mesma base. |

Quando houver conflito, ajustar a sequência ou o contrato de caminhos no GitHub. Não apagar mudanças de outra pessoa, não exportar ruído de configuração e não resolver divergência de schema escolhendo um lado sem compreender seus dados.

## Registro mínimo na issue

```text
Execução: data e responsável
Issue e branch: links/identificadores
Objetivo e caminhos assumidos: escopo concreto
Dependências e sobreposição: conferidas; reservas/ordem quando houver
Ambiente: cópia isolada identificada, sem credenciais
Entregue: comportamento e PR
Verificação: testes, resultado, commit e limitações
Pendências e próximo passo: revisão, decisão ou dependência real
```

O registro deve ser conciso e útil. Não incluir dumps, payloads pessoais, settings completos, tokens ou segredos. Links para evidências devem respeitar o acesso do repositório; dados privados não se tornam apropriados para Git por o repo ser privado.

## Revisão e autorizações

Um PR descreve o problema e o comportamento resultante para quem não acompanhou a conversa. Deve vincular sua issue e registrar mudanças de configuração, migração ou acesso que importem ao revisor. A [Definition of Done](../product/DEFINITION_OF_DONE.md) orienta a avaliação.

Merge exige autorização específica do usuário para o PR/candidato. Publicação exige autorização própria para candidato, ambiente, mudanças de banco/arquivos/roteamento, backup e procedimento. A autorização de merge não equivale à de produção, e a criação da issue de publicação não autoriza executá-la.

Preparar primeiro o resultado concreto para a decisão. Não pedir uma aprovação abstrata no começo de cada ajuste reversível, nem deixar o trabalho autorizado incompleto apenas para pedir confirmação.

## Retomada de trabalho

Ao retomar, ler issue, último PR, diff e evidências; conferir o estado real do checkout e do ambiente; registrar o ponto de continuação. Não assumir que uma sessão anterior foi interrompida antes de concluir nem repetir operações de dados sem verificar seus efeitos. A fila continua no GitHub, e uma nova tarefa precisa de atribuição em uma execução própria.
