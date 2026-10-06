# Visão de produto — Portal NERUDS

Data de referência: 06/10/2026. Esta visão orienta o novo portal; não descreve funcionalidades já entregues.

## Propósito

Tornar o conhecimento do NERUDS encontrável, compreensível e útil para quem pesquisa, aprende, atua em cooperativas e comunidades ou formula políticas públicas. O portal conecta pesquisa e extensão e permite entender quais perguntas o núcleo investiga, com quem trabalha, em quais territórios atua e quais resultados compartilha.

A origem é o NERUDS de produção e seu vínculo com a UFT. O trabalho com outras universidades, instituições e organizações deve aparecer conforme registros e validação institucional, sem inferir afiliação formal a partir de biografias individuais.

O foco editorial contempla cooperativismo, organização coletiva, extensão rural, inclusão produtiva, desigualdades e sistemas socioecológicos. Territórios, comunidades, saberes locais e métodos de pesquisa devem ter presença concreta nos projetos e materiais. O portal deve acolher o vocabulário acadêmico e oferecer explicações acessíveis quando necessário.

## Resultado pretendido

Um portal Drupal nativo, com novo design próprio, páginas legíveis em celular, busca útil, acervo conectado e um fluxo editorial que a equipe consiga manter. Qualidade comparável aos referenciais significa confiança, descoberta e continuidade editorial, demonstradas nas jornadas; não depende de copiar aparência, escala de equipe ou frequência de publicação de outra instituição.

O [benchmark](benchmark.md) documenta referências e limites. O [ADR-001](../architecture/ADR-001-drupal-native.md) registra a escolha de Drupal nativo. O [roadmap](../planning/ROADMAP.md) organiza as entregas em oito milestones e 32 issues.

## Públicos e quatro jornadas mensuráveis

As metas abaixo são **critérios de aceite propostos**, não resultados medidos. Validar com conteúdo real ou cópia sanitizada representativa, em celular e desktop, registrando tarefa, passos, resultado e dificuldades. Se houver avaliação com participantes, registrar perfil, quantidade e condições; não transformar uma pequena amostra em afirmação estatística sobre todo o público.

| Jornada | Tarefa concreta | Medida de conclusão | Evidência esperada |
|---|---|---|---|
| J1 — Cooperativa, associação ou comunidade | Partindo da home, encontrar um projeto relevante por tema ou território e abrir seu material público ou contato institucional. | Concluir em até três transições de página após escolher a entrada temática/territorial; identificar situação, local e equipe sem login. | Percurso registrado, links válidos e verificação em celular e teclado; material ou contato real disponível. |
| J2 — Pesquisador ou estudante | Localizar uma publicação conhecida por título parcial ou autor, identificar autoria completa e abrir DOI/documento. | A amostra de cinco publicações, incluindo uma antiga, deve ser recuperável pelas duas formas de busca; os cinco registros preservam ordem de autoria, ano e destino correto. | Matriz de consultas/resultados e comparação com a referência de origem; nenhum cadastro obrigatório para conteúdo público. |
| J3 — Gestor ou possível parceiro | Escolher um projeto e entender problema, método, território, período, equipe, parceiros e resultados. | Os sete elementos estão presentes ou explicitamente indicados como informação ainda não disponível nos três projetos da amostra; meta futura nunca aparece como resultado realizado. | Ficha editorial dos três projetos e origem das informações; contato institucional identificável. |
| J4 — Jornalista ou público interessado | Ler uma notícia de pesquisa, compreender o principal achado e acessar sua evidência. | Nas três notícias de amostra, encontrar data editorial, autoria da notícia, achado, contexto/limitação e link para estudo ou resultado; chegar à evidência em um acionamento a partir da notícia. | Revisão editorial com fonte; título/link compreensível e acesso sem depender de mídia incorporada. |

Um conteúdo que ainda não existe deve ser apontado como lacuna editorial, com responsável a definir; não será fabricado para satisfazer um teste. Uma alteração justificada nas metas deve ser registrada na issue correspondente antes de declarar a jornada aprovada.

## Navegação proposta

- **O NERUDS:** propósito, história, vínculo institucional, organização, parceiros e documentos públicos.
- **Pesquisa e extensão:** projetos, grupos e eixos, com períodos e situações claros.
- **Territórios:** coleções editoriais ligadas a locais e regiões em que há atuação documentada.
- **Publicações e materiais:** produção científica, relatórios, cartilhas, notas e materiais audiovisuais existentes.
- **Pessoas:** pesquisadores, estudantes e colaboradores, com função e vínculos atuais.
- **Notícias e agenda:** comunicação datada, atividades e oportunidades confirmadas.

Busca e contato devem ser acessíveis globalmente. A navegação será validada nas issues de taxonomia e design; não criar páginas vazias para preencher o menu. ODS, territórios, eixos, grupos e projetos não são categorias intercambiáveis.

## Princípios de experiência

1. A página explica sua função antes de exibir listas de campos. Coleções têm resumos; detalhes preservam o conteúdo completo.
2. Pessoas, projetos e publicações se relacionam por dados, evitando cópias divergentes de uma mesma biografia ou descrição.
3. Conteúdo antigo permanece encontrável. URLs úteis são preservadas ou ganham redirecionamentos documentados.
4. Páginas funcionam com leitura linear, teclado e conexão limitada. Imagens, vídeo e mapas complementam uma explicação textual.
5. A identidade visual parte de ativos autorizados do NERUDS e é desenvolvida em tema Drupal. O protótipo Jaspr anterior não será reutilizado como base visual.
6. Fotografia documental e créditos representam trabalho real. Não usar imagens fictícias como registro de pessoas, projetos ou resultados.
7. A equipe editorial dispõe de rascunho, revisão, publicação e arquivamento, com permissões por ação e conteúdo.

## Escopo do primeiro portal

Entram: base isolada e reproduzível; atualização segura do Drupal na cópia; organização editorial e permissões; novo tema; páginas institucionais; pessoas, projetos, publicações e acervo; atuação territorial; busca; mídia; preservação de URLs; acessibilidade; desempenho medido; recuperação e pacote revisável de lançamento.

Ficam fora: retomada de Jaspr; aplicativo/PWA; IA/RAG ou agentes editoriais autônomos; marketplace, monetização e certificados; campanhas e comunicação a terceiros; novos serviços pagos sem decisão própria; mudança global de VPS; uso de contas reais nos testes; alteração de produção nesta fase. Componentes herdados não relacionados ao objetivo devem ser inventariados e avaliados, não acionados apenas por estarem instalados.

## Sucesso e entrega

O produto será avaliado pelo cumprimento das quatro jornadas, integridade do acervo, capacidade editorial, acessibilidade e condições de operação. A [Definition of Done](DEFINITION_OF_DONE.md) separa conclusão de uma issue, validação de uma candidata e autorização de publicação.

Não há calendário prometido neste documento. Dependências, evidências e escopo determinam quando uma entrega está pronta; o andamento real está no GitHub.
