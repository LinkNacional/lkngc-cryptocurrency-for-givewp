<?php
/**
 * Configuração dos testes integrados (wp-phpunit) do
 * Cryptocurrency Payment for GiveWP.
 *
 * Carregado pelo pacote `wp-phpunit/wp-phpunit`. O banco `local_tests` precisa
 * existir (Local WP → Adminer/DBeaver).
 *
 * IMPORTANTE: não use o mesmo banco do site de desenvolvimento. Os testes
 * recriam as tabelas (prefixo `wptests_`) e apagam os dados existentes.
 *
 * @package Lkngc\CryptocurrencyForGivewp\Tests
 */

/* -------------------------------------------------------------------------- */
/*  Detecção automática do WordPress do Local WP                              */
/* -------------------------------------------------------------------------- */

if ( ! defined( 'ABSPATH' ) ) {
    // Este arquivo vive em `plugins/{plugin}/tests/`. Sobe 4 níveis até
    // `app/public` (tests → plugin → plugins → wp-content → public).
    $lkngc_candidate_root = dirname( __DIR__, 4 );

    if ( ! file_exists( $lkngc_candidate_root . '/wp-load.php' ) ) {
        $lkngc_candidate_root = getenv( 'LOCAL_WP_PATH' ) ?: dirname( __DIR__, 4 );
    }

    define( 'ABSPATH', rtrim( $lkngc_candidate_root, '/' ) . '/' );
}

/* -------------------------------------------------------------------------- */
/*  Banco de dados de teste (local_tests)                                     */
/* -------------------------------------------------------------------------- */

define( 'DB_NAME', getenv( 'WP_TESTS_DB_NAME' ) ?: 'local_tests' );
define( 'DB_USER', getenv( 'WP_TESTS_DB_USER' ) ?: 'root' );
define( 'DB_PASSWORD', getenv( 'WP_TESTS_DB_PASSWORD' ) ?: 'root' );

// Local WP: usa socket quando informado, senão localhost.
$lkngc_socket_path = getenv( 'WP_TESTS_DB_SOCKET' ) ?: '';
if ( $lkngc_socket_path && file_exists( $lkngc_socket_path ) ) {
    // O formato "host:socket" é aceito pelo wpdb e sobrevive a subprocessos
    // (o instalador do wp-phpunit roda em um `php` separado).
    define( 'DB_HOST', 'localhost:' . $lkngc_socket_path );
} else {
    define( 'DB_HOST', getenv( 'WP_TESTS_DB_HOST' ) ?: 'localhost' );
}

define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

$table_prefix = 'wptests_';

/* -------------------------------------------------------------------------- */
/*  Constantes obrigatórias do wp-phpunit                                     */
/* -------------------------------------------------------------------------- */

define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Test Blog' );
define( 'WP_PHP_BINARY', getenv( 'WP_PHP_BINARY' ) ?: 'php' );
