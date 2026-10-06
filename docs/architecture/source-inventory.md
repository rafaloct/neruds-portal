# Inventário de origem e critérios de importação do Portal NERUDS

Revisão da [NERUDS-001, issue #1](https://github.com/rafaloct/neruds-portal/issues/1), em **06/10/2026**. A verificação dos fontes terminou às **21:53:18 UTC, 18:53:18 em Brasília**.

**Resultado: os 287 arquivos da seleção original, totalizando 2.418.471 bytes, coincidem integralmente com o manifesto histórico, os blobs da base e os blobs do commit de importação.** Este inventário consolida proveniência, componentes, exclusões e limites para as próximas tarefas. Não certifica aplicação inicializada, atualização, segurança integral ou prontidão de publicação.

A [evidência desta revisão](source-inventory-review-2026-10-06.json) registra método, contagens, a amostra detalhada de 32 arquivos, os 295 pacotes do lock, componentes próprios, limites e correspondência dos critérios de aceite. Ela é um registro derivado; o [manifesto histórico](source-manifest.json) e o [lockfile](../../composer.lock) continuam sendo as referências de seus respectivos conjuntos.

## 1. Origem, identidade e defasagem

| Referência | Identificação |
| --- | --- |
| Origem registrada na captura | Drupal que servia `https://neruds.org`, raiz `/var/www/html`, DocumentRoot `web/` |
| Commit da seleção original | `3b69c3d2286a11e3abf132cd0f89c8d8d452b4fa` |
| Base desta execução | `e600761ad0d5a133638cad66a093ca8087f09ae1` |
| Manifesto de proveniência | [source-manifest.json](source-manifest.json) |
| SHA-256 do manifesto conferido | `900ebe34e4d6bbcbaac1e1d84b00c2690d4180c77b63699428eff1328a991d5d` |
| Origem e versões resumidas | [source-baseline.json](source-baseline.json) |
| Captura, restauração e saneamento | [Snapshot](../operations/SNAPSHOT-2026-10-06.md) e [evidência histórica](../operations/SNAPSHOT-2026-10-06.evidence.json) |

O registro histórico liga o domínio ao vhost OpenLiteSpeed e ao DocumentRoot. Esta revisão não voltou à produção nem refez essa inspeção. A afirmação `source_is_current_production` no baseline refere-se à origem identificada **no momento da captura**. A homologação Jaspr antiga não forneceu fontes, banco ou referência visual.

### Momentos distintos da captura

Todos os horários abaixo são de 06/10/2026, em UTC. Os valores completos, com frações de segundo, estão na evidência JSON.

| Evento | Horário |
| --- | --- |
| Seleção dos 287 fontes e arquivos Composer | 18:23:59 |
| Início do snapshot de banco | 18:31:43 |
| Fim do dump bruto | 18:31:45 |
| Fim da primeira cópia runtime | 18:33:44 |
| Restauração no banco próprio | 18:33:51 |
| Geração da referência estrutural sanitizada | 18:41:32 |
| Verificação dos controles de saneamento | 18:43:34 |
| Refinamento dos filtros e novo hash runtime | 18:46:57 |
| Exportação sanitizada | 18:50:16 |
| Conferência dos fontes nesta revisão | 21:53:18 |

A transação consistente do dump comprova uma propriedade daquela coleta de banco. Ela não demonstra captura atômica entre banco, código e uploads. Não foi encontrada divergência na seleção de fontes conferida, mas mudanças posteriores às respectivas capturas não foram avaliadas. Uma futura publicação precisará reconciliar conteúdo e arquivos produzidos desde a captura, conforme as issues de migração e lançamento.

## 2. Inventário consolidado

### Distribuição, core, dependências e ferramentas

| Objeto | Valor do baseline | Alcance da evidência |
| --- | --- | --- |
| Projeto raiz | `drupal/cms 1.2.8` | Metadata em `composer.json`; o projeto raiz não integra a contagem do lock |
| Core | `drupal/core 11.2.12` | Mesma versão em core-recommended, core-composer-scaffold e core-project-message |
| Outros componentes core | core-recipe-unpack e core-vendor-hardening `11.2.5` | Versões próprias desses pacotes no lock, preservadas sem atualização |
| PHP observado | `8.4.14` | Captura e análise estática anterior; executável indisponível neste desktop |
| MariaDB observado | `11.8.3` | Captura; a restauração registrou `11.8.3-MariaDB-ubu2404` |
| Drush | `13.7.3` | Dependência resolvida; nenhum comando Drush executado nesta tarefa |
| Composer CLI anterior | `2.8.12` | Ferramenta usada na análise estática histórica |
| Composer como biblioteca | `composer/composer 2.9.8` | Pacote do lock; não comprova versão do executável |
| Pacotes resolvidos | **295**, sem `packages-dev` | Leitura do lockfile preservado |
| Pacotes Drupal por tipo | **94** módulos, **4** temas, **27** receitas | Inventário de pacotes; não é contagem de extensões habilitadas |
| Demais tipos do lock | 153 bibliotecas, 6 plugins Composer, 5 drupal-library, 3 metapacotes, 1 drupal-core, 1 class e 1 symfony-bridge | A soma de todos os tipos é 295 |

Os quatro temas contrib do lock são `drupal/bootstrap5 4.0.6`, `drupal/drupal_cms_olivero 1.2.8`, `drupal/easy_email_theme 1.1.0` e `drupal/gin 5.0.5`. A listagem completa de nome, versão e tipo dos pacotes está em `composer.packages` na evidência JSON.

**Código presente, pacote resolvido e módulo habilitado são estados diferentes.** A referência versionada não contém `core.extension`; a habilitação completa dos módulos permanece desconhecida nesta revisão. A documentação oficial do [Drupal sobre instalação de módulos](https://www.drupal.org/docs/extending-drupal/installing-modules) também distingue download de instalação e observa que um projeto pode conter vários submódulos. O [Composer documenta o lockfile](https://getcomposer.org/doc/01-basic-usage.md) como registro das versões resolvidas.

Nenhum desses números define versões de destino seguras. A escolha de versões e a compatibilidade real pertencem ao M02, issues #6 a #9.

### Nove módulos próprios

Cada componente foi identificado pelo respectivo `web/modules/custom/<nome>/<nome>.info.yml`. A restrição de core é uma declaração herdada, não um teste de compatibilidade.

| Nome de máquina | Arquivos | Versão declarada | Core declarado |
| --- | ---: | --- | --- |
| `neruds_analytics` | 6 | 1.0.0 | `^11` |
| `neruds_blocks` | 8 | 1.0.0 | `^11 \|\| ^12` |
| `neruds_extensionista_guard` | 6 | não declarada | `^10 \|\| ^11` |
| `neruds_google_integration` | 46 | não declarada | `^11` |
| `neruds_gui_views` | 11 | 1.0.0 | `^11 \|\| ^12` |
| `neruds_mcp` | 10 | 1.0.0 | `^11 \|\| ^12` |
| `neruds_notifications` | 7 | 1.0.0 | `^11` |
| `neruds_orcid` | 4 | 1.0.0 | `^11` |
| `neruds_related_content` | 4 | 1.0.0 | `^11` |
| **Total** | **102** | | |

### Três temas próprios e ativos compartilhados

Cada tema tem um `.info.yml` próprio sob `web/themes/custom/<nome>/`.

| Nome de máquina | Arquivos | Versão declarada | Tema base declarado |
| --- | ---: | --- | --- |
| `bico_papagaio` | 28 | 1.0.0 | `olivero` |
| `neruds_gui` | 81 | 2.0.0 | `olivero` |
| `neruds_theme` | 48 | 1.0.0 | `olivero` |
| **Total dos temas** | **157** | | |

Os três temas declaram `^11 || ^12` para core; essa metadata não demonstra compatibilidade com essas séries. A [referência de seleção de tema](structural-reference/system.theme.reference.json) registra historicamente **`default=neruds_gui` e `admin=claro`**. Não houve renderização ou confirmação funcional dessa seleção nesta execução.

Existem ainda **26 arquivos compartilhados** em `web/themes/custom/css/` (4), `js/` (3) e `templates/` (19). Esses três diretórios não são temas adicionais. Sua utilização efetiva precisa ser conferida na tarefa visual pertinente.

A soma concilia a importação: **2 Composer + 102 módulos + 157 temas + 26 ativos compartilhados = 287 arquivos**.

### Runtime e dados preservados

Os números seguintes vêm exclusivamente da evidência histórica de snapshot. A disponibilidade e integridade atual dos artefatos privados não foram revalidadas nesta execução.

| Objeto privado | Registro histórico |
| --- | --- |
| Runtime | 98.284 arquivos; 641.340.368 bytes; nenhum symlink |
| Uploads dentro da árvore privada | 391 arquivos; 54.319.491 bytes; sem exportar listagem de nomes |
| Banco restaurado | 428 tabelas InnoDB em `neruds_portal_copy` |
| Container e volume próprios | `neruds_portal_snapshot_db_20261006` e `neruds_portal_snapshot_db_20261006_data` |
| Isolamento do banco | Rede Docker `none`, nenhuma porta publicada e socket privado |
| Saneamento de contas | 3 contas tratadas; nenhuma ativa, com e-mail real ou hash válido de senha remanescente |
| Dados operacionais | 72 tabelas purgadas, 36.085 linhas removidas e 2.295 entradas key-value operacionais removidas |
| Configuração | 68 registros ajustados; mailer de teste, cron automático zero, JSON:API em leitura e manutenção ativa |
| Aplicação no registro histórico | Sem bootstrap Drupal e sem servidor web iniciado |

O material privado continua sob a raiz de captura documentada no snapshot. O dump sanitizado preserva conteúdo editorial público e identidades publicadas de pesquisadores; não é um conjunto de dados integralmente anonimizado para divulgação.

### Referência estrutural

A [referência estrutural](structural-reference/README.md) contém 570 descrições: 14 tipos de conteúdo, 38 vocabulários, 146 storages, 210 campos vinculados, 26 displays de formulário, 85 displays visuais, 41 Views, 7 papéis e 3 registros de tema.

Esses arquivos são documentação com omissões intencionais. **Não constituem exportação importável**, não incluem `core.extension` e não comprovam um `config/sync` pronto. Não se deve executar importação Drupal apontando para essa pasta.

Duas descrições históricas de configurações de tema conservam referências públicas de mídia em `data.logo.path`. Os valores e nomes não são reproduzidos nesta revisão. A ausência dos arquivos de upload no Git não permite afirmar que todas as referências a mídia foram removidas das descrições. O tratamento dessas referências pertence à revisão de conteúdo e saneamento pertinente, preservando a evidência histórica.

## 3. Inclusões, exclusões e destino de importação

| Categoria | Destino e condição |
| --- | --- |
| `composer.json` e `composer.lock` | Permanecem versionados e inalterados como fonte das dependências herdadas |
| 102 arquivos de módulos próprios | Permanecem no baseline; revisão de segurança e compatibilidade na issue #9 |
| 183 arquivos de temas e ativos compartilhados | Permanecem no baseline; inventário não aprova design, uso de todos os ativos ou compatibilidade |
| Dependências geradas, vendor, core, contrib e receitas do runtime | Preservação privada ou reprodução futura a partir do lock revisado; não copiar o runtime completo para o Git |
| Dump bruto, dump sanitizado, banco, uploads e conteúdo de contas | Material privado; nunca anexar a commits, issues, CI ou PRs |
| Settings de produção, dotenv, auth.json, chaves, tokens e credenciais | Excluídos da importação e vedados como dependência do ambiente de desenvolvimento |
| Backups de código | 11 caminhos excluídos enumerados no manifesto histórico e em `manifest.excluded` na evidência |
| Referência estrutural | Documentação para análise, sem importação direta de configuração |
| Settings e infraestrutura de desenvolvimento já versionados | Preparação própria posterior à seleção original; não são settings de produção nem prova de aplicação funcional |
| Homologação Jaspr e aliases externos do vhost | Fora da origem técnica e visual deste projeto |

As 11 exclusões explícitas da seleção correspondem a **10 arquivos sob o backup de `neruds_extensionista_guard`** e **1 backup de `PortalDataService.php`**. O manifesto mantém a enumeração histórica; nenhum arquivo foi recuperado desses backups nesta revisão.

Os filtros históricos da cópia runtime excluem `settings*.php`, `.env*`, `auth.json`, `.git`, dumps SQL, `*.dump`, `*.bak*`, nomes de backup, logs e diretórios `/web/sites/*/files/config_*/`. Esse último filtro é restrito aos arquivos públicos: o bootstrap já corrigiu a exclusão ampla que removia código legítimo chamado `config_*`. A seleção Git e a cópia runtime têm finalidades e conjuntos diferentes.

A [política de ignore](../../.gitignore) e o [guard existente](../../scripts/check_repository.py) complementam a triagem. Seus padrões são limitados; um resultado sem achados não é auditoria integral de segurança ou atestado de que todas as dependências herdadas são seguras.

## 4. Integrações que devem continuar isoladas

A tabela registra superfícies observadas no código ou no lock. Não presume que todas estejam habilitadas. Os controles do snapshot são históricos; antes de iniciar uma futura aplicação, a issue responsável precisa confirmar o comportamento na área de desenvolvimento própria.

| Família | Evidência de origem | Condição para as tarefas futuras |
| --- | --- | --- |
| E-mail imediato e por fila | [neruds_notifications.module](../../web/modules/custom/neruds_notifications/neruds_notifications.module); mailsystem, easy_email, simplenews e symfony_mailer_lite no lock | O hook de inserção de comentário envia e-mail diretamente. Cron desligado, sozinho, não cobre esse caminho. Usar destinatários sintéticos, coletor e bloqueio de transportes reais |
| Cron, filas, agendamentos e automação | Snapshot; pacotes scheduler, ECA, automatic_updates e project_browser | Não consumir filas nem executar automações herdadas por existirem no baseline. Manter tarefas, downloads e destinos reais neutralizados |
| Google, OAuth, Drive, Calendar, buscas e BigQuery | [PortalDataService.php](../../web/modules/custom/neruds_google_integration/src/Service/PortalDataService.php), [AuditService.php](../../web/modules/custom/neruds_google_integration/src/Service/AuditService.php) e pacotes OAuth | Não reutilizar clientes, credenciais, contas ou APIs de produção. Até uma consulta de readiness de busca pode iniciar acesso externo |
| IA e MCP | Providers no lock e [rotas MCP próprias](../../web/modules/custom/neruds_mcp/neruds_mcp.routing.yml) | Distinguir contrib MCP do módulo próprio; a API própria inclui escrita e exclusão de conteúdo. Nenhuma chamada ou permissão foi homologada |
| Identidade, Control Center, REST e JSON:API | [Rotas do guard](../../web/modules/custom/neruds_extensionista_guard/neruds_extensionista_guard.routing.yml) e snapshot | Manter clientes reais desconectados. JSON:API em leitura não neutraliza automaticamente endpoints próprios de escrita |
| Formulários e antispam | Webform, CAPTCHA, reCAPTCHA, Friendly Captcha e saneamento registrado | Handlers e credenciais precisam permanecer neutralizados; não enviar formulários a serviços reais |
| Analytics, mapas, embeds, fontes, CDNs e acessibilidade externa | [Módulo Google](../../web/modules/custom/neruds_google_integration/neruds_google_integration.module), bibliotecas dos temas e configurações Klaro registradas | Neutralizar todas as variantes de ambiente, incluindo fallbacks de produção. O navegador pode carregar recursos externos independentemente da rede do container de banco ou PHP |
| ORCID e ícones externos | [neruds_orcid.module](../../web/modules/custom/neruds_orcid/neruds_orcid.module) | Há links e apresentação de identificação; não demonstram OAuth ou sincronização de perfis |

A revisão estática também delimitou funções que não podem ser anunciadas como prontas: a métrica de Sheets retorna fallback local, Firebase tem resposta de placeholder 501 e o controlador de chat chama métodos não declarados no `PortalDataService` vinculado nesse baseline. São registros para a revisão de código próprio da issue #9, sem correção ou teste funcional nesta execução.

## 5. Integridade, verificações e limites

### O que foi conferido agora

1. JSON do manifesto, 287 caminhos únicos, tamanhos válidos e hashes SHA-256 válidos.
2. Igualdade do conjunto de arquivos da seleção com os arquivos do commit de importação e da base atual.
3. **287/287 correspondências de SHA-256 e tamanho**, comparando manifesto, bytes da worktree e blobs Git dos dois commits. Não houve diferença de finais de linha.
4. Detalhamento de **32 arquivos representativos**: dois Composer, metadata e código dos nove módulos e dos três temas, além de CSS, JavaScript e Twig do tema selecionado e dos diretórios compartilhados.
5. Leitura do lock e metadata dos componentes, conciliação de contagens e conferência dos limites das referências estruturais.
6. Guard local aprovado com 895 arquivos candidatos e 706 arquivos de texto; JSON, 19 links relativos, caminhos de evidência, UTF-8, escopo dos dois documentos e whitespace conferidos. O diff preparado para commit e a leitura do HEAD remoto são registrados no PR.

O método leu os blobs por `git archive --format=tar <commit> composer.json composer.lock web/modules/custom web/themes/custom` em memória e comparou seu conteúdo com `hashlib.sha256` e tamanho em bytes. A verificação dos arquivos da worktree usou os mesmos caminhos explícitos do manifesto, sem procurar uploads nem varrer os 98 mil arquivos do runtime privado.

### Hashes de referência

| Objeto | SHA-256 | Natureza da conferência |
| --- | --- | --- |
| composer.json | `a8104b7cf57eb6b28bf1332d950905738c54b5261c893c1fa99a30f6167c08d1` | Recalculado nesta execução |
| composer.lock | `cc1bfacc83dcb78df2e7fb3bced99eafac0685e778d5d5fc0b626dca92386d14` | Recalculado nesta execução |
| Runtime agregado | `ea4e4303bf023d6286a3c1200fa397678458a0766d73c851202efe7fb3b56986` | Registro histórico, sem nova hashagem |
| Referência estrutural agregada | `9f64a69f2a1f878eeaa92a7aa058433540d0888ec343acb747cd8894cf139ab6` | Registro histórico, sem nova hashagem integral |

A [análise estática anterior](../operations/STATIC-VALIDATION-2026-10-06.json) informa 41 arquivos PHP aprovados e validação Composer sem instalação, scripts, plugins ou rede. Os hashes desses 41 arquivos e dos dois Composer foram confrontados com os arquivos desta worktree, sem divergência. Esses resultados continuam sendo históricos; não foram executados novamente nesta revisão documental.

### O que esta execução não comprova

- O acesso SSH autenticado ao runtime preservado não estava disponível a partir da máquina selecionada. Assim, sua disponibilidade atual, os dumps, uploads, banco e controles do container não foram relidos. Isso limita a atualidade dessas evidências, sem invalidar a integridade dos fontes versionados conferida agora.
- Não foi feita nova captura, restauração, sanitização de banco, execução PHP/Drush, bootstrap, cache rebuild, importação de configuração, instalação ou atualização de dependências.
- PHP e Composer não estão disponíveis no desktop desta execução. O guard os informa como SKIP; não há aprovação nova desses checks.
- Isolamento funcional de saídas, permissões, fluxos, renderização, acessibilidade e compatibilidade de atualização permanecem nas tarefas correspondentes.
- A CI hospedada do candidato deve ser consultada após o push e registrada no PR. O histórico contém bloqueio de cobrança ou limite de gastos; aprovação local não pode ser apresentada como CI hospedada aprovada.

## 6. Critérios de aceite e evidência

| Critério da issue #1 | Evidência desta entrega | Limite |
| --- | --- | --- |
| Vincular a cópia à origem e informar defasagem | Seção 1; manifesto imutável; cronologia e `source_integrity` no JSON | Sem equivalência com a produção atual ou sincronismo atômico entre arquivos e banco |
| Identificar core, temas, módulos e runtime sem segredos | Seção 2; `custom_components`, `composer` e `runtime_observed_at_capture` | Habilitação dos módulos e funcionamento não inferidos |
| Enumerar inclusões, exclusões e integrações a isolar | Seções 3 e 4; `import_policy`, `manifest.excluded` e `integrations_to_isolate` | Sem reutilizar credenciais, dados ou destinos reais |
| Entregar revisão da base e critérios de importação por PR | Estes dois documentos, associados à única branch NERUDS-001; commit, push e PR registrados na issue | Entrega para revisão não equivale a merge, fechamento ou publicação |

## 7. Coordenação e próxima decisão

A divergência de caminhos da issue foi resolvida no [registro de escopo](https://github.com/rafaloct/neruds-portal/issues/1#issuecomment-6026116358): foram usados os artefatos canônicos de `docs/architecture/`, com acréscimo somente deste documento e da evidência JSON. Não foi criado um manifesto concorrente em `docs/baseline/`.

O PR #33 foi lido como proposta separada, ainda draft e não integrada na conferência inicial, no HEAD `0383d4ee103b23ecafd6320eee5c63a881f4c784`. Ele documenta o Project já criado e supera a nota antiga de `main` que dizia aguardar autenticação. Nenhum de seus oito arquivos foi alterado por esta entrega.

A execução usa `DESKTOP-8T5DRBS`, branch `docs/neruds-001-source-inventory-20261006` e worktree `C:\Users\Usuario\Downloads\neruds-portal-inventory-001`. O HEAD entregue e as verificações remotas pertencem ao PR e ao comentário de conclusão, evitando autorreferência de commit neste arquivo.

Ao entregar o PR, a issue deve permanecer aberta, com `agent:review`, e a Fila do Project deve ser `Em revisão`. Só a Fila desta issue é atualizada diretamente; o importador global não é reaplicado. A decisão seguinte é revisar e, se aprovado, autorizar a integração do candidato concreto. A execução não inicia automaticamente a issue #2.

As próximas atribuições podem aproveitar os artefatos já preservados, conferindo os controles pertinentes antes da primeira aplicação. Inventário de extensões habilitadas, primeira inicialização e recuperação funcional pertencem às issues #2 e #3; revisão do código próprio e versões, ao M02; migração e conteúdo recente, às tarefas de conteúdo e lançamento. A produção permanece sem alterações nesta revisão.
