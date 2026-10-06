# Portal NERUDS

Novo portal institucional de pesquisa e extensão do **Núcleo de Estudos Rurais, Desigualdades e Sistemas Socioecológicos**, com origem no Drupal que serve [neruds.org](https://neruds.org) e desenvolvimento em cópia isolada.

**Estado inicial: baseline importado para trabalho, ainda não atualizado e não pronto para publicação. O novo design será construído.** A presença de código, configuração, documentação ou CI neste repositório não comprova que a restauração, a atualização ou os critérios de aceite foram concluídos.

A decisão técnica inicial é manter **Drupal nativo**, porque o usuário pediu partir do portal de produção e rejeitou a homologação anterior como base. O trabalho preservará conteúdo, relações e ativos institucionais úteis da produção e desenvolverá uma experiência própria do NERUDS, orientada pela organização científica do CEBRAP e pela apresentação territorial do Instituto Perene.

## Origem e limites

| Informação da captura inicial | Valor identificado |
|---|---|
| Repositório | Privado: [rafaloct/neruds-portal](https://github.com/rafaloct/neruds-portal) |
| Origem do site publicado | `/var/www/html`, com document root `web/` |
| Distribuição e core observados | Drupal CMS **1.2.8** / Drupal core **11.2.12** |
| PHP observado | **8.4.14** |
| Banco de produção observado | MariaDB **11.8.3** |
| Seleção inicial de código | **287 arquivos** de fontes próprias e arquivos Composer, sujeitos ao inventário e saneamento registrados no baseline |
| Banco para desenvolvimento | Snapshot privado restaurado em MariaDB separado e sanitizado em 06/10/2026; aplicação ainda sem bootstrap ou validação funcional |

Esses valores registram a origem; não são versões de destino aprovadas. O inventário e os registros do baseline devem informar data, consistência e eventual defasagem da cópia. Não se presume equivalência com staging antigo, backups anteriores ou a homologação Jaspr.

**Produção permanece fora do escopo de alterações até autorização específica.** Desenvolvimento, atualizações, migrações, testes e ensaios de recuperação ocorrem em serviços, banco e arquivos próprios. Segredos, dumps, contas reais, arquivos privados e credenciais de produção ficam fora do Git e dos testes. A aplicação copiada deve ter envios e integrações de produção bloqueados antes de iniciar.

## Produto

O portal deve ajudar comunidades, cooperativas, pesquisadores, estudantes e gestores a encontrar o trabalho do núcleo, compreender seus resultados e acessar materiais confiáveis. A experiência conectará pessoas, projetos, produção científica, extensão e territórios, com atenção ao Tocantins, ao Cerrado e à Amazônia e sem fabricar indicadores ou parcerias.

- [Visão, escopo e quatro jornadas mensuráveis](docs/product/VISION.md)
- [Modelo de conteúdo e regras editoriais](docs/product/CONTENT_MODEL.md)
- [Benchmark com fontes e datas](docs/product/benchmark.md)
- [Definition of Done](docs/product/DEFINITION_OF_DONE.md)
- [Decisão arquitetural: Drupal nativo](docs/architecture/ADR-001-drupal-native.md)

## Trabalho e planejamento

O planejamento inicial compreende **32 issues em 8 milestones**, sem prazos fictícios. O estado operacional é mantido nas [issues](https://github.com/rafaloct/neruds-portal/issues), nos [PRs](https://github.com/rafaloct/neruds-portal/pulls) e nas [milestones](https://github.com/rafaloct/neruds-portal/milestones). Consulte o [roadmap](docs/planning/ROADMAP.md) para escopo e dependências e o [registro do bootstrap](docs/operations/BOOTSTRAP-2026-10-06.md) para entregas e limites verificados.

**GitHub Projects ainda está pendente do escopo de autenticação `project`.** Este README não declara um quadro criado. Issues e milestones permitem coordenar o trabalho enquanto essa permissão não estiver disponível. O [importador do Project](docs/planning/PROJECT.md) está preparado para concluir o quadro depois da autorização.

Os commits iniciais de importação e preparação de `main` fazem parte do bootstrap autorizado para o repositório novo. A evolução posterior segue **uma issue → uma branch → um PR**, com escopo delimitado e evidências. Entregas do bootstrap devem ser registradas nas issues pertinentes, sem encerrá-las apenas pela existência dos arquivos. A próxima execução deve conferir o que foi efetivamente preparado e o que falta validar no ambiente/restauração.

Antes de trabalhar, leia [AGENTS.md](AGENTS.md) e o [contrato de coordenação](docs/operations/AGENT_COORDINATION.md). Alterações reversíveis já autorizadas não exigem confirmações repetidas. **Merge e alterações em produção exigem autorização específica; não há auto-merge, deploy automático ou execução automática da fila.**

## Limites de automação da conta

O código foi enviado e o guard passou localmente. Na cópia privada, os 41 arquivos PHP herdados passaram na análise de sintaxe e o Composer validou o manifesto/lockfile, sem iniciar Drupal ou instalar dependências; [evidência](docs/operations/STATIC-VALIDATION-2026-10-06.json). A [primeira execução do GitHub Actions](https://github.com/rafaloct/neruds-portal/actions/runs/37515555374) não chegou a iniciar: o GitHub informou falha recente de pagamentos ou necessidade de ampliar o limite de gastos. A conta precisa ser regularizada pelo titular antes de validar a CI hospedada.

A API também retornou HTTP 403 para proteção de branches neste repositório privado, com exigência de GitHub Pro. Assim, o fluxo por PR e autorização está documentado, mas **a exigência de PR/checks ainda não é imposta pelo servidor**. Auto-merge permanece desativado e as restrições de Actions disponíveis foram aplicadas. Consulte [o estado verificado](docs/operations/repository-settings.json).

## Como começar

1. Leia a issue atribuída, suas dependências, caminhos e critérios de aceite no GitHub.
2. Confira a origem e o estado da cópia, os PRs abertos e possíveis sobreposições de arquivos/configuração.
3. Use um ambiente isolado com dados e contas de teste, seguindo o [ambiente de desenvolvimento](infra/dev/README.md).
4. Faça a mudança delimitada, execute os testes pertinentes e entregue um PR concreto e revisável.

Não use os arquivos deste repositório como comando implícito para iniciar o Drupal contra serviços reais. A montagem e a restauração são entregas verificáveis do primeiro milestone.
