# Ambiente isolado de desenvolvimento

Esta pasta prepara uma cópia **Drupal nativa** para o repositório
`rafaloct/neruds-portal`. A origem identificada é o portal atual, com raiz
`/var/www/html` e DocumentRoot `web/`. A preparação não usa o frontend Jaspr.

O inventário informou Drupal CMS 1.2.8, core 11.2.12 e MariaDB 11.8.3.
Esses números descrevem a origem, não uma versão aprovada para publicação.
A atualização de dependências pertence à issue de atualização. A receita foi
construída e exercida na NERUDS-002 (build, `up`, verificação de isolamento e
`down`), sem restauração de dados nem bootstrap do Drupal; ver
[docs/development/local-environment.md](../../docs/development/local-environment.md).

## O que a receita contém

- PHP 8.4 com Apache, DocumentRoot `/var/www/html/web` e extensões usuais do Drupal.
- MariaDB 11.8.3 em serviço chamado `db`, sem porta publicada no host.
- Porta HTTP publicada somente em `127.0.0.1`, padrão 18086. A porta 8088 da VPS
  já é utilizada pelo OpenLiteSpeed e não deve ser reutilizada.
- Rede Compose `internal: true` para `web` e `db`, sem rede externa, volumes
  externos ou bind mounts.
- Proxy `edge` mínimo (nginx, `edge.Dockerfile` + `edge-nginx.conf`) ligado à
  rede interna e a uma bridge própria: o Docker 28 não ativa a publicação de
  porta para containers presos somente a redes `internal`, então o HTTP do
  loopback entra por esse proxy enquanto `web` e `db` permanecem sem egresso.
- Volumes próprios para banco, arquivos públicos e arquivos privados.
- Limites de 1 CPU e 768 MiB para `web`/`db` e 0.5 CPU e 128 MiB para `edge`;
  limites de processos também definidos.
- Configuração do Drupal por ambiente, sem senha ou salt embutidos e sem fallback
  para banco ou hostname de produção.
- Cron automático desativado e JSON:API em leitura.
- Backend de e-mail padrão `test_mail_collector`, com `sendmail` desativado na imagem.
- Cabeçalho `private, no-store` para requisições com Cookie e rotas
  `/user`, `/admin`, `/session` e `/jsonapi`. Os controles e contextos de cache
nativos do Drupal são preservados.

O HTTP de desenvolvimento fica restrito ao loopback. Nesta receita,
`session.cookie_secure=0` permite login nesse HTTP local; HttpOnly e SameSite=Lax
permanecem configurados. O settings recusa ambiente e hosts de produção. Uma
configuração futura de produção deverá exigir TLS e cookies Secure próprios;
esta receita de desenvolvimento não deve ser promovida a produção.

A rede interna é a barreira prevista contra chamadas externas feitas por código
herdado. Módulos com transportes próprios de e-mail, jobs e integrações precisam
ser revisados na cópia antes de testes funcionais. O coletor padrão não significa
que todas as integrações herdadas já tenham sido homologadas.

Esta infraestrutura é independente da restauração privada realizada na VPS.
Não incorporar ao Compose sockets, nomes de containers de snapshot, volumes,
`db.env`, dumps ou caminhos privados daquela restauração.

## Preparar as variáveis locais

`.env.example` contém apenas nomes e valores não secretos. Os campos de senha
e salt ficam vazios de propósito: o Compose e o bootstrap devem falhar quando
faltarem valores.

O comando abaixo é para uma etapa futura de execução local. Ele gera um arquivo
ignorado pelo Git e não imprime os valores:

~~~bash
python3 - <<'PY'
from pathlib import Path
import os
import secrets

target = Path(".env")
if target.exists():
    raise SystemExit(".env já existe; não será sobrescrito.")
text = Path(".env.example").read_text()
for name in (
    "NERUDS_DB_PASSWORD",
    "NERUDS_DB_ROOT_PASSWORD",
    "NERUDS_HASH_SALT",
):
    text = text.replace(name + "=\n", name + "=" + secrets.token_hex(32) + "\n")
target.write_text(text)
os.chmod(target, 0o600)
print(".env local criado; valores não exibidos.")
PY
~~~

Não reutilizar contas, senhas ou salt do portal publicado. Não adicionar a
`.env` destinos em `neruds.org`, inclusive subdomínios. O settings verifica o
ambiente e recusa esses valores. Aceita somente `NERUDS_ENVIRONMENT=development`,
banco no host `db`, porta 3306 e uma conta de banco diferente de root.

Os hosts HTTP aceitos são `localhost`, `127.0.0.1` e `neruds-portal.test`.
O último depende de resolução local configurada pelo operador; não há alteração
de DNS público neste procedimento.

## Verificação sem instalar ou iniciar serviços

Na raiz do repositório:

~~~bash
python3 scripts/check_repository.py
~~~

O script:

1. Examina arquivos rastreados e candidatos não ignorados pelo Git. Arquivos
   forçados ao índice continuam sujeitos ao guard, mesmo que constem no ignore.
2. Recusa arquivos típicos de credenciais, dumps, arquivos de runtime,
   settings alternativos e symlinks que precisem de revisão de importação.
3. Faz uma triagem limitada de padrões de segredo, sem imprimir valores
   encontrados.
4. Permite menções a produção em documentação e código herdado. Recusa destinos
   de produção em ENV/ARG do Dockerfile, ambiente do Compose, dotenv e ambiente
   do processo; verifica também os controles básicos da receita isolada.
5. Valida a sintaxe PHP se houver PHP instalado, sem carregar o Drupal.
6. Executa `composer --no-plugins --no-scripts validate --no-check-publish
   --no-interaction` se houver Composer, com rede desativada. Não instala,
   resolve upgrades, executa plugins ou scripts.

Se PHP ou Composer estiverem ausentes, o resultado informa **SKIP**. Isso não
equivale a aprovação desses checks. Também não comprova isolamento em execução,
compatibilidade completa, ausência de vulnerabilidades ou possibilidade de
restaurar o portal.

A opção `--skip-tools` executa somente os guards estáticos.
`--root` permite verificar uma cópia/fixture separada. O script não é um parser
completo de Compose: preserva a forma simples e explícita desta receita e exige
revisão quando sua estrutura mudar.

O CI em `.github/workflows/ci.yml` usa permissão somente de leitura e checkout
com credenciais não persistidas. A action está fixada no commit
`3d3c42e5aac5ba805825da76410c181273ba90b1`, tag oficial `v7.0.1`,
conferida pela API do repositório `actions/checkout` em 06/10/2026.
O workflow não faz deploy nem merge.

## Build e execução

Executar somente depois de revisar a importação e os arquivos de configuração
da cópia. Na NERUDS-002 estes comandos já foram exercidos com sucesso:

Usar uma estação de desenvolvimento ou builder com limites próprios comprovados.
Os limites CPU/RAM dos serviços Compose não limitam automaticamente a build.
Não executar build na VPS compartilhada sem cotas específicas do builder.

~~~bash
docker compose config --quiet
docker compose build web
docker compose up -d
~~~

A build futura instala dependências a partir do `composer.lock`, sem
`composer update` e com scripts do projeto desativados. Os plugins de instalação
e scaffold explicitamente permitidos no Composer permanecem necessários ao
layout Drupal; revisar a lista `allow-plugins` importada antes da primeira build.
Depois do scaffold, o Dockerfile copia novamente o settings seguro do repositório
e o torna somente leitura, impedindo que o artefato final use um settings gerado
ou herdado em seu lugar.
O download de imagens, pacotes PHP e Composer usa rede durante a build; a
restrição `internal` vale para os serviços em execução.

A imagem PHP usa a série 8.4 e o binário Composer usa a série 2. Esses tags podem
receber manutenção. Não alegar build reproduzível por digest antes da etapa
dedicada de entrega. A receita MariaDB fixa 11.8.3 para a cópia inicial.

O Compose cria um banco novo. Ele **não restaura automaticamente** dados ou
arquivos. Importar somente a cópia protegida aprovada, através do procedimento
de restauração correspondente. A aplicação não estará validada por simplesmente
subir os serviços.

Para encerrar serviços sem excluir dados:

~~~bash
docker compose down
~~~

Não usar `down --volumes` sem decidir expressamente pelo descarte dos dados
locais. Nenhum comando deste documento altera o domínio público ou publica uma
nova versão.

## Referências dos mecanismos usados

- [Docker Compose: redes internas](https://docs.docker.com/reference/compose-file/networks/#internal).
- [Drupal: TestMailCollector](https://api.drupal.org/api/drupal/core!lib!Drupal!Core!Mail!Plugin!Mail!TestMailCollector.php/class/TestMailCollector/11.x).
- [GitHub: referência oficial do checkout v7.0.1](https://api.github.com/repos/actions/checkout/git/ref/tags/v7.0.1).
