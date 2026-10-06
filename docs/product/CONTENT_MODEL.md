# Modelo de conteúdo e governança editorial

Este documento especifica o produto pretendido. A implementação deve conferir bundles, campos, taxonomias, Views e permissões da cópia antes de renomear, remover ou migrar qualquer estrutura. Os nomes abaixo são conceitos editoriais, não machine names novos já aprovados.

## Tipos e relações

| Tipo | Informação mínima para publicação | Relações úteis |
|---|---|---|
| Projeto de pesquisa ou extensão | Título, resumo público, problema, objetivos, método, período, situação, território, equipe, resultados ou estágio atual, atualização e contato. Parceiros/financiamento quando verificados e públicos. | Pessoas, grupo, eixos, territórios, materiais, notícias e eventos. |
| Publicação ou material | Título canônico, autoria completa ordenada, ano, tipo, resumo, veículo quando pertinente, DOI/URL ou arquivo autorizado, idioma e referência para citação. | Pessoas com perfil, projetos, eixos e territórios. |
| Pessoa | Nome de exibição, função no núcleo, vínculo atual, biografia curta/completa, temas, foto autorizada com crédito e links acadêmicos conferidos. | Projetos, grupos e produção; histórico de colaboração quando pertinente. |
| Grupo de pesquisa/estudos | Nome, escopo, coordenação, participantes, período/situação e forma de participação quando aberta. | Pessoas, projetos, eixos e materiais. |
| Território | Nome, recorte geográfico compreensível, contexto, atuação documentada e organizações participantes quando públicas. | Projetos, materiais e resultados; mapa somente com dados apropriados. |
| Notícia | Título informativo, resumo, data de publicação, data do fato quando distinta, atualização, autoria editorial, corpo, fontes e mídia com legenda/crédito. | Projeto, publicação, pessoa, evento e território. |
| Evento/oportunidade | Título, descrição, data/horário e fuso, local/modalidade, público, situação, prazo e inscrição quando aberta. | Projeto, pessoas e registro posterior da atividade. |
| Página institucional | Finalidade, conteúdo validado, fontes institucionais e responsável/data de revisão. | Documentos públicos, organização e contato. |

## Regras que preservam o acervo

- **Autoria completa não depende de ter perfil no portal.** Manter autores externos e ordem original; vincular perfis locais quando existirem.
- **Autoria científica, autoria editorial e conta de cadastro são distintas.** A conta técnica que importou conteúdo não deve virar autor da obra ou da notícia.
- **Tema, eixo, grupo, projeto, território e ODS são conceitos diferentes.** Produzir tabela de equivalência antes de reorganizar a taxonomia herdada; preservar IDs/relações ou documentar sua migração.
- **Título bibliográfico é um dado.** Conferir maiúsculas, pontuação, caracteres estranhos e DOI com a fonte antes da correção. Não aplicar transformações destrutivas para atender ao layout.
- **Meta e resultado são informações diferentes.** Indicadores precisam de unidade, período, fonte e responsável. Não deduzir impacto pelo número de registros cadastrados.
- **Datas têm significado próprio.** Diferenciar acontecimento, publicação, atualização, início/fim do projeto e ano bibliográfico. Data de captura/importação não substitui as demais.
- **Uma imagem ou arquivo tem acesso e contexto.** Registrar título, crédito/uso, alternativa textual, formato/tamanho e condição pública/privada. Preservar o original autorizado e suas referências.
- **Resumo e corpo completo são campos de apresentação distintos.** Cartões não devem repetir biografias inteiras; evitar armazenamento duplicado para cada modo de exibição.
- **Endereços fazem parte da continuidade.** Preservar aliases públicos válidos; toda mudança necessita mapa de redirecionamento e conferência de referências.

## Fluxo editorial pretendido

| Estado | Finalidade | Condição para avançar |
|---|---|---|
| Rascunho | Elaborar ou migrar conteúdo sem expor uma versão incompleta. | Campos mínimos e fontes disponíveis para revisão. |
| Em revisão | Conferir informação, linguagem, autoria, mídia e vínculos. | Revisor autorizado registra revisão e resolve ou explicita lacunas. |
| Publicado | Disponibilizar a versão aprovada ao público correspondente. | Permissão de publicação, metadados e regras de acesso atendidos. |
| Arquivado | Preservar histórico de uma atividade encerrada ou conteúdo substituído. | Motivo e destino claros; URL e citação preservadas quando cabível. |

Os papéis concretos serão configurados nas issues NERUDS-011 e NERUDS-012. Separar capacidade de criar, revisar, publicar, administrar contas e administrar o site. Testar ações permitidas e negadas, inclusive por acesso direto e troca de sessão. A definição de um fluxo no documento não significa que suas permissões já estejam implementadas.

## Responsabilidade e revisão

A coordenação do núcleo valida informações institucionais; responsáveis por projetos e autores conferem conteúdo de sua competência; revisão editorial confere apresentação, fontes e consistência. Os nomes e atribuições devem ser registrados pela equipe, sem atribuir funções por suposição.

Cada página permanente deve ter responsável e data de revisão. Uma mudança de função, encerramento de projeto ou correção bibliográfica gera revisão pontual. A cadência de manutenção será acordada conforme a capacidade da equipe; não inventar datas de entrega nem frequência de publicação para preencher o calendário.

## Lacunas a tratar a partir do benchmark

Conferir na cópia: taxonomia com níveis misturados; cartões extensos; título bibliográfico com caracteres `¿`; resumo possivelmente duplicado na extração da home; diferenças entre data do fato e de publicação; separação entre perfil de um autor e lista completa da obra. O [benchmark](benchmark.md) contém as URLs de evidência e distingue observações de extração de defeitos visuais ainda não confirmados.

Conteúdo sem evidência suficiente fica em revisão ou é omitido da versão pública, conforme o caso. O agente entrega a lacuna concreta e continua outras partes autorizadas; não inventa validação da coordenação nem interrompe toda a tarefa por uma decisão editorial independente.
