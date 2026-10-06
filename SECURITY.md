# Segurança e dados de desenvolvimento

Este repositório começa com uma seleção do portal em produção em 06/10/2026. O core e as dependências ainda precisam da atualização descrita no milestone M02. Importação, scanner ou CI aprovados não certificam a segurança da aplicação.

Relate problemas com evidências sanitizadas em uma issue do repositório privado. Não publique credenciais, dados de contas, dumps ou detalhes de exploração em canais públicos. Se algum segredo for identificado, interrompa sua inclusão em commits e comunique a localização sem reproduzir o valor.

O diretório de desenvolvimento contém código e documentos. Banco, arquivos enviados, sessões, chaves e backups ficam em armazenamento separado. O banco restaurado deve ter contas bloqueadas/anonimizadas e integrações neutralizadas antes de qualquer execução do Drupal.

O ambiente de desenvolvimento usa hosts locais, rede interna, credenciais próprias, e-mail de teste e cron automático desligado. Não conectar APIs, OAuth, MCP, IA ou serviços reais para satisfazer um teste. Não copiar settings de produção.

O único destino autorizado para código de desenvolvimento é este repositório privado. Publicar uma versão do portal depende de candidato identificado, atualização das dependências, autorização específica e plano de backup/reversão.

Consulte [AGENTS.md](AGENTS.md), [infraestrutura de desenvolvimento](infra/dev/README.md), [roadmap](docs/planning/ROADMAP.md) e as evidências de bootstrap em `docs/operations/`.
