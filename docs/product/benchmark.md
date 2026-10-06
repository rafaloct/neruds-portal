# Benchmark editorial e de experiência para o novo Portal NERUDS

**Data da consulta:** 6 de outubro de 2026.  
**Base do novo projeto:** NERUDS de produção, em `https://neruds.org`.  
**Referências:** CEBRAP e Instituto Perene; páginas oficiais da UFT para vínculo institucional.  
**Finalidade:** orientar arquitetura da informação, conteúdo, identidade, experiência e backlog do novo repositório. A decisão por Drupal nativo está registrada no [ADR-001](../architecture/ADR-001-drupal-native.md). Este benchmark não autoriza publicação em produção.

## Direção proposta

Construir um portal de pesquisa que permita descobrir **o que o NERUDS investiga, com quem trabalha, em quais territórios atua e quais conhecimentos compartilha**. A pesquisa e a extensão devem ser compreensíveis tanto para uma pessoa da comunidade ou de uma cooperativa quanto para pesquisadores, estudantes, gestores e possíveis parceiros.

Do CEBRAP, aproveitar a organização do acervo e as relações entre pesquisa, equipe, núcleos e resultados. Do Perene, aproveitar a explicação territorial dos projetos e a presença das comunidades como participantes. A identidade, os textos, as imagens e os dados do NERUDS devem vir de fontes próprias e ser revisados com sua equipe. Não reproduzir logotipos, slogans, textos ou código visual dos referenciais.

O NERUDS de produção já tem projetos, publicações com DOI, perfis e filtros. Esses ativos são o ponto de partida. A homologação anterior não é referência visual para esta proposta.

## Método e limites

Foram consultados resultados de busca e páginas públicas dos próprios sites, incluindo páginas internas. As observações abaixo descrevem a estrutura e o conteúdo recuperados. Não houve acesso administrativo, submissão de formulário ou alteração dos sites.

Esta etapa **não é uma auditoria visual por captura de tela**, nem medição de contraste, desempenho ou navegação por teclado. Não foram atribuídas notas, fontes tipográficas, cores ou métricas aos sites de referência. Duplicações na extração podem resultar de variantes responsivas escondidas; quando isso é possível, o item é registrado para confirmação no HTML e no navegador.

Datas de consulta e datas de publicação são distintas. Uma página sem data editorial não foi tratada como conteúdo produzido em 2026. Quando um indicador aparece como meta futura na fonte, ele não é apresentado aqui como resultado realizado.

## Referências concretas

| ID | Observação no site oficial | Aplicação proposta ao NERUDS | Fonte |
|---|---|---|---|
| C01 | O CEBRAP organiza entradas próprias para pesquisas, pesquisadores e núcleos; notícias, publicações e agenda aparecem em seções distintas da página inicial. | Distinguir o acervo permanente da comunicação corrente. A pesquisa deve continuar encontrável depois que sua notícia sair da home. | [CEBRAP, início](https://cebrap.org.br/) |
| C02 | O catálogo de pesquisas oferece busca e agrupamento por núcleo, com associações explícitas entre os registros. | Projetos com filtros úteis e vínculos para temas, equipe e produtos. Um termo deve conduzir a uma coleção, não a uma página vazia. | [Pesquisas](https://cebrap.org.br/pesquisas/) |
| C03 | A apresentação dos núcleos combina escopo, coordenação e acesso às pesquisas correspondentes. | Cada eixo do NERUDS deve explicar sua pergunta de pesquisa em linguagem direta e reunir pessoas, projetos e resultados relacionados. | [Núcleos](https://cebrap.org.br/nucleos/) |
| C04 | A Biblioteca Virtual separa acervo bibliográfico, documental e audiovisual e oferece campos de autor, título, data e palavra-chave. | Um acervo unificado com tipos explícitos; filtros progressivos por autor, ano, tema e formato. Evitar fragmentar a descoberta em vários sites sem busca comum. | [Biblioteca Virtual](https://bibliotecavirtual.cebrap.org.br/) |
| C05 | Uma notícia de 30/09/2026 apresenta objeto do estudo, método, autores, resultado e acesso à pesquisa completa. | Padronizar notícias de pesquisa com pergunta, evidência, implicação e link da publicação. Identificar separadamente autoria científica e autoria da notícia. | [Notícia sobre participação social](https://cebrap.org.br/estudo-com-pesquisadores-do-cebrap-analisa-189-programas-de-governo-e-a-participacao-social-nas-eleicoes-de-2026/) |
| C06 | A página do Caderno 4 do COMPLUS, publicada em 12/08/2026, contextualiza achados, informa o projeto e disponibiliza versões em português e inglês. | Cada resultado deve ter resumo HTML, contexto de produção e acesso ao documento; oferecer tradução quando houver uma versão efetivamente revisada. | [COMPLUS, Caderno 4](https://cebrap.org.br/complus-caderno-4-governanca-em-sistemas-de-saude-plurais/) |
| C07 | O institucional apresenta governança e referências a boas práticas, privacidade e segurança da informação. | Disponibilizar vínculo UFT, organização do núcleo, contatos responsáveis e documentos públicos pertinentes. Redimensionar o conteúdo à estrutura real de um núcleo universitário. | [Institucional](https://cebrap.org.br/institucional/) |
| C08 | A Cátedra de Inclusão Produtiva Rural, vinculada ao CEBRAP, conecta trajetória, eixos, equipe, parceiros e diferentes formatos de publicação, incluindo sínteses e notas para políticas públicas. | É uma referência temática próxima: traduzir resultados sobre cooperativismo, extensão rural e território em artigos, cartilhas e sínteses para uso público, sem inventar produtos inexistentes. | [Cátedra, sobre](https://inclusaoprodutivarural.cebrap.org.br/sobre/) |
| P01 | O Perene apresenta propósito, pilares, biomas, projetos e comunidades como entradas reconhecíveis. | Explicar a atuação logo na abertura e oferecer caminhos por tema e território; a pessoa não deve precisar conhecer a estrutura acadêmica para navegar. | [Perene, início](https://www.perene.org.br/) |
| P02 | O catálogo de projetos oferece descrições curtas que articulam a ação realizada e sua localização. | Nos cartões, informar problema/ação, território e situação do projeto antes de detalhes administrativos. | [Projetos](https://www.perene.org.br/projetos/) |
| P03 | A página de comunidades descreve envolvimento de lideranças desde o planejamento e transferência de conhecimentos. | Apresentar atores locais, formas de participação, atividades e devolutivas. Dar espaço às pessoas que produzem conhecimento junto à universidade. | [Comunidades](https://www.perene.org.br/comunidades/) |
| P04 | A página reNascer Cerrado situa a atuação no Tocantins, apresenta tecnologia, uma meta de entregas e trabalho em área rural próxima a Palmas. | Projetos precisam distinguir contexto, método, território, metas e resultados, com data de atualização para cada indicador. Não converter uma meta futura da fonte em impacto comprovado. | [reNascer Cerrado](https://www.perene.org.br/renascer-cerrado/) |
| P05 | A página de pilares articula conservação ambiental, meios de vida e uma tecnologia aplicada. | Ligar conceitos acadêmicos a problemas concretos: organização coletiva, comercialização, renda, gestão territorial, educação ambiental e sistemas socioecológicos. | [Pilares](https://www.perene.org.br/pilares/) |

O benchmarking é seletivo. A página inicial do Perene ainda destaca notícias de 2023 na consulta de 2026; isso reforça a necessidade de uma política explícita de atualização. A extração de algumas páginas do CEBRAP contém texto de preenchimento ou shortcode, que devem ser confirmados antes de qualquer diagnóstico sobre sua apresentação visual. Esses aspectos não são padrões a reproduzir. [Fontes: C01, P01.]

## O que preservar da produção do NERUDS

| Ativo observado | Decisão para o novo projeto | Fonte |
|---|---|---|
| Nome completo do núcleo e vinculação visível à UFT. | Preservar reconhecimento institucional; confirmar redação, marcas e vínculos atuais com documentos do núcleo. | [Início NERUDS](https://neruds.org/), [página oficial UFT](https://www.uft.edu.br/nucleos-de-pesquisa-e-extensao/nucleo-de-estudos-rurais-desigualdades-e-sistemas-socioecologicos) |
| Projetos com filtros de título, ODS, eixo, situação e tipo. Há conteúdo sobre Jalapão e educação ambiental em Palmas. | Preservar dados e relações; apresentar resumos curtos na listagem e conteúdo aprofundado na página do projeto. | [Projetos NERUDS](https://neruds.org/projetos) |
| Publicações com ano, autoria, periódico e filtros. Um registro de pesquisa sobre gênero e associação camponesa tem DOI e resumo. | Manter autoria completa, DOI, URL, ano, resumo e relação com os pesquisadores; preservar os endereços citados ou prever redirecionamentos. | [Publicações](https://neruds.org/publicacoes), [exemplo de publicação](https://neruds.org/genero-e-poder-uma-analise-de-uma-diretoria-de-associacao-camponesa-no-norte-do-tocantins) |
| Perfis de pesquisadores com biografia, formação, vínculo e temas. | Separar uma apresentação curta para a listagem de uma biografia completa no perfil. Preservar colaboradores e instituições associadas. | [Pesquisadores](https://neruds.org/pesquisadores) |
| Grupo sobre cooperativismo, extensão rural e processos participativos, com período e liderança na home. | Dar ao grupo página própria e ligações com os projetos; revisar contagens e situação antes de exibi-las como indicadores. | [Início NERUDS](https://neruds.org/) |
| Página institucional com missão e atuação em desenvolvimento rural, sistemas socioecológicos, desigualdades, turismo e educação ambiental. | Usar como inventário inicial; validar com a coordenação a hierarquia editorial dos eixos. | [Sobre NERUDS](https://neruds.org/sobre) |
| Registro oficial da Resolução Consepe 36/2021 relativo à criação do núcleo. | Referenciar a origem institucional com documento primário. A data de upload do arquivo não deve ser usada como data de fundação. | [UFTDocs, resolução](https://docs.uft.edu.br/share/s/VsZ8hY-7TOmffL5mzLsNZQ) |

### Pontos editoriais e de apresentação para verificar na cópia

1. **Listagens extensas:** a extração de `/projetos` inclui objetivos, área de atuação e equipe dentro dos resultados; `/pesquisadores` inclui biografias longas e vários rótulos. Confirmar no HTML e reduzir o cartão a título, resumo e metadados essenciais.
2. **Taxonomia heterogênea:** `/linhas-de-pesquisa` mistura temas amplos, capitalização desigual e títulos semelhantes a projetos específicos. Elaborar uma tabela de equivalência revisada por pessoas responsáveis pelo conteúdo, conservando os IDs e relações antigas durante a migração.
3. **Integridade textual:** uma publicação em `/publicacoes` contém os caracteres `¿` em torno de parte do título. Comparar com a referência bibliográfica antes de corrigir; não aplicar substituições indiscriminadas no acervo.
4. **Resumos:** a extração da home repete o resumo da notícia de escolas de Palmas. Confirmar se vem do conteúdo, de dois modos de exibição ou da extração; o resultado esperado é uma apresentação sem redundância.
5. **Cronologia editorial:** a home exibe uma notícia de maio de 2026 que descreve etapa iniciada em março de 2025. Isso pode ser uma republicação legítima. Distinguir data do acontecimento, publicação e atualização, sem classificar a diferença como erro automaticamente.
6. **Autoria científica:** no exemplo de publicação, a pessoa vinculada ao perfil e a lista completa de autores são campos diferentes. A ficha bibliográfica deve preservar todos os autores e suas ordens; ter perfil no portal não pode determinar quem recebe crédito.

Fontes: [home](https://neruds.org/), [projetos](https://neruds.org/projetos), [pesquisadores](https://neruds.org/pesquisadores), [linhas de pesquisa](https://neruds.org/linhas-de-pesquisa), [publicações](https://neruds.org/publicacoes), [registro bibliográfico examinado](https://neruds.org/genero-e-poder-uma-analise-de-uma-diretoria-de-associacao-camponesa-no-norte-do-tocantins).

## Arquitetura da informação proposta

A proposta abaixo é uma decisão de produto derivada do benchmark, não uma descrição de uma estrutura já aprovada pelo NERUDS.

| Entrada principal | Conteúdo | Ação que deve facilitar |
|---|---|---|
| O NERUDS | Missão, história, vínculo UFT, organização, parceiros e documentos públicos. | Entender a instituição e encontrar contato confiável. |
| Pesquisa e extensão | Projetos, grupos e eixos, com situação e período. | Descobrir quem investiga determinado tema e acessar seus resultados. |
| Territórios | Coleções editoriais de projetos e resultados por território, quando houver conteúdo validado. | Conhecer o trabalho numa região sem precisar saber o nome do projeto. |
| Publicações e materiais | Artigos, livros, capítulos, relatórios, cartilhas, notas técnicas e audiovisual existentes. | Ler, citar, baixar ou compartilhar um resultado. |
| Pessoas | Pesquisadores, estudantes e colaboradores, com funções e vínculos claros. | Encontrar competências e conexões. |
| Notícias e agenda | Notícias datadas, encontros, eventos e oportunidades confirmadas. | Acompanhar atividades ou participar de uma oportunidade aberta. |

Busca e contato permanecem acessíveis globalmente. Documentos institucionais, acessibilidade e privacidade podem ficar no rodapé. A inclusão de uma área internacional depende de capacidade real de tradução e manutenção. Não criar páginas vazias para completar o menu.

Os eixos editoriais iniciais a validar são: cooperativismo e organização coletiva; extensão rural e inclusão produtiva; desigualdades e relações de gênero; sistemas socioecológicos e gestão territorial; educação ambiental e tecnologias aplicadas. Eles não substituem automaticamente linhas acadêmicas formais. Termos, grupos de pesquisa, projetos, territórios e ODS devem ser entidades distintas, relacionadas entre si.

### Jornadas prioritárias

| Público | Tarefa | Caminho esperado |
|---|---|---|
| Pessoa de cooperativa, associação ou comunidade | Encontrar atividade e material úteis ao seu território. | Tema ou território → projeto → material acessível e contato da equipe. |
| Pesquisador ou estudante | Localizar e citar uma publicação ou conhecer uma linha. | Busca/acervo → registro completo → DOI/documento → autores e projeto. |
| Gestor ou parceiro | Entender capacidade de atuação e resultados. | Projeto → método, período, equipe, parceiros e resultados comprovados. |
| Jornalista ou público interessado | Compreender um achado e acessar sua evidência. | Notícia → síntese em linguagem direta → estudo e contato institucional. |

### Página inicial

Compor uma abertura curta com nome, vínculo e propósito do NERUDS, seguida por um destaque editorial significativo. Depois, mostrar projetos selecionados, uma entrada por território ou tema, publicações recentes, notícias/agenda e pessoas/parcerias. A ordem deve ser validada com as jornadas, e o número de seções deve acompanhar a capacidade de curadoria.

Evitar depender de um carrossel para descobrir conteúdo principal. Metadados essenciais e links devem aparecer sem animação ou interação adicional. Números de impacto só entram quando houver método de contagem, período, fonte e responsável definidos.

## Modelo de conteúdo mínimo

| Tipo | Campos essenciais propostos | Relações |
|---|---|---|
| Projeto | Título, resumo público, problema, objetivos, método, período, situação, território, equipe, parceiros, fonte de financiamento quando pública, resultados, materiais e atualização. | Pessoas, eixos, territórios, publicações, notícias, eventos. |
| Publicação/material | Título canônico, autoria completa e ordenada, ano, tipo, resumo, veículo, DOI/URL, arquivo quando autorizado, idioma e referência para citação. | Pessoas com perfil, projetos, temas, territórios. |
| Pessoa | Nome de exibição, função no núcleo, vínculo atual, biografia curta e completa, interesses, foto com crédito e links acadêmicos verificados. | Projetos, grupos, produções. |
| Território | Nome, recorte geográfico compreensível, contexto, parceiros locais, projetos e resultados. | Projetos e materiais públicos; mapas apenas quando houver dados apropriados. |
| Notícia | Título informativo, resumo, data do fato quando distinta, publicação/atualização, autoria editorial, corpo, fontes e mídia com legenda/crédito. | Projeto, pessoas, publicação, evento. |
| Evento/oportunidade | Título, descrição, data/horário e fuso, modalidade/local, público, situação e inscrição quando aberta. | Projeto e pessoas; registro da atividade após encerramento. |

A autoria de uma notícia, a autoria de uma obra e a conta que cadastrou o registro não são a mesma informação. O sistema deve permitir edição e revisão sem publicar por engano o nome de um usuário técnico como autor científico.

## Identidade e direção visual próprias

Estas orientações são propostas para o NERUDS, não afirmações sobre cores ou tipografia dos sites consultados:

- Partir do logotipo e dos ativos válidos da produção. Inventariar variantes, resolução, licença e regras de aplicação da marca UFT antes da criação dos componentes.
- Usar uma linguagem editorial legível: títulos com hierarquia clara, resumos curtos e espaço consistente para metadados. Reservar a densidade informacional maior para catálogos e fichas.
- Valorizar fotografias documentais próprias de campo, encontros, pessoas e paisagens relacionadas ao conteúdo, com legenda, local, data quando relevante e autoria. Imagens decorativas não devem simular evidência de projetos reais.
- Mostrar pesquisadores e participantes com dignidade e contexto. Retratos de comunidades devem acompanhar processos e contribuições, sem reduzir pessoas a uma ilustração genérica de vulnerabilidade.
- Desenvolver tokens próprios de cor, tipografia, espaçamento, foco e contraste a partir da marca existente. A aprovação visual deve comparar a produção e a proposta nova, sem usar a homologação anterior como modelo.
- Oferecer versões leves para imagens, downloads identificados e leitura útil em celular e conexão limitada. Mapas, vídeos e painéis devem complementar um conteúdo textual acessível.

## Qualidade editorial como parte do produto

1. Cada página permanente tem uma pessoa responsável e uma data de revisão. Funções institucionais e situações de projetos precisam de revisão periódica.
2. Cada projeto informa o que está planejado, em andamento e concluído. Metas e resultados são campos distintos.
3. Uma notícia de pesquisa liga a evidência original e explica alcance e limitações do resultado em linguagem pública.
4. Cada publicação preserva título, autoria, ano e identificador. Correções bibliográficas têm justificativa e registro.
5. Cada imagem tem informação de autoria/uso e texto alternativo adequado ao contexto; arquivos para baixar têm título, formato e tamanho.
6. O conteúdo institucional sobre UFT, UFNT, programas e parceiros é validado com fontes e responsáveis atuais. Não ampliar afiliações por inferência a partir da biografia de uma pessoa.
7. Contadores derivam do acervo revisado. Não inventar métricas, depoimentos, parcerias ou alcance territorial para preencher componentes.
8. A mesma informação deve ser reutilizada por relacionamento, evitando copiar a biografia completa de uma pessoa para cada publicação ou projeto.

## Critérios propostos para definir qualidade comparável

Comparabilidade deve ser demonstrada por tarefas e qualidade do acervo, sem alegar que os referenciais passaram por uma certificação técnica nesta pesquisa.

| Dimensão | Critério verificável no novo projeto |
|---|---|
| Origem | Inventário vincula cada conteúdo e ativo migrado à produção; existe uma lista explícita de itens que exigem validação editorial. |
| Encontrabilidade | As quatro jornadas principais funcionam em celular e desktop; uma publicação pode ser encontrada por título e autor, e projetos por tema/situação. |
| Relações | Registros de amostra levam de projeto a pessoa e publicação e permitem voltar à coleção pertinente. |
| Integridade | Nenhum registro de autoria completa é substituído apenas pela lista de pessoas com perfil; DOI e links são verificados. |
| Navegação | Busca tem estados de vazio, erro e resultados; filtros podem ser removidos e a navegação mantém contexto. |
| Acessibilidade | Adotar WCAG 2.2 nível AA como objetivo; revisar manualmente teclado, foco, títulos, nomes de controles e leitor de tela nas jornadas. Auditoria automática isolada não comprova conformidade. |
| Desempenho | Definir e medir orçamento de página com imagens reais. Para operação pública, almejar Core Web Vitals bons no percentil 75: LCP ≤ 2,5 s, INP ≤ 200 ms e CLS ≤ 0,1, separando dados de campo de ensaios de laboratório. |
| Conteúdo | Páginas prioritárias passam por revisão de datas, instituição, títulos, resumos, autoria e licenças; sem blocos de preenchimento. |
| SEO e continuidade | Preservar URLs públicas válidas; mapear alterações e redirecionamentos; conferir títulos, idioma, canonical e sitemap no ambiente candidato. |
| Governança | Existem responsabilidades por revisão, calendário de manutenção e instruções simples para cadastrar cada tipo de conteúdo. |

Referências técnicas oficiais: [W3C, WCAG 2.2](https://www.w3.org/TR/WCAG22/) — versão consultada identifica recomendação de 12/12/2024; [Google, Web Vitals](https://web.dev/articles/vitals). Consulta em 06/10/2026. Estes são critérios propostos para o NERUDS, não resultados já medidos.

## Aplicação no roadmap

O benchmark alimenta o [roadmap](../planning/ROADMAP.md) de **32 issues em 8 milestones**. O estado real está nas [issues](https://github.com/rafaloct/neruds-portal/issues) e nas [milestones](https://github.com/rafaloct/neruds-portal/milestones); esta seção mapeia contribuições do benchmark e não mantém uma fila paralela.

| Milestone | Contribuição deste benchmark | Issues do plano |
|---|---|---|
| M01 — Origem confiável e cópia isolada | Proveniência na produção, inventário de conteúdo/URLs e preservação dos ativos próprios. | NERUDS-001 a NERUDS-005 |
| M02 — Drupal atualizado e compatível | Preservar acervo e comportamento ao atualizar; versões e compatibilidade pertencem à frente técnica. | NERUDS-006 a NERUDS-009 |
| M03 — Governança editorial e organização do conteúdo | Separar conceitos, autoria, datas, revisão e permissões; tratar a taxonomia heterogênea observada. | NERUDS-010 a NERUDS-012 |
| M04 — Novo design institucional e experiência pública | Identidade própria, navegação por tarefas, home editorial e vínculo institucional verificável. | NERUDS-013 a NERUDS-016 |
| M05 — Pesquisadores, projetos e acervo conectado | Modelos de conteúdo e relações; território, resultados e integridade da migração. | NERUDS-017 a NERUDS-022 |
| M06 — Descoberta, mídia e desempenho | Busca e filtros, URLs, mídia acessível e medições de experiência. | NERUDS-023 a NERUDS-026 |
| M07 — Operação recuperável e entrega reproduzível | Continuidade editorial, documentação e evidências de recuperação; implementação pertence à frente operacional. | NERUDS-027 a NERUDS-029 |
| M08 — Validação e publicação com autorização específica | Jornadas completas, revisão final de conteúdo e evidências do candidato. | NERUDS-030 a NERUDS-032 |

Não estimar prazos pelo número de páginas visíveis: o inventário da produção, a disponibilidade de arquivos e o esforço de revisão determinam o tamanho real. A criação do backlog não autoriza merge nem produção. As [jornadas mensuráveis](VISION.md) e a [Definition of Done](DEFINITION_OF_DONE.md) detalham como verificar a entrega.

## Registro das fontes e datas

Todas as URLs abaixo foram consultadas em **06/10/2026**. “Sem data exibida” significa que não foi encontrada data editorial na página extraída; não indica ausência de manutenção.

| Fonte | URL | Data editorial relevante observada |
|---|---|---|
| CEBRAP — início | https://cebrap.org.br/ | Agregador; sem data única. |
| CEBRAP — pesquisas | https://cebrap.org.br/pesquisas/ | Sem data exibida. |
| CEBRAP — núcleos | https://cebrap.org.br/nucleos/ | Sem data exibida. |
| CEBRAP — institucional | https://cebrap.org.br/institucional/ | Sem data única. |
| CEBRAP — notícia usada como exemplo editorial | https://cebrap.org.br/estudo-com-pesquisadores-do-cebrap-analisa-189-programas-de-governo-e-a-participacao-social-nas-eleicoes-de-2026/ | 30/09/2026. |
| CEBRAP — COMPLUS Caderno 4 | https://cebrap.org.br/complus-caderno-4-governanca-em-sistemas-de-saude-plurais/ | 12/08/2026. |
| Biblioteca Virtual CEBRAP | https://bibliotecavirtual.cebrap.org.br/ | Sem data exibida. |
| Cátedra de Inclusão Produtiva Rural | https://inclusaoprodutivarural.cebrap.org.br/sobre/ | Texto distingue trajetória e fase iniciada em 2024; sem data de atualização exibida. |
| Perene — início | https://www.perene.org.br/ | Agregador com novidades de 2023 visíveis na consulta. |
| Perene — projetos | https://www.perene.org.br/projetos/ | Sem data exibida. |
| Perene — comunidades | https://www.perene.org.br/comunidades/ | Sem data exibida. |
| Perene — reNascer Cerrado | https://www.perene.org.br/renascer-cerrado/ | Texto menciona início em fevereiro de 2022; não é data de atualização. |
| Perene — pilares | https://www.perene.org.br/pilares/ | Sem data exibida. |
| NERUDS — início | https://neruds.org/ | Agregador com notícias de 2025 e 2026. |
| NERUDS — sobre | https://neruds.org/sobre | Sem data exibida. |
| NERUDS — projetos | https://neruds.org/projetos | Situações e datas dentro dos registros; sem data única. |
| NERUDS — publicações | https://neruds.org/publicacoes | Anos bibliográficos dentro dos registros. |
| NERUDS — pesquisadores | https://neruds.org/pesquisadores | Sem data de revisão exibida na listagem. |
| NERUDS — linhas de pesquisa | https://neruds.org/linhas-de-pesquisa | Sem data exibida. |
| NERUDS — registro bibliográfico examinado | https://neruds.org/genero-e-poder-uma-analise-de-uma-diretoria-de-associacao-camponesa-no-norte-do-tocantins | Ano bibliográfico 2025. |
| UFT — página do núcleo | https://www.uft.edu.br/nucleos-de-pesquisa-e-extensao/nucleo-de-estudos-rurais-desigualdades-e-sistemas-socioecologicos | Sem data editorial exibida. |
| UFTDocs — Resolução Consepe 36/2021 | https://docs.uft.edu.br/share/s/VsZ8hY-7TOmffL5mzLsNZQ | Registro criado em 15/02/2022 e modificado em 24/06/2024; o documento é identificado como 36/2021. |
| W3C — WCAG 2.2 | https://www.w3.org/TR/WCAG22/ | Recomendação exibida: 12/12/2024. |
| Google — Web Vitals | https://web.dev/articles/vitals | Publicado em 04/05/2020; última atualização exibida em 31/10/2024; critérios consultados em 06/10/2026. |
