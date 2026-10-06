# Referência estrutural do Drupal NERUDS

**Esta pasta contém 570 descrições JSON de estruturas da cópia sanitizada. Ela não constitui um export completo ou importável de configuração Drupal.**

A origem é o portal atual de produção capturado em 06/10/2026, restaurado em banco próprio e sanitizado antes de qualquer bootstrap. O [registro da captura](../../operations/SNAPSHOT-2026-10-06.md) descreve o ambiente, os hashes, os controles e os limites.

## Conteúdo

| Estrutura | Arquivos |
|---|---:|
| Tipos de conteúdo | 14 |
| Vocabulários, sem termos | 38 |
| Storages de campos | 146 |
| Campos vinculados a bundles | 210 |
| Displays de formulário | 26 |
| Displays visuais | 85 |
| Views | 41 |
| Papéis | 7 |
| Seleção e configurações de tema | 3 |
| **Total** | **570** |

Cada arquivo informa `reference_only: true`, `not_a_full_importable_export: true`, o nome da configuração e sua descrição estrutural.

## Saneamento e alcance

Foram omitidos UUIDs, metadados `_core`, valores padrão de campos e blocos de texto livre que poderiam transportar conteúdo ou identificadores pessoais. E-mails e campos reconhecidos como credenciais foram neutralizados. Os papéis refletem o saneamento aplicado à cópia, incluindo retirada de permissões MCP e de bypass do papel autenticado.

Esta pasta não contém registros de conteúdo, contas, termos de taxonomia, uploads, sessões, tokens, filas, submissões de formulários, estado completo do site, dumps ou arquivos de ambiente.

O scanner aplicado à referência encontrou zero ocorrências das assinaturas de segredos, e-mails reais e caminhos privados de produção examinados. Os 570 hashes individuais foram conferidos novamente após a transferência. Essa verificação delimitada não substitui a revisão de uma mudança futura de configuração.

## Como utilizar

Use os arquivos para localizar entidades, entender dependências e preparar uma alteração delimitada. A configuração que vier a ser importada deverá ser produzida e validada no ambiente de trabalho isolado, seguindo [AGENTS.md](../../../AGENTS.md) e o contrato de configuração atômica.

**Não aponte `drush config:import` para esta pasta.** Os arquivos usam um envelope JSON de documentação, têm omissões intencionais e não abrangem todas as dependências do site.

## Integridade

O [manifesto](manifest.json) lista arquivos, tamanhos e SHA-256 individuais. O hash agregado é:

```text
9f64a69f2a1f878eeaa92a7aa058433540d0888ec343acb747cd8894cf139ab6
```

O agregado é calculado na ordem do manifesto, concatenando nome de arquivo, byte NUL, SHA-256 do arquivo e quebra de linha. Os arquivos de apoio README e manifesto ficam fora desse agregado.

