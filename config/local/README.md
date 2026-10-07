# Configuração local de desenvolvimento

Este diretório recebe configuração usada **somente na cópia isolada de
desenvolvimento**. Ele não é o diretório de sincronização do Drupal: o
`config_sync_directory` aponta para `config/sync` (veja
`web/sites/default/settings.php`), e nada aqui é importado por
`drush config:import`.

Regras deste diretório:

- Nunca colocar credenciais, senhas, salts, dumps ou dados de produção.
- Nunca colocar configuração exportada de produção; ajustes devem partir da
  revisão delimitada da issue correspondente.
- Arquivos colocados aqui precisam de justificativa no PR que os introduz.

Na NERUDS-002 o diretório permanece sem overrides: o ambiente ainda não executa
bootstrap do Drupal e nenhuma configuração local adicional é necessária.
