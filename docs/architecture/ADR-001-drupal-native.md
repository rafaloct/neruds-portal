# ADR-001: Portal em Drupal nativo

- **Status:** decisão técnica inicial adotada para atender à orientação de clonar o portal de produção.
- **Data:** 06/10/2026.
- **Escopo:** arquitetura do novo repositório `rafaloct/neruds-portal`; não autoriza alterações em produção.

## Contexto

O usuário solicitou um novo repositório a partir do NERUDS de produção e rejeitou o frontend Jaspr como base do novo portal. A origem foi identificada em `/var/www/html`, com document root `web/`, Drupal CMS 1.2.8, core 11.2.12, PHP 8.4.14 e MariaDB 11.8.3 no serviço observado.

A seleção inicial de código é um baseline a inventariar, sanear e atualizar. Não comprova que a aplicação tenha sido restaurada, atualizada ou validada. O conteúdo de pesquisa, extensão, pessoas, projetos e publicações deve manter proveniência, relações e continuidade de URLs.

O portal exige novo design, descoberta do acervo, edição por equipe, revisão, permissões e operação recuperável. O [benchmark](../product/benchmark.md) orienta produto e linguagem visual própria; não estabelece um framework de frontend.

## Decisão

Construir a experiência pública e editorial em **Drupal nativo**, usando o modelo de conteúdo, configuração, controle de acesso, Views, formulários e tema Drupal conforme apropriado ao inventário e à tarefa. Desenvolver componentes e templates do novo design no tema próprio, com assets acessíveis e manutenção documentada.

Preservar os dados e componentes úteis da produção após revisão; atualizar core, contribuições e código próprio somente na cópia isolada. A matriz de versões de destino será confirmada com suporte vigente e resolução real de dependências nas issues NERUDS-006 a NERUDS-009. Este ADR não fixa versões futuras por antecipação.

Não haverá frontend Jaspr separado como camada pública do novo portal nem dependência do backend de produção durante desenvolvimento. Recursos de API que existam na origem serão inventariados e avaliados conforme necessidade, acesso e risco; sua existência não é decisão de manter uma arquitetura desacoplada.

## Alternativas consideradas

| Alternativa | Decisão e motivo |
|---|---|
| Evoluir a homologação Jaspr existente | Não adotada: o usuário a rejeitou como base do novo portal e pediu partir da produção. |
| Criar outra aplicação desacoplada | Não adotada neste escopo: acrescentaria uma camada e decisões de integração sem necessidade demonstrada nas jornadas definidas. |
| Manter Drupal e apenas trocar a aparência | Insuficiente: a entrega inclui atualização, revisão do modelo/acervo, permissões, descoberta e operação, além do design. |
| Drupal nativo com atualização e novo tema | Adotada: corresponde à orientação do usuário e reúne experiência pública e editorial no sistema de origem. |

## Consequências

- A equipe mantém uma experiência editorial integrada ao conteúdo e às permissões do Drupal.
- O tema e o código próprio precisam acompanhar compatibilidade, acessibilidade, cache e padrões do Drupal atualizado.
- Configuração relacionada deve ser versionada atomicamente e testada em importação; banco e arquivos privados ficam fora do Git.
- Conteúdo público, autoria, arquivos, IDs e aliases devem ser preservados ou migrados com mapa e evidências.
- O ambiente de desenvolvimento precisa de banco, arquivos e credenciais próprios, com integrações reais bloqueadas antes de iniciar a cópia.
- O design será construído e validado com conteúdo real; importar o tema atual não significa que esse design já foi entregue.
- Repositório e CI não publicam por conta própria. Merge e produção permanecem sujeitos a autorizações específicas.

## Como verificar esta decisão

As issues de design, conteúdo e descoberta devem entregar comportamento no Drupal nativo e suas configurações/tema. As quatro jornadas da [visão](../product/VISION.md), o fluxo editorial e a [Definition of Done](../product/DEFINITION_OF_DONE.md) fornecem critérios de validação. O plano técnico deve demonstrar restauração, atualização, isolamento e recuperação da candidata.

Uma proposta futura de mudar essa arquitetura exige motivação concreta, impacto sobre conteúdo/edição/operação e decisão explícita registrada em novo ADR. Não reintroduzir uma camada desacoplada silenciosamente durante uma issue de interface.
