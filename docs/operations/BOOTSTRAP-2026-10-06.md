# Bootstrap de 06/10/2026

## Entrega e origem

O repositório privado [rafaloct/neruds-portal](https://github.com/rafaloct/neruds-portal) foi criado a pedido do usuário para desenvolver uma atualização futura do portal. A origem é o Drupal atual que serve `neruds.org`; a homologação Jaspr anterior não foi usada como base.

A seleção de **287 arquivos originais**, com hashes individuais em [source-manifest.json](../architecture/source-manifest.json), contém `composer.json`, `composer.lock`, módulos e temas próprios. O commit de origem é `3b69c3d2286a11e3abf132cd0f89c8d8d452b4fa`. O arquivo compactado da seleção teve SHA-256 `c2047afe73731d2e38e033b2717c32638de6c680b1d860bfb7776ede593ce97b`, conferido após a transferência.

A origem observada usa Drupal CMS 1.2.8, core 11.2.12, PHP 8.4.14 e MariaDB 11.8.3. São valores do baseline herdado, não uma recomendação de versões seguras. A atualização e a resolução real de compatibilidade são o milestone M02.

## Cópia protegida

Foi obtido um snapshot consistente do banco por transação, sem bloqueio de tabelas, e restaurado em banco separado. O material bruto, os arquivos de conteúdo e o runtime completo permanecem privados no servidor; não entram no Git. A cópia sanitizada tem contas bloqueadas, sessões e tokens removidos, envios neutralizados e integrações desativadas. Nenhum Drupal ou servidor web da cópia foi iniciado nesta etapa.

O [relatório específico do snapshot](SNAPSHOT-2026-10-06.md) registra contagens, hashes, saneamento e controles de isolamento. A referência estrutural é documental: não substitui uma exportação completa e revisada de configuração para importação Drupal.

## Preparação do trabalho

- 8 milestones e 32 issues criados, com objetivo, escopo, dependências, caminhos, critérios de aceite e testes focais. As 78 dependências nativas foram criadas e relidas, sem faltantes ou extras; [evidência](../planning/dependencies-state.json).
- Visão de produto, modelo de conteúdo, benchmark, Definition of Done e decisão arquitetural registrados.
- Receita local com banco e volumes próprios, rede interna e HTTP limitado ao loopback; sem mounts ou credenciais de produção.
- Settings próprios de desenvolvimento, variáveis de exemplo sem valores reais, bloqueio de arquivos sensíveis e guard de repositório.
- CI sem instalação de dependências, bootstrap Drupal ou deploy. Verifica caminhos, padrões selecionados de segredos, isolamento declarado e ferramentas de sintaxe/Composer disponíveis.
- Contrato por issue, branch e PR, com revisão e autorização específica para merge e publicação.

## Configuração do GitHub

O repositório foi confirmado como privado. Auto-merge está desativado, apenas squash merge está habilitado e branches integradas podem ser removidas automaticamente. O token padrão de Actions tem leitura e não pode aprovar PRs. Actions de terceiros não aprovadas estão bloqueadas; são permitidas actions mantidas pelo GitHub, com exigência de SHA completo. Estado conferido por leitura da API em [repository-settings.json](repository-settings.json).

A política proposta de branch está em [branch-protection.request.json](branch-protection.request.json). A API de proteção retornou HTTP 403 exigindo GitHub Pro para este repositório privado; portanto PR obrigatório, checks obrigatórios, restrições de force push e de exclusão de branch **não foram ativados pelo servidor**. A autorização específica continua como regra de trabalho, sem alegação de bloqueio técnico.

A [primeira execução de CI](https://github.com/rafaloct/neruds-portal/actions/runs/37515555374), no commit `674f3752db2f2a268a20498ef32b5052c25738a7`, terminou com falha antes de iniciar qualquer step. A anotação do GitHub atribui o bloqueio a pagamentos recentes ou limite de gastos da conta. Isso não é um teste de código reprovado, e tampouco uma CI aprovada. Nenhuma configuração financeira foi alterada.

## Verificação e limites

A verificação estática local passou no scratch e no Windows; no Windows foram conferidos 892 arquivos candidatos do Git e 703 arquivos de texto. Uma falha do guard ao usar o encoding padrão cp1252 no Windows foi corrigida exigindo UTF-8 nos arquivos de configuração. O hash do composer.lock permaneceu inalterado e o JSON foi validado em UTF-8. PHP e Composer não estão disponíveis no scratch nem no desktop Windows. Como alternativa de verificação, os 41 arquivos PHP/inc/module/install/theme da cópia privada, com hashes iguais aos fontes versionados, passaram em `php -n -l` usando PHP 8.4.14. Composer 2.8.12 validou composer.json e composer.lock com exit 0, sem plugins, scripts ou rede; houve apenas aviso sobre versões exatas. [Evidência da análise estática](STATIC-VALIDATION-2026-10-06.json). A sintaxe dos settings adicionais e os controles negativos do guard foram verificados durante a preparação. Na preparação inicial, o importador de Projects passou no dry-run em Linux e Windows e em 11 verificações focais simuladas, sem criar um Project ou validar acesso remoto. Nenhum build Docker, instalação de dependências, importação de configuração Drupal ou fluxo funcional da aplicação foi validado neste bootstrap.

O saneamento e os checks de importação não equivalem a uma auditoria completa de segurança ou a uma candidata pronta para produção. O design atual foi preservado como referência de origem; o novo design institucional ainda será construído no milestone M04.

### Conclusão do GitHub Projects

A criação do quadro ficou inicialmente pendente do escopo OAuth `project`. Após a autorização concluída pelo usuário em 06/10/2026, o importador terminou com exit 0 e criou o [Project NERUDS #2](https://github.com/users/rafaloct/projects/2), privado e vinculado somente a este repositório. A leitura independente encerrada às 21:25:01 UTC confirmou 32 issues, sem duplicatas ou itens extras/arquivados, e 128 de 128 valores corretos em Fila, Prioridade, Marco e Tamanho. A distribuição inicial é 30 Backlog, #1 Pronto e #32 Bloqueado por `human-gate`; [evidência dos itens](../planning/project-verification-2026-10-06.json).

As visualizações [Planejamento](https://github.com/users/rafaloct/projects/2/views/1), em tabela, e [Fluxo de trabalho](https://github.com/users/rafaloct/projects/2/views/2), em quadro com colunas por Fila, foram configuradas pelas APIs públicas e relidas às 21:27:24 UTC; [evidência das visualizações](../planning/project-views-2026-10-06.json). O importador não criou essas visualizações nem faz sincronização contínua. O [procedimento de manutenção](../planning/PROJECT.md) explica suas limitações.

Esta conclusão resolve a pendência de Projects. A issue #5 permanece aberta porque a CI hospedada e a proteção obrigatória de branch ainda têm os bloqueios de conta registrados acima. Não houve mudança financeira, merge, execução da aplicação ou publicação nesta continuação.

A primeira tarefa preparada é [NERUDS-001 / issue #1](https://github.com/rafaloct/neruds-portal/issues/1), para conferir este inventário e os limites já registrados, sem repetir a captura de origem. O estado operacional continua nas issues; a existência dos arquivos não encerra seus critérios automaticamente.
