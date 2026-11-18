# Testes — Cryptocurrency Payment for GiveWP

Suíte de testes integrados (`wp-phpunit`) do plugin. O WordPress real é carregado
a partir da instalação do Local WP; o **GiveWP não é carregado** (ele traz as
próprias tabelas e um bootstrap de testes pesado). No lugar dele usamos stubs da
superfície usada pelo plugin em `tests/stubs/give-stubs.php`, com um store em
memória que cada teste inspeciona (`Lkngc_Give_Stub_Store`).

## Pré-requisitos

- Banco de teste `local_tests` criado no MySQL do Local (Adminer/DBeaver).
  **Nunca** use o banco do site de desenvolvimento: as tabelas `wptests_*` são
  recriadas a cada execução.
- `composer install` feito no plugin (traz `phpunit`, `wp-phpunit`, polyfills).

## Como rodar

O `php` do sistema não serve (sem `mysqli`). Use o binário CLI do Local com
`LD_LIBRARY_PATH` e o socket do MySQL:

```bash
cd wp-content/plugins/lkngc-cryptocurrency-for-givewp

PHPDIR=/opt/Local/resources/extraResources/lightning-services/php-8.2.29+0
export LD_LIBRARY_PATH="$PHPDIR/bin/linux/shared-libs:$PHPDIR/bin/linux/lib"
export WP_TESTS_DB_SOCKET="$HOME/.config/Local/run/2R7ojtDtH/mysql/mysqld.sock"
export WP_PHP_BINARY="$PHPDIR/bin/linux/bin/php"

"$PHPDIR/bin/linux/bin/php" \
  -d mysqli.default_socket="$WP_TESTS_DB_SOCKET" \
  -d memory_limit=2G \
  vendor/bin/phpunit
```

Filtros úteis:

```bash
vendor/bin/phpunit --filter CreatePaymentTest      # uma classe
vendor/bin/phpunit --filter test_id_do_gateway     # um teste
```

O `2R7ojtDtH` é o id do site `ambiente-dev` (veja `~/.config/Local/sites.json`);
para outro site, troque o id.

### Cobertura

O `phpunit.xml` já aponta as pastas de código (`Includes`, `Admin`, `Public`).
Falta um driver de cobertura no CLI do Local; habilite Xdebug para o PHP do Local
e rode:

```bash
vendor/bin/phpunit --coverage-text
```

## Estrutura

| Arquivo | Cobre |
| --- | --- |
| `EnvironmentTest.php` | fumaça: WP de teste, stubs do Give e carregamento do plugin |
| `HelperTest.php` | `get_configs()`, `get_wallets()`, link "Configurações", aviso de dependência e log |
| `CryptoApiQueryTest.php` | URL/args da consulta à BlockCypher e falha da API |
| `TransactionVerificationTest.php` | regra de confirmação (carteira, valor, margem de erro, BTC/ETH, limite de 5 transações) |
| `RestNotificationTest.php` | rota REST do botão "Consult Transaction" (token, confirmação, erros) |
| `AdminSettingsTest.php` | seção de configurações no Give, enqueue do admin e bloco de detalhes da doação |
| `PaymentGatewayTest.php` | IDs/nomes dos gateways, markup do formulário clássico e assets |
| `CreatePaymentTest.php` | `createPayment()`: pendente, completo, valor divergente e falhas |
| `PublicAssetsTest.php` | enqueue de CSS/JS do front e carteiras localizadas |
| `PluginCoreTest.php` | constantes, loader, hooks registrados, i18n e ativação/desativação |
| `UninstallTest.php` | `uninstall.php` apaga apenas as opções do plugin |

## Notas

- O idioma é forçado para `en_US` no bootstrap (`wp_tests_options['WPLANG']`),
  senão as mensagens traduzidas variam conforme o `WPLANG` que ficou no banco de
  teste.
- O mock de HTTP é o filtro `pre_http_request` (`TestCase::mock_http()`); sem mock
  qualquer requisição vira `WP_Error`, o que permite testar "API fora do ar".
- Estes testes **não** vão para o pacote: o workflow de release só copia
  `Admin`, `Includes`, `languages`, `Public` e os arquivos da raiz.
