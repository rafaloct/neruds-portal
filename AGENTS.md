# Contrato de trabalho dos agentes

Este arquivo vale para todo o repositório. **Instruções explícitas do usuário prevalecem sobre convenções e procedimentos locais.** Não invente bloqueios nem solicite de novo uma autorização já concedida para a ação concreta. Registre conflitos materiais de escopo e avance no trabalho independente autorizado.

## Objetivo e fronteira

Desenvolver o novo portal NERUDS em **Drupal nativo**, a partir da origem atual de produção capturada para uma cópia isolada. O usuário rejeitou Jaspr como base do novo portal. Não retome a homologação antiga como fonte visual ou substituto silencioso da produção.

O baseline inicial está desatualizado e ainda não é uma candidata pronta. Não declare atualização, restauração, migração, segurança ou prontidão sem evidência do ambiente e do commit efetivamente verificados.

Produção não pode ser alterada nesta fase. Não execute nela Drupal, Drush, Composer, migrações, importação de configuração, testes, cron, comandos de limpeza ou atualizações para facilitar o trabalho na cópia. Não use `/var/www/html` como diretório de desenvolvimento. Uma captura de origem expressamente autorizada tem o escopo específico daquela instrução; não autoriza manter a produção como dependência de desenvolvimento.

## Antes de editar

1. Leia [README.md](README.md), [ADR-001](docs/architecture/ADR-001-drupal-native.md), [coordenação](docs/operations/AGENT_COORDINATION.md) e a issue atribuída no GitHub.
2. Confira objetivo único, dependências, caminhos permitidos, critérios de aceite e testes focais. Se um nome de módulo, tema ou bundle for um placeholder, resolva-o pelo inventário antes de editar.
3. Confira `git status`, branches/PRs em andamento e reservas de caminhos registradas na issue. Não sobrescreva, reverta, descarte ou faça stash do trabalho de outra pessoa para obter uma árvore limpa.
4. Registre na issue o escopo assumido e eventuais arquivos de configuração compartilhados. Use checkout/worktree próprio quando houver trabalho simultâneo.

## Uma issue por execução

- Trabalhe em **uma issue, uma branch e um PR**. A branch identifica a chave da tarefa, por exemplo `feat/neruds-013-sistema-visual`.
- O bootstrap autorizado do repositório novo pode criar os commits iniciais em `main`. Não exija PR retroativo dessa importação. Essa exceção não se estende a mudanças posteriores.
- GitHub é a fonte operacional de verdade: issue para escopo/dependências, PR para diff/revisão/evidências e milestone para agrupamento. Documentos explicam decisões; não substituem o estado real das tarefas.
- `agent:ready` indica que a tarefa foi preparada, não autoriza um processo a executá-la sem atribuição nesta sessão. Não percorra a fila automaticamente nem inicie outra issue ao terminar.
- Se uma entrega de bootstrap já cobre parte da issue, confira a evidência e registre a cobertura. Não refaça trabalho concluído nem feche a issue por suposição.
- Preserve o limite da tarefa. Um defeito independente deve ser registrado para triagem; não transforme uma correção delimitada em refatoração geral.

## Isolamento e dados

- Não versione segredos, dumps, backups, uploads privados, cookies, sessões, tokens, chaves, credenciais, dados pessoais desnecessários ou configurações com valores reais. Não os exponha em logs, issues, screenshots ou artefatos de CI, mesmo com o repositório privado.
- Use contas sintéticas na cópia. Sanitização e proteção do material devem preceder o uso do banco restaurado em desenvolvimento.
- Bloqueie ou substitua e-mail, cron, jobs, webhooks, OAuth, MCP, IA e demais integrações reais conforme o inventário; a presença desses componentes na origem não autoriza acioná-los.
- Não permita que a aplicação em desenvolvimento use banco, arquivos, APIs ou outros serviços de produção. Evite também links de ações administrativas que levem alguém da cópia à produção.
- Não monte diretórios de produção em escrita, não reutilize seus volumes ou credenciais e não altere serviços globais da VPS.
- Não use Git ou GitHub com saída de rede a partir da produção. Commits, push, downloads de dependências e publicação de artefatos ocorrem no ambiente de trabalho isolado autorizado, sem transportar dados privados no histórico.
- Um bloqueio real de acesso ou ferramenta deve ser documentado. Não contorne uma fronteira de isolamento para concluir um teste.

## Configuração Drupal atômica

Mudanças relacionadas de configuração devem chegar completas no mesmo PR: tipos/campos, seus storages e displays, Views, permissões, workflows e dependências que o comportamento exigir. Não confunda atomicidade com exportar toda mudança existente no banco de trabalho.

1. Faça a alteração em uma cópia de trabalho que não receba edições concorrentes de configuração.
2. Exporte para o diretório `config/sync` confirmado pelo projeto e revise cada arquivo do diff. Exclua alterações alheias à issue e substituições específicas de ambiente.
3. Não altere UUIDs, IDs de entidades ou dependências para forçar um import sem investigar a causa.
4. Verifique a importação e o comportamento numa cópia apropriada, com backup quando a operação afetar seus dados. Registre alterações destrutivas de schema, perda de campos ou desinstalação de módulos de forma concreta antes de executá-las.
5. Se outro PR tocar as mesmas configurações, serialize o trabalho ou combine uma divisão explícita registrada. Uma segunda worktree não isola um banco compartilhado.

Não execute `config:import`, `updatedb`, `cache:rebuild` ou equivalente na origem de produção. Não versione `settings.php` com segredos como substituto de configuração exportável.

## Implementação e verificação

- Preserve autoria científica, URLs úteis, arquivos autorizados e relações do acervo. Não invente texto institucional, parcerias, métricas de impacto ou pessoas para completar layouts.
- Use APIs e convenções Drupal e o tema nativo conforme o ADR. Registre decisões que alterem o modelo ou a arquitetura antes de expandir a solução.
- Execute os testes focais da issue e os checks exigidos para a mudança. Não escreva testes que apenas espelham a implementação ou amplie a matriz sem uma falha, alteração ou risco concreto.
- Confira acessos permitidos e negados quando mudar permissões, arquivos, cache ou publicação. Dados de uma sessão não podem aparecer para outra por efeito de cache.
- Registre resultado, ambiente, commit e limites. Um teste que não pôde rodar permanece pendente; nunca o apresente como aprovado.
- Documentação deve acompanhar comportamento relevante. Consulte a [Definition of Done](docs/product/DEFINITION_OF_DONE.md).

## Entrega, merge e produção

O PR deve explicar o problema, a mudança, o comportamento resultante, evidências e limites; vincular a issue; e identificar os arquivos/configurações compartilhados. Depois, atualize o estado da issue e entregue o trabalho para revisão.

**Não faça merge nem habilite auto-merge sem autorização específica do usuário para o PR/candidato.** Checks aprovados ou ausência de comentários não são autorização. Não avance automaticamente para a próxima tarefa.

**Não publique nem altere produção sem autorização específica**, vinculada ao candidato e ao procedimento de banco, arquivos e roteamento. Prepare primeiro o pacote concreto, com backup, preservação do conteúdo recente, validação e reversão. A issue NERUDS-032 é o gate obrigatório de produção; sua criação não concede permissão de execução.

Mudanças reversíveis dentro de uma issue atribuída e já autorizada devem prosseguir sem pedir confirmação a cada arquivo ou comando. Se houver uma decisão indispensável do usuário, apresente a escolha concreta e a razão, mantendo o trabalho independente em andamento.
