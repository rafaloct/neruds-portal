# Roadmap do novo portal NERUDS

Plano inicial registrado em 06/10/2026. A origem é o portal de produção atual; a homologação Jaspr anterior não participa do novo design. O estado real de cada entrega vive nas issues.

## Objetivo

Construir um portal de pesquisa e extensão com novo design e conteúdo confiável, relacionando pessoas, projetos, publicações, materiais, territórios e resultados. A edição permanece no Drupal.

## Milestones

| Marco | Resultado esperado | Issues |
|---|---|---|
| [M01 Origem confiável e cópia isolada](https://github.com/rafaloct/neruds-portal/milestone/1) | Estabelecer como base o Drupal que serve neruds.org atualmente, com origem comprovada, conteúdo protegido e desenvolvimento reproduzível. | [#1](https://github.com/rafaloct/neruds-portal/issues/1), [#2](https://github.com/rafaloct/neruds-portal/issues/2), [#3](https://github.com/rafaloct/neruds-portal/issues/3), [#4](https://github.com/rafaloct/neruds-portal/issues/4), [#5](https://github.com/rafaloct/neruds-portal/issues/5) |
| [M02 Drupal atualizado e compatível](https://github.com/rafaloct/neruds-portal/milestone/2) | Colocar a cópia em versões mantidas e compatíveis, preservando os recursos existentes que continuarem úteis. | [#6](https://github.com/rafaloct/neruds-portal/issues/6), [#7](https://github.com/rafaloct/neruds-portal/issues/7), [#8](https://github.com/rafaloct/neruds-portal/issues/8), [#9](https://github.com/rafaloct/neruds-portal/issues/9) |
| [M03 Governança editorial e organização do conteúdo](https://github.com/rafaloct/neruds-portal/milestone/3) | Definir taxonomias, relações, revisão e permissões para uma equipe de pesquisa manter o portal com autonomia. | [#10](https://github.com/rafaloct/neruds-portal/issues/10), [#11](https://github.com/rafaloct/neruds-portal/issues/11), [#12](https://github.com/rafaloct/neruds-portal/issues/12) |
| [M04 Novo design institucional e experiência pública](https://github.com/rafaloct/neruds-portal/milestone/4) | Construir um novo tema Drupal responsivo e acessível, inspirado na qualidade editorial de CEBRAP e Instituto Perene e na identidade do NERUDS. | [#13](https://github.com/rafaloct/neruds-portal/issues/13), [#14](https://github.com/rafaloct/neruds-portal/issues/14), [#15](https://github.com/rafaloct/neruds-portal/issues/15), [#16](https://github.com/rafaloct/neruds-portal/issues/16) |
| [M05 Pesquisadores, projetos e acervo conectado](https://github.com/rafaloct/neruds-portal/milestone/5) | Transformar o conteúdo herdado em um portal de pesquisa navegável, com relações entre pessoas, projetos, produção e territórios. | [#17](https://github.com/rafaloct/neruds-portal/issues/17), [#18](https://github.com/rafaloct/neruds-portal/issues/18), [#19](https://github.com/rafaloct/neruds-portal/issues/19), [#20](https://github.com/rafaloct/neruds-portal/issues/20), [#21](https://github.com/rafaloct/neruds-portal/issues/21), [#22](https://github.com/rafaloct/neruds-portal/issues/22) |
| [M06 Descoberta, mídia e desempenho](https://github.com/rafaloct/neruds-portal/milestone/6) | Facilitar encontrar, compartilhar e acessar a produção do núcleo com páginas rápidas e conteúdo completo. | [#23](https://github.com/rafaloct/neruds-portal/issues/23), [#24](https://github.com/rafaloct/neruds-portal/issues/24), [#25](https://github.com/rafaloct/neruds-portal/issues/25), [#26](https://github.com/rafaloct/neruds-portal/issues/26) |
| [M07 Operação recuperável e entrega reproduzível](https://github.com/rafaloct/neruds-portal/milestone/7) | Preparar manutenção, recuperação e entrega sem depender de conhecimento informal de uma única pessoa. | [#27](https://github.com/rafaloct/neruds-portal/issues/27), [#28](https://github.com/rafaloct/neruds-portal/issues/28), [#29](https://github.com/rafaloct/neruds-portal/issues/29) |
| [M08 Validação e publicação com autorização específica](https://github.com/rafaloct/neruds-portal/milestone/8) | Validar o portal completo e preparar uma publicação futura com reversão e decisão explícita. | [#30](https://github.com/rafaloct/neruds-portal/issues/30), [#31](https://github.com/rafaloct/neruds-portal/issues/31), [#32](https://github.com/rafaloct/neruds-portal/issues/32) |

## Fila completa

| Issue | Prioridade | Dependências | Entrega |
|---|---|---|---|
| [#1](https://github.com/rafaloct/neruds-portal/issues/1) | P0 | Sem dependências | Validar a cópia atual e consolidar o inventário de importação |
| [#2](https://github.com/rafaloct/neruds-portal/issues/2) | P0 | [#1](https://github.com/rafaloct/neruds-portal/issues/1) | Preparar infraestrutura de desenvolvimento isolada para o Drupal |
| [#3](https://github.com/rafaloct/neruds-portal/issues/3) | P0 | [#2](https://github.com/rafaloct/neruds-portal/issues/2) | Restaurar uma cópia protegida do portal atual no ambiente isolado |
| [#4](https://github.com/rafaloct/neruds-portal/issues/4) | P0 | [#3](https://github.com/rafaloct/neruds-portal/issues/3) | Registrar um baseline versionado e livre de segredos |
| [#5](https://github.com/rafaloct/neruds-portal/issues/5) | P0 | [#4](https://github.com/rafaloct/neruds-portal/issues/4) | Configurar o contrato de trabalho por issues e a integração contínua |
| [#6](https://github.com/rafaloct/neruds-portal/issues/6) | P0 | [#4](https://github.com/rafaloct/neruds-portal/issues/4) | Definir a matriz de atualização do Drupal e suas dependências |
| [#7](https://github.com/rafaloct/neruds-portal/issues/7) | P0 | [#6](https://github.com/rafaloct/neruds-portal/issues/6), [#5](https://github.com/rafaloct/neruds-portal/issues/5) | Atualizar o Drupal core e o runtime da cópia |
| [#8](https://github.com/rafaloct/neruds-portal/issues/8) | P0 | [#7](https://github.com/rafaloct/neruds-portal/issues/7) | Atualizar ou remover dependências contrib incompatíveis e sem manutenção |
| [#9](https://github.com/rafaloct/neruds-portal/issues/9) | P0 | [#8](https://github.com/rafaloct/neruds-portal/issues/8) | Revisar segurança e compatibilidade do código Drupal próprio |
| [#10](https://github.com/rafaloct/neruds-portal/issues/10) | P1 | [#9](https://github.com/rafaloct/neruds-portal/issues/9) | Definir taxonomias e relações do acervo de pesquisa |
| [#11](https://github.com/rafaloct/neruds-portal/issues/11) | P1 | [#10](https://github.com/rafaloct/neruds-portal/issues/10) | Configurar revisão editorial, publicação e arquivamento |
| [#12](https://github.com/rafaloct/neruds-portal/issues/12) | P0 | [#11](https://github.com/rafaloct/neruds-portal/issues/11) | Aplicar permissões editoriais por ação no Drupal |
| [#13](https://github.com/rafaloct/neruds-portal/issues/13) | P1 | [#1](https://github.com/rafaloct/neruds-portal/issues/1), [#9](https://github.com/rafaloct/neruds-portal/issues/9) | Criar o sistema visual do novo tema institucional |
| [#14](https://github.com/rafaloct/neruds-portal/issues/14) | P1 | [#13](https://github.com/rafaloct/neruds-portal/issues/13) | Implementar navegação e estrutura global responsivas |
| [#15](https://github.com/rafaloct/neruds-portal/issues/15) | P1 | [#14](https://github.com/rafaloct/neruds-portal/issues/14), [#10](https://github.com/rafaloct/neruds-portal/issues/10) | Construir a home editorial do NERUDS |
| [#16](https://github.com/rafaloct/neruds-portal/issues/16) | P1 | [#14](https://github.com/rafaloct/neruds-portal/issues/14), [#12](https://github.com/rafaloct/neruds-portal/issues/12) | Organizar páginas institucionais e contato do núcleo |
| [#17](https://github.com/rafaloct/neruds-portal/issues/17) | P1 | [#10](https://github.com/rafaloct/neruds-portal/issues/10), [#12](https://github.com/rafaloct/neruds-portal/issues/12), [#14](https://github.com/rafaloct/neruds-portal/issues/14) | Implementar perfis de pesquisadores e relações com a produção |
| [#18](https://github.com/rafaloct/neruds-portal/issues/18) | P1 | [#17](https://github.com/rafaloct/neruds-portal/issues/17), [#10](https://github.com/rafaloct/neruds-portal/issues/10), [#12](https://github.com/rafaloct/neruds-portal/issues/12), [#14](https://github.com/rafaloct/neruds-portal/issues/14) | Implementar projetos de pesquisa e extensão |
| [#19](https://github.com/rafaloct/neruds-portal/issues/19) | P1 | [#17](https://github.com/rafaloct/neruds-portal/issues/17), [#18](https://github.com/rafaloct/neruds-portal/issues/18), [#10](https://github.com/rafaloct/neruds-portal/issues/10), [#12](https://github.com/rafaloct/neruds-portal/issues/12), [#14](https://github.com/rafaloct/neruds-portal/issues/14) | Implementar publicações e referências bibliográficas |
| [#20](https://github.com/rafaloct/neruds-portal/issues/20) | P1 | [#18](https://github.com/rafaloct/neruds-portal/issues/18), [#19](https://github.com/rafaloct/neruds-portal/issues/19), [#10](https://github.com/rafaloct/neruds-portal/issues/10), [#12](https://github.com/rafaloct/neruds-portal/issues/12), [#14](https://github.com/rafaloct/neruds-portal/issues/14) | Estruturar o acervo documental e de dados públicos |
| [#21](https://github.com/rafaloct/neruds-portal/issues/21) | P1 | [#18](https://github.com/rafaloct/neruds-portal/issues/18), [#19](https://github.com/rafaloct/neruds-portal/issues/19), [#20](https://github.com/rafaloct/neruds-portal/issues/20), [#10](https://github.com/rafaloct/neruds-portal/issues/10), [#14](https://github.com/rafaloct/neruds-portal/issues/14) | Apresentar atuação territorial e resultados verificáveis |
| [#22](https://github.com/rafaloct/neruds-portal/issues/22) | P0 | [#17](https://github.com/rafaloct/neruds-portal/issues/17), [#18](https://github.com/rafaloct/neruds-portal/issues/18), [#19](https://github.com/rafaloct/neruds-portal/issues/19), [#20](https://github.com/rafaloct/neruds-portal/issues/20), [#21](https://github.com/rafaloct/neruds-portal/issues/21) | Migrar e conferir o conteúdo herdado na estrutura nova |
| [#23](https://github.com/rafaloct/neruds-portal/issues/23) | P1 | [#22](https://github.com/rafaloct/neruds-portal/issues/22), [#14](https://github.com/rafaloct/neruds-portal/issues/14), [#12](https://github.com/rafaloct/neruds-portal/issues/12) | Implementar busca unificada com filtros e paginação |
| [#24](https://github.com/rafaloct/neruds-portal/issues/24) | P1 | [#22](https://github.com/rafaloct/neruds-portal/issues/22), [#14](https://github.com/rafaloct/neruds-portal/issues/14) | Preservar URLs e preparar metadados para descoberta pública |
| [#25](https://github.com/rafaloct/neruds-portal/issues/25) | P1 | [#22](https://github.com/rafaloct/neruds-portal/issues/22), [#14](https://github.com/rafaloct/neruds-portal/issues/14), [#12](https://github.com/rafaloct/neruds-portal/issues/12) | Padronizar mídia, arquivos e alternativas acessíveis |
| [#26](https://github.com/rafaloct/neruds-portal/issues/26) | P1 | [#23](https://github.com/rafaloct/neruds-portal/issues/23), [#25](https://github.com/rafaloct/neruds-portal/issues/25) | Medir e corrigir gargalos de desempenho do portal |
| [#27](https://github.com/rafaloct/neruds-portal/issues/27) | P0 | [#22](https://github.com/rafaloct/neruds-portal/issues/22), [#9](https://github.com/rafaloct/neruds-portal/issues/9) | Implementar backup e comprovar restauração da candidata |
| [#28](https://github.com/rafaloct/neruds-portal/issues/28) | P1 | [#9](https://github.com/rafaloct/neruds-portal/issues/9), [#27](https://github.com/rafaloct/neruds-portal/issues/27) | Preparar diagnóstico operacional e runbooks de manutenção |
| [#29](https://github.com/rafaloct/neruds-portal/issues/29) | P0 | [#5](https://github.com/rafaloct/neruds-portal/issues/5), [#8](https://github.com/rafaloct/neruds-portal/issues/8), [#9](https://github.com/rafaloct/neruds-portal/issues/9), [#27](https://github.com/rafaloct/neruds-portal/issues/27) | Preparar build reproduzível e entrega manual da candidata |
| [#30](https://github.com/rafaloct/neruds-portal/issues/30) | P0 | [#16](https://github.com/rafaloct/neruds-portal/issues/16), [#21](https://github.com/rafaloct/neruds-portal/issues/21), [#22](https://github.com/rafaloct/neruds-portal/issues/22), [#23](https://github.com/rafaloct/neruds-portal/issues/23), [#24](https://github.com/rafaloct/neruds-portal/issues/24), [#25](https://github.com/rafaloct/neruds-portal/issues/25), [#26](https://github.com/rafaloct/neruds-portal/issues/26), [#12](https://github.com/rafaloct/neruds-portal/issues/12), [#29](https://github.com/rafaloct/neruds-portal/issues/29) | Validar os fluxos completos e a acessibilidade da candidata |
| [#31](https://github.com/rafaloct/neruds-portal/issues/31) | P0 | [#30](https://github.com/rafaloct/neruds-portal/issues/30), [#27](https://github.com/rafaloct/neruds-portal/issues/27), [#28](https://github.com/rafaloct/neruds-portal/issues/28), [#29](https://github.com/rafaloct/neruds-portal/issues/29) | Consolidar conteúdo final e preparar o pacote de lançamento |
| [#32](https://github.com/rafaloct/neruds-portal/issues/32) | P0 | [#31](https://github.com/rafaloct/neruds-portal/issues/31) | Publicar a versão aprovada e verificar o resultado |

## Sequência e paralelismo

M01 fixa uma base reproduzível. M02 resolve versões e compatibilidade. Modelo editorial e design podem avançar em paralelo depois da origem validada, desde que reservem os arquivos e o banco de configuração. As páginas do acervo dependem dessas decisões. Descoberta, operação e validação completam a candidata.

Uma worktree não isola um banco compartilhado. Composer, papéis e configurações Drupal sobrepostos devem ter execução coordenada. Cada execução seleciona uma issue; não há consumo automático da fila.

## Primeira tarefa

A [issue #1](https://github.com/rafaloct/neruds-portal/issues/1) começa pronta para validar os artefatos de bootstrap e identificar somente as lacunas reais. O código e o snapshot atual já foram preparados durante a criação deste projeto; a tarefa não deve repetir essa captura sem motivo. As verificações funcionais de restauração e ambiente permanecem nas issues #2 e #3.

## Critério de conclusão

Os oito marcos terminam por evidência, sem datas artificiais. A candidata precisa cumprir as quatro jornadas da [visão](../product/VISION.md), os controles editoriais e a [Definition of Done](../product/DEFINITION_OF_DONE.md). A issue #32 depende de autorização específica para produção. Nenhuma outra etapa autoriza esse corte.

## GitHub Projects

A especificação do quadro está em [PROJECT.md](PROJECT.md). Issues, milestones e dependências são utilizáveis desde já. O quadro nativo depende do escopo adicional de autenticação `project`; sua preparação não equivale à criação remota.
