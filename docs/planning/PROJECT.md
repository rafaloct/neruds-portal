# GitHub Projects: configuração e manutenção

## Estado verificado em 06/10/2026

O [Project NERUDS #2](https://github.com/users/rafaloct/projects/2) está criado, privado, aberto e vinculado somente ao repositório privado `rafaloct/neruds-portal`. As 32 issues existentes foram adicionadas sem duplicação. O usuário concluiu a autorização OAuth `project`, e o importador terminou com exit 0.

A leitura independente posterior confirmou as 32 issues e **128 de 128 valores corretos** nos quatro campos, sem itens faltantes, extras, duplicados ou arquivados. A [evidência dos itens](project-verification-2026-10-06.json) registra o estado às 21:25:01 UTC. As [duas visualizações também foram relidas](project-views-2026-10-06.json), às 21:27:24 UTC. Essas evidências registram o estado nesses horários; mudanças posteriores precisam de nova conferência pertinente.

## Quadro configurado

- Proprietário: `rafaloct`.
- Título: **NERUDS | Novo portal de pesquisa**.
- Número: **2**; ID: `PVT_kwHOAJ_FRc4Bl-59`.
- Visibilidade: privada.
- Repositório vinculado: `rafaloct/neruds-portal`.
- Itens: as 32 issues existentes, sem duplicá-las.
- Campos: Fila, Prioridade, Marco e Tamanho.
- Fila: Backlog, Pronto, Em execução, Em revisão, Bloqueado, Concluído.
- Milestones e relações de dependência permanecem na própria issue.

| Visualização | Configuração verificada |
| --- | --- |
| [Planejamento](https://github.com/users/rafaloct/projects/2/views/1) | Tabela com Title, Fila, Prioridade, Marco, Tamanho, Milestone, Assignees, Labels e Linked pull requests. Sem agrupamento; os marcos aparecem em colunas. |
| [Fluxo de trabalho](https://github.com/users/rafaloct/projects/2/views/2) | Board com colunas por Fila. Cartões mostram título, responsáveis, labels, prioridade, marco e tamanho. |

Ambas usam o filtro `is:issue`, que permite acompanhar também issues encerradas. A distribuição inicial é **30 Backlog, #1 Pronto e #32 Bloqueado** por `human-gate`. Isso descreve a preparação da fila, não uma autorização para executar tarefas ou publicar.

A tabela padrão foi atualizada por `updateProjectV2View` no GraphQL. O Board foi criado pela API REST pública, `POST /users/rafaloct/projectsV2/2/views`, com `vertical_group_by` apontando para Fila. Nessa rota foi validado o login `rafaloct` como identificador do usuário. O GitHub retornou os campos do Board em sua própria ordem; a evidência conserva a ordem observada. O importador prepara campos e itens; essas visualizações foram configuradas separadamente.

## Manutenção e reaplicação controlada

A autorização desta criação está concluída. Caso uma sessão futura perca o escopo necessário, renovar a autorização na máquina de trabalho usando a conta `rafaloct` já conectada:

```bash
gh auth refresh --hostname github.com --scopes project
```

Nesse caso, concluir a autorização apresentada pelo GitHub. Não colar tokens em chat, arquivos ou issues. A existência do quadro não exige renovar a autenticação a cada leitura.

Antes de qualquer reaplicação, reconciliar as decisões atuais das issues com o manifesto, conforme a seção seguinte. Dentro do repositório, usando Python 3.10 ou superior e o número do quadro existente:

```bash
python scripts/bootstrap_project.py --project-number 2
python scripts/bootstrap_project.py --apply --project-number 2
```

A primeira chamada valida o manifesto inteiro e mostra o plano local. Não chama `gh`, não consulta a rede e não grava estado. A segunda faz consultas de preparação antes de qualquer escrita: identidade e privacidade do repositório, quadro selecionado, campos existentes, itens e identidade de todas as issues. URLs devem corresponder exatamente ao repositório e ao número registrado; chaves e números repetidos ou opções inválidas interrompem o fluxo.

A aplicação reutiliza um quadro aberto com o título previsto ou cria um novo, define a visibilidade privada, vincula o repositório e adiciona as issues existentes. Ela não altera código, milestones, estado ou labels das issues, merge ou produção. O script fixa o host `github.com`, desabilita prompts interativos do CLI e nunca executa autenticação. Uma falha de escopo exige concluir o OAuth separadamente; erros de manifesto ou de campos não são tratados como falhas de OAuth.

Se houver mais de um quadro com o mesmo título, selecionar explicitamente o número conferido na interface:

```bash
python scripts/bootstrap_project.py --project-number NUMERO
python scripts/bootstrap_project.py --apply --project-number NUMERO
```

Substituir `NUMERO` pelo inteiro do quadro de `rafaloct`. O título também precisa corresponder. Quadros fechados são detectados e rejeitados; o script não os reabre nem cria outro silenciosamente. As listas são limitadas a 100 resultados e o fluxo para quando detecta uma resposta incompleta. A seleção por número resolve a descoberta de quadros; listas de campos ou itens maiores exigem revisão do importador antes de aplicá-lo.

## Fontes dos campos e repetição da execução

| Campo | Valor usado na aplicação |
| --- | --- |
| Fila | Estado e labels atuais da issue no GitHub, consultados antes da escrita. |
| Prioridade | `priority` no `docs/planning/backlog.json`. |
| Marco | Chave `milestone` no mesmo manifesto; não altera o milestone nativo da issue. |
| Tamanho | `relative_size` no mesmo manifesto. |

Uma issue fechada recebe **Concluído**. Em uma issue aberta, `human-gate` recebe **Bloqueado**, mesmo que exista uma label de agente. Nas demais, a precedência é `agent:working`, `agent:review`, `agent:ready` e, sem essas labels, **Backlog**. Isso projeta as labels existentes; não calcula dependências, não aprova gates e não inicia agentes.

O script é um importador para a preparação inicial, sem sincronização contínua. Uma repetição sobrescreve esses quatro campos do Project com os valores acima. Como o GitHub é a fonte de verdade operacional, reconciliar o manifesto com decisões posteriores registradas nas issues antes de uma nova aplicação. Campos existentes incompatíveis ou opções duplicadas interrompem o fluxo para revisão; nenhum campo é apagado para contornar o problema.

As operações remotas são sequenciais e não formam uma transação. Falhas durante a escrita podem deixar um quadro ou parte dos itens configurados. O resultado local só é gravado ao concluir todas as operações, em `.runtime/project-state.json`, fora do Git. Se a execução falhar, conferir o quadro remoto e seu número antes de repetir. Uma nova execução reutiliza o quadro e os itens pelas URLs; não há rollback automático nem bloqueio contra duas execuções simultâneas. Executar apenas uma aplicação por vez e evitar mudanças concorrentes de labels ou campos durante esse procedimento.

O importador não cria visualizações, workflows ou automações do Project. Ele não faz leitura final de cada campo para comprovar persistência: após uma aplicação autorizada, conferir a visibilidade, o vínculo, a quantidade de itens e os quatro campos no GitHub antes de registrar a reaplicação como concluída. Nesta criação, essa conferência foi realizada separadamente e está anexada acima.

## Validação da preparação e da aplicação

Em 2026-10-06, o dry-run local passou com as 32 issues. A revisão verificou os flags na ajuda do **GitHub CLI 2.102.0**, incluindo criação, edição de visibilidade, vínculo do repositório, campos de seleção e edição de itens por IDs. O manual confirma `--closed` e os limites de listagem; os testes oficiais dessa versão documentam as respostas JSON de campos e itens com `totalCount`.

As 11 verificações focais locais com o CLI substituído por respostas simuladas cobriram o dry-run sem chamadas externas, as recusas antes de escrita e a precedência de `human-gate`. Esses testes verificaram a preparação, sem substituir a aplicação remota.

Após a autorização, a execução real de `--apply` concluiu as operações e registrou 32 itens em `.runtime/project-state.json`, fora do Git. A verificação independente consultou o GraphQL somente em leitura e comparou todas as issues, os IDs dos campos/opções e os 128 valores. Fila foi comparada com o estado e as labels atuais; os outros três campos, com o manifesto cujo SHA-256 consta na evidência. As duas visualizações foram conferidas em leitura separada, incluindo layouts, filtro, campos visíveis e a coluna Fila do Board.

Esta entrega conclui a configuração do Project. Não conclui os critérios de CI da [issue #5](https://github.com/rafaloct/neruds-portal/issues/5), cujos bloqueios de conta estão em [repository-settings.json](../operations/repository-settings.json). O Project não inicia agentes, não integra PRs e não publica o portal por conta própria.

## Fontes

- [GitHub: API de Projects e escopos necessários](https://docs.github.com/en/issues/planning-and-tracking-with-projects/automating-your-project/using-the-api-to-manage-projects)
- [GitHub REST: criação e configuração de visualizações](https://docs.github.com/en/rest/projects/views#create-a-view-for-a-user-owned-project)
- [GitHub GraphQL: atualização de visualização](https://docs.github.com/en/graphql/reference/projects#updateprojectv2viewinput)
- [GitHub CLI: project](https://cli.github.com/manual/gh_project)
- [GitHub CLI: project list](https://cli.github.com/manual/gh_project_list)
- [GitHub CLI: item-list](https://cli.github.com/manual/gh_project_item-list)
- [Código e testes oficiais de `field-list`, versão 2.102.0](https://github.com/cli/cli/blob/v2.102.0/pkg/cmd/project/field-list/field_list_test.go)
- [Código e testes oficiais de `item-list`, versão 2.102.0](https://github.com/cli/cli/blob/v2.102.0/pkg/cmd/project/item-list/item_list_test.go)
