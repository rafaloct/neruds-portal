# Definition of Done

Esta definição organiza evidências de conclusão. Não concede autorização de merge ou publicação e não substitui os critérios específicos da issue.

## Uma issue está pronta para revisão quando

- O objetivo único foi cumprido e o diff respeita os caminhos/limites combinados. Alterações incidentais foram removidas ou justificadas dentro do escopo.
- A implementação usa a base Drupal acordada e preserva o que a tarefa não precisava alterar.
- Cada critério de aceite tem evidência correspondente ao candidato real; uma lacuna continua identificada como pendência.
- Os testes focais e checks pertinentes foram executados. O registro informa ambiente, resultado e limitações; testes impossíveis de executar não são apresentados como aprovados.
- Configuração Drupal relacionada está completa no mesmo PR, com dependências e importação conferidas quando afetadas. O diff não contém exportações alheias nem valores específicos de produção.
- Segredos, dumps, dados privados, contas reais, sessões e credenciais estão ausentes do Git, logs e artefatos da entrega.
- Mudanças de conteúdo preservam autoria, datas, links e relações; permissões e cache foram verificados quando afetados.
- Documentação necessária ao comportamento e à manutenção foi atualizada sem prometer funcionalidades futuras como prontas.
- O PR identifica issue, problema, mudança, comportamento resultante, testes, limites e sobreposições relevantes. O GitHub registra o estado real, dependências e próximo passo.

Revisão concluída e autorização de merge são etapas distintas. A issue só deve ser encerrada conforme o resultado integrado e os critérios acordados; um PR aberto ou documento criado não prova a entrega completa. A evolução não inicia automaticamente outra tarefa.

## Uma candidata está validada para decisão de publicação quando

| Área | Evidência mínima |
|---|---|
| Identidade da candidata | Commit, artefato/digest quando aplicável, lockfiles, configuração e ambiente registrados e coerentes com os testes. |
| Atualização | Matriz de versões e dependências validada na execução; core, contribuições e código próprio compatíveis no ambiente isolado. |
| Conteúdo | Migração conferida por contagens e amostras; autoria, arquivos, relações e URLs relevantes preservados; lacunas institucionais resolvidas ou retiradas da publicação. |
| Jornadas | J1 a J4 da [visão de produto](VISION.md) testadas com amostras e evidências; não apenas screenshots da home. |
| Editorial | Criar, revisar, publicar e arquivar funcionam para papéis sintéticos autorizados; ações negadas continuam negadas. |
| Acessibilidade | Revisão manual de teclado, foco, semântica, idioma, formulários e leitura dos percursos; ferramenta automática usada como apoio; bloqueadores corrigidos. Objetivo: WCAG 2.2 AA. |
| Desempenho | Medições reproduzíveis de páginas representativas, condições registradas e orçamento da issue atendido; separar laboratório de dados de campo. |
| Privacidade e acesso | Arquivos privados, permissões e cache verificados; nenhum envio ou integração real acionado nos testes. |
| Operação | Recuperação ensaiada em cópia descartável; versão identificável; diagnóstico e runbooks permitem responder a falhas essenciais. |
| Continuidade editorial | Plano para incorporar alterações ocorridas na produção desde a captura, sem sobrescrevê-las silenciosamente. |
| Publicação | Procedimento concreto de banco/arquivos/roteamento, backup, verificações, critérios de abortar e reversão preparados para revisão. |

Os critérios técnicos e suas fontes estão no [benchmark](benchmark.md). Uma pendência material de segurança, integridade ou acesso bloqueia a declaração de prontidão. Não substituir evidências por uma lista de caixas marcadas.

## Autorização e execução

1. **Merge:** requer autorização específica do usuário para o PR/candidato. Checks aprovados e estado `agent:review` não a substituem.
2. **Produção:** requer autorização específica posterior para a versão e o procedimento concretos. A preparação do repositório, das issues ou do pacote não concede essa autorização.
3. **Mudança posterior:** se o candidato ou o procedimento aprovado mudar materialmente, a autorização anterior não cobre automaticamente a mudança.
4. **Após publicação autorizada:** registrar versão, horário, verificações e resultado, incluindo eventual reversão e pendências, sem expor credenciais.

Não há auto-merge, deploy automático nem execução recorrente da fila. O bootstrap inicial autorizado permanece a exceção delimitada de criação da base em `main`, sem exigir PR retroativo.
