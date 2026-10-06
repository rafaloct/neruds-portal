# GitHub Projects: configuração preparada

## Estado

O repositório privado, as 32 issues e os oito milestones foram criados. O quadro nativo ainda depende de autorização do GitHub CLI para o escopo `project`. A API retornou `INSUFFICIENT_SCOPES` ao consultar Projects; a sessão atual possui acesso ao repositório, mas esse acesso não cobre Projects.

Esta é uma limitação concreta de autenticação. Não exige recriar repositório, issues ou milestones. Não há processo automático aguardando a autorização.

## Quadro previsto

- Proprietário: `rafaloct`.
- Título: **NERUDS | Novo portal de pesquisa**.
- Visibilidade: privada.
- Repositório vinculado: `rafaloct/neruds-portal`.
- Itens: as 32 issues existentes, sem duplicá-las.
- Campos: Fila, Prioridade, Marco e Tamanho.
- Fila: Backlog, Pronto, Em execução, Em revisão, Bloqueado, Concluído.
- Milestones e relações de dependência permanecem na própria issue.

Visualizações sugeridas na interface: quadro agrupado por Fila; tabela por milestone; filtro das tarefas prontas; filtro de publicação com `human-gate`. O script prepara os campos e itens. Ajustes de visualização na interface não são alegados como concluídos.

## Concluir a autorização

Na máquina que fará a criação, usando a conta `rafaloct` já conectada:

```bash
gh auth refresh --hostname github.com --scopes project
```

Concluir a autorização apresentada pelo GitHub. Não colar tokens em chat, arquivos ou issues.

Depois que o escopo estiver disponível, dentro do repositório, usando Python 3.10 ou superior:

```bash
python scripts/bootstrap_project.py
python scripts/bootstrap_project.py --apply
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

O importador não cria visualizações, workflows ou automações do Project. Não há leitura final de cada campo para comprovar persistência: após uma aplicação autorizada, conferir a visibilidade, o vínculo, a quantidade de itens e os quatro campos no GitHub antes de registrar o quadro como concluído.

## Validação desta preparação

Em 2026-10-06, o dry-run local passou com as 32 issues. A revisão verificou os flags na ajuda do **GitHub CLI 2.102.0**, incluindo criação, edição de visibilidade, vínculo do repositório, campos de seleção e edição de itens por IDs. O manual confirma `--closed` e os limites de listagem; os testes oficiais dessa versão documentam as respostas JSON de campos e itens com `totalCount`.

Testes locais com o CLI substituído por respostas simuladas verificam o dry-run sem chamadas externas, as recusas antes de escrita e a precedência de `human-gate`. Essas verificações não executam `--apply` contra o GitHub e não demonstram uma importação remota concluída. A autenticação, as permissões efetivas e a persistência remota só poderão ser validadas quando o escopo estiver disponível e a aplicação for realizada.

## Fontes

- [GitHub: API de Projects e escopos necessários](https://docs.github.com/en/issues/planning-and-tracking-with-projects/automating-your-project/using-the-api-to-manage-projects)
- [GitHub CLI: project](https://cli.github.com/manual/gh_project)
- [GitHub CLI: project list](https://cli.github.com/manual/gh_project_list)
- [GitHub CLI: item-list](https://cli.github.com/manual/gh_project_item-list)
- [Código e testes oficiais de `field-list`, versão 2.102.0](https://github.com/cli/cli/blob/v2.102.0/pkg/cmd/project/field-list/field_list_test.go)
- [Código e testes oficiais de `item-list`, versão 2.102.0](https://github.com/cli/cli/blob/v2.102.0/pkg/cmd/project/item-list/item_list_test.go)
