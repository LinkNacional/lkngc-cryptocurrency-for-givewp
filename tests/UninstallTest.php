<?php
/**
 * Testes da remoção do plugin (`uninstall.php`): só as opções do plugin podem
 * ser apagadas das configurações do Give.
 *
 * @package Lkngc\CryptocurrencyForGivewp\Tests
 */

namespace Lkngc\CryptocurrencyForGivewp\Tests;

use Lkngc_Give_Stub_Store;

/**
 * @coversNothing
 */
final class UninstallTest extends TestCase {
    /**
     * Executa o `uninstall.php` do plugin.
     *
     * @return void
     */
    private function run_uninstall(): void {
        if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
            define( 'WP_UNINSTALL_PLUGIN', 'lkngc-cryptocurrency-for-givewp/lkngc-cryptocurrency-for-givewp.php' );
        }

        require LKNGC_GIVE_CRYPTOCURRENCY_FOR_GIVEWP_DIR . 'uninstall.php';
    }

    public function test_apaga_apenas_as_opcoes_do_plugin(): void {
        Lkngc_Give_Stub_Store::$settings = array(
            'lkn_give_cryptocurrency_bitcoin_wallet'      => 'wallet-btc',
            'lkn_give_cryptocurrency_ethereum_wallet'     => 'wallet-eth',
            'lkn_give_cryptocurrency_enable_error_margin' => 'enabled',
            'lkn_give_cryptocurrency_debug'               => 'enabled',
            'currency'                                    => 'BRL',
            'base_country'                                => 'BR',
            'lknmp_mercadopago_access_token'              => 'nao-e-nosso',
        );

        $this->run_uninstall();

        $this->assertSame(
            array(
                'lkn_give_cryptocurrency_bitcoin_wallet',
                'lkn_give_cryptocurrency_ethereum_wallet',
                'lkn_give_cryptocurrency_enable_error_margin',
                'lkn_give_cryptocurrency_debug',
            ),
            Lkngc_Give_Stub_Store::$deleted_options
        );
    }

    public function test_sem_opcoes_do_plugin_nao_apaga_nada(): void {
        Lkngc_Give_Stub_Store::$settings = array(
            'currency'     => 'BRL',
            'base_country' => 'BR',
        );

        $this->run_uninstall();

        $this->assertSame( array(), Lkngc_Give_Stub_Store::$deleted_options );
    }

    public function test_sem_settings_do_give_nao_quebra(): void {
        Lkngc_Give_Stub_Store::$settings = null;

        $this->run_uninstall();

        $this->assertSame( array(), Lkngc_Give_Stub_Store::$deleted_options );
    }
}
