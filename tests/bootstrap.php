<?php
/**
 * Bootstrap dos testes integrados (wp-phpunit) do
 * Cryptocurrency Payment for GiveWP.
 *
 * Fluxo:
 *   1. autoload do plugin (Composer)
 *   2. wp-tests-config.php (constantes de DB + ABSPATH → WordPress do Local WP)
 *   3. `muplugins_loaded` → carrega os stubs do GiveWP e o plugin ANTES do
 *      `init`, para que os hooks (REST, settings, gateways) se registrem.
 *   4. bootstrap do wp-phpunit (instala o banco de teste e carrega o WP)
 *
 * REGRA DE OURO: os stubs do GiveWP DEVEM existir antes do plugin ser incluído.
 *
 * @package Lkngc\CryptocurrencyForGivewp\Tests
 */

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// 1. Arquivo de configuração dos testes.
if ( ! defined( 'WP_TESTS_CONFIG_FILE_PATH' ) ) {
    define( 'WP_TESTS_CONFIG_FILE_PATH', __DIR__ . '/wp-tests-config.php' );
}

// 2. Polyfills de PHPUnit (Yoast).
if ( ! defined( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH' ) ) {
    define(
        'WP_TESTS_PHPUNIT_POLYFILLS_PATH',
        dirname( __DIR__ ) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php'
    );
}

$lkngc_plugin_dir = dirname( __DIR__ );

/*
 * Idioma fixo em en_US: o banco de testes pode vir com `WPLANG` = pt_BR de uma
 * execução anterior (o installer não reseta a opção), e isso faria as
 * asserções de mensagens (`__()`) variarem entre execuções.
 */
$GLOBALS['wp_tests_options'] = array_merge(
    isset( $GLOBALS['wp_tests_options'] ) ? $GLOBALS['wp_tests_options'] : array(),
    array( 'WPLANG' => '' )
);

// functions.php define `tests_add_filter()`, que permite plugar código antes do
// carregamento do WordPress.
require_once $lkngc_plugin_dir . '/vendor/wp-phpunit/wp-phpunit/includes/functions.php';

tests_add_filter(
    'muplugins_loaded',
    function () use ( $lkngc_plugin_dir ) {
        // 3. Stubs do GiveWP primeiro: o plugin usa `give_*` e `Give\...` no
        // carregamento (inclusive na construção das classes).
        require_once $lkngc_plugin_dir . '/tests/stubs/give-stubs.php';

        // O bootstrap do plugin faz `require_once 'vendor/autoload.php'`
        // (caminho relativo), então o CWD precisa ser a raiz do plugin.
        $lkngc_previous_cwd = getcwd();
        chdir( $lkngc_plugin_dir );

        require_once $lkngc_plugin_dir . '/lkngc-cryptocurrency-for-givewp.php';

        chdir( $lkngc_previous_cwd );
    }
);

require_once $lkngc_plugin_dir . '/vendor/wp-phpunit/wp-phpunit/includes/bootstrap.php';
