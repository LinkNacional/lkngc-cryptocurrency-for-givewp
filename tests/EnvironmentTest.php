<?php
/**
 * Teste de fumaça do harness: garante que o WordPress de teste carregou, que os
 * stubs do GiveWP existem e que o plugin foi incluído.
 *
 * @package Lkngc\CryptocurrencyForGivewp\Tests
 */

namespace Lkngc\CryptocurrencyForGivewp\Tests;

/**
 * @coversNothing
 */
final class EnvironmentTest extends TestCase {
    public function test_wordpress_de_teste_carregou(): void {
        $this->assertTrue( function_exists( 'wp_hash' ) );
        $this->assertSame( 'wptests_', $GLOBALS['table_prefix'] );
    }

    public function test_stubs_do_give_existem(): void {
        $this->assertTrue( function_exists( 'give_get_option' ) );
        $this->assertTrue( function_exists( 'give_update_payment_meta' ) );
        $this->assertTrue( class_exists( 'Give\\Donations\\Models\\Donation' ) );
        $this->assertTrue( class_exists( 'Give\\Framework\\PaymentGateways\\PaymentGateway' ) );
    }

    public function test_plugin_carregou(): void {
        $this->assertTrue( defined( 'LKNGC_GIVE_CRYPTOCURRENCY_FOR_GIVEWP_VERSION' ) );
        $this->assertSame( '4.0.4', LKNGC_GIVE_CRYPTOCURRENCY_FOR_GIVEWP_VERSION );
        $this->assertTrue( function_exists( 'lkngc_cryptocurrency_for_givewp_run' ) );
        $this->assertTrue( class_exists( 'Lkngc\\CryptocurrencyForGivewp\\Includes\\LkngcCryptocurrencyForGivewp' ) );
    }
}
