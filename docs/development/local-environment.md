# Ambiente local isolado de desenvolvimento

Guia operacional da cópia isolada preparada na NERUDS-002. A receita está em
[`infra/dev/README.md`](../../infra/dev/README.md); este documento cobre o
uso, a verificação de isolamento e os limites do que o ambiente comprova.

## Limites

- O ambiente é **somente desenvolvimento local**. Não é staging, não replica
  produção e não deve ser promovido a produção.
- Nenhum banco, volume, diretório, credencial ou integração de produção é
  usado. O snapshot privado preservado pertence à restauração da NERUDS-003 e
  não é montado aqui.
- Subir os serviços não valida a aplicação. Restauração, bootstrap Drupal e
  testes funcionais pertencem às issues seguintes.

## Pré-requisitos

- Docker Desktop com engine Linux (contexto `desktop-linux`).
- `.env` local gerado a partir de `.env.example`, com os três valores
  (`NERUDS_DB_PASSWORD`, `NERUDS_DB_ROOT_PASSWORD`, `NERUDS_HASH_SALT`)
  criados localmente — nunca reutilizados de produção, nunca versionados.
- PHP e Composer não são necessários no host; a receita usa containers.

## Operação

Na raiz do repositório, usando o Compose do projeto (`-p` e `--env-file`
explícitos ou o wrapper local equivalente):

~~~bash
docker compose --env-file .env config --quiet   # valida sem interpolar segredos
docker compose --env-file .env build web        # instala composer.lock exato
docker compose --env-file .env up -d            # sobe db + web
docker compose --env-file .env ps               # estado dos serviços
docker compose --env-file .env down             # encerra, preservando volumes
~~~

Não usar `down --volumes` sem decisão explícita sobre descartar os dados
locais. O HTTP fica em `127.0.0.1:${NERUDS_HTTP_PORT}` (padrão 18086; a
worktree do Devin usa 18087 via wrapper `neruds-compose`). O banco **não**
publica porta no host.

## O que o ambiente garante

| Controle | Mecanismo |
|---|---|
| Sem destino de produção | `settings.php` rejeita `neruds.org` em qualquer variável de ambiente, exige `NERUDS_ENVIRONMENT=development`, host `db:3306` e conta não-root |
| Rede isolada | `web` e `db` somente na rede Compose `internal: true`; sem DNS/egress externo em runtime |
| Entrada HTTP | proxy `edge` (nginx) entre a rede interna e uma bridge própria, pois o Docker 28 não publica portas de containers presos a redes `internal`; único serviço nas duas redes |
| Sem binds de produção | somente volumes nomeados próprios do projeto; nenhum bind mount |
| Portas restritas | HTTP somente em `127.0.0.1` via `edge`; `web` e `db` sem porta publicada |
| E-mail | `sendmail_path=/bin/false` na imagem; Drupal usa `test_mail_collector`; envio externo é impossível na rede interna |
| Jobs/integrações | `automated_cron` desligado e JSON:API em leitura por override; revisão por módulo continua na restauração |
| Recursos | 1 CPU / 768 MiB por serviço, `pids_limit`, `no-new-privileges`, `restart: "no"` |
| Cache/sessão | `private, no-store` com Cookie e em `/user`, `/admin`, `/session`, `/jsonapi`; sem cache compartilhado de respostas autenticadas |

## Verificação de isolamento

Executar com os serviços no ar (`up -d`):

~~~bash
# 1. Mounts e volumes: apenas volumes nomeados do projeto.
docker inspect <proj>-web-1 --format '{{json .Mounts}}'
docker inspect <proj>-db-1  --format '{{json .Mounts}}'

# 2. Rede interna e ausência de egresso externo (deve falhar/timeout).
docker inspect <proj>-web-1 --format '{{json .NetworkSettings.Networks}}'
docker exec <proj>-web-1 php -r 'var_dump(@file_get_contents("https://example.org/"));'

# 3. E-mail bloqueado: sendmail desabilitado e coletor de teste como backend.
docker exec <proj>-web-1 php -r 'var_dump(ini_get("sendmail_path"));'
docker exec <proj>-web-1 php -r 'var_dump(mail("probe@example.invalid","t","b"));'

# 4. Limites de recursos e opções de segurança.
docker inspect <proj>-web-1 --format 'cpu={{.HostConfig.NanoCpus}} mem={{.HostConfig.Memory}} pids={{.HostConfig.PidsLimit}}'
docker inspect <proj>-db-1  --format 'cpu={{.HostConfig.NanoCpus}} mem={{.HostConfig.Memory}} pids={{.HostConfig.PidsLimit}}'

# 5. Configuração efetiva sem segredos: host, porta e nome do banco.
docker exec <proj>-web-1 printenv NERUDS_DB_HOST NERUDS_DB_PORT NERUDS_DB_NAME NERUDS_ENVIRONMENT

# 6. HTTP somente no loopback do host.
curl -sS -o /dev/null -w '%{http_code}\n' http://127.0.0.1:${NERUDS_HTTP_PORT:-18086}/
~~~

## Encerramento

`docker compose ... down` remove os containers e a rede do projeto,
preservando os volumes nomeados. O serviço publicado (neruds.org) não é
tocado: ele está em outra infraestrutura e este ambiente não tem rota,
credencial ou volume para alcançá-lo.

## Pendências conhecidas

- O proxy `edge` tem acesso externo por estar na bridge `edge`; ele só encaminha
  HTTP para `web`. Se um transporte de saída for exigido no futuro (ex.: coletor
  SMTP dedicado), a política deve ser explícita na issue correspondente.
- Integrações herdadas (Google/OAuth/BigQuery, IA/MCP, formulários, analytics,
  envio por módulos com transporte próprio) são neutralizadas por camada de
  rede/e-mail; a confirmação por módulo ocorre na restauração (NERUDS-003) e
  na revisão de código (NERUDS-009).
- A CI hospedada depende da regularização da conta; o guard local
  (`scripts/check_repository.py`) é a verificação disponível.
