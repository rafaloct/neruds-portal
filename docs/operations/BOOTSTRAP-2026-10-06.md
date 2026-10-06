# Bootstrap de 06/10/2026

## Entrega e origem

O repositório privado [rafaloct/neruds-portal](https://github.com/rafaloct/neruds-portal) foi criado a pedido do usuário para desenvolver uma atualização futura do portal. A origem é o Drupal atual que serve `neruds.org`; a homologação Jaspr anterior não foi usada como base.

A seleção de **287 arquivos originais**, com hashes individuais em [source-manifest.json](../architecture/source-manifest.json), contém `composer.json`, `composer.lock`, módulos e temas próprios. O commit de origem é `3b69c3d2286a11e3abf132cd0f89c8d8d452b4fa`. O arquivo compactado da seleção teve SHA-256 `c2047afe73731d2e38e033b2717c32638de6c680b1d860bfb7776ede593ce97b`, conferido após a transferência.

A origem observada usa Drupal CMS 1.2.8, core 11.2.12, PHP 8.4.14 e MariaDB 11.8.3. São valores do baseline herdado, não uma recomendação de versões seguras. A atualização e a resolução real de compatibilidade são o milestone M02.

## Cópia protegida

Foi obtido um snapshot consistente do banco por transação, sem bloqueio de tabelas, e restaurado em banco separado. O material bruto, os arquivos de conteúdo e o runtime completo permanecem privados no servidor; não entram no Git. A cópia sanitizada tem contas bloqueadas, sessões e tokens removidos, envios neutralizados e integrações desativadas. Nenhum Drupal ou servidor web da cópia foi iniciado nesta etapa.

O relatório específico do snapshot registra contagens, hashes, saneamento e controles de isolamento. A referência estrutural é documental: não substitui uma exportação completa e revisada de configuração para importação Drupal.

## Preparação do trabalho

- 8 milestones e 32 issues criados, com objetivo, escopo, dependências, caminhos, critérios de aceite e testes focais.
- Visão de produto, modelo de conteúdo, benchmark, Definition of Done e decisão arquitetural registrados.
- Receita local com banco e volumes próprios, rede interna e HTTP limitado ao loopback; sem mounts ou credenciais de produção.
- Settings próprios de desenvolvimento, variáveis de exemplo sem valores reais, bloqueio de arquivos sensíveis e guard de repositório.
- CI sem instalação de dependências, bootstrap Drupal ou deploy. Verifica caminhos, padrões selecionados de segredos, isolamento declarado e ferramentas de sintaxe/Composer disponíveis.
- Contrato por issue, branch e PR, com revisão e autorização específica para merge e publicação.

## Configuração do GitHub

O repositório foi confirmado como privado. Auto-merge está desativado, apenas squash merge está habilitado e branches integradas podem ser removidas automaticamente. O token padrão de Actions tem leitura e não pode aprovar PRs. Actions de terceiros não aprovadas estão bloqueadas; são permitidas actions mantidas pelo GitHub, com exigência de SHA completo. Estado conferido por leitura da API em [repository-settings.json](repository-settings.json).

## Verificação e limites

A verificação estática local passou. PHP e Composer não estão disponíveis no ambiente scratch, e suas verificações locais foram explicitamente puladas. A sintaxe dos settings e controles negativos do guard foram verificados durante a preparação. Nenhum build Docker, instalação de dependências, importação de configuração Drupal ou fluxo funcional da aplicação foi validado neste bootstrap.

O saneamento e os checks de importação não equivalem a uma auditoria completa de segurança ou a uma candidata pronta para produção. O design atual foi preservado como referência de origem; o novo design institucional ainda será construído no milestone M04.

GitHub Projects depende do escopo OAuth `project`, ausente na autenticação disponível. O [procedimento e importador](../planning/PROJECT.md) estão preparados, mas o quadro não foi criado.

A primeira tarefa preparada é [NERUDS-001 / issue #1](https://github.com/rafaloct/neruds-portal/issues/1), para conferir este inventário e os limites já registrados, sem repetir a captura de origem. O estado operacional continua nas issues; a existência dos arquivos não encerra seus critérios automaticamente.
