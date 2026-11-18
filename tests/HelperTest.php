<?php
/**
 * Testes do helper (configurações, carteiras, links do plugin e logs).
 *
 * @package Lkngc\CryptocurrencyForGivewp\Tests
 */

namespace Lkngc\CryptocurrencyForGivewp\Tests;

use Lkngc\CryptocurrencyForGivewp\Includes\LkngcCryptocurrencyForGivewpHelper;
use Lkngc_Give_Stub_Store;

/**
 * @covers \Lkngc\CryptocurrencyForGivewp\Includes\LkngcCryptocurrencyForGivewpHelper
 */
final class HelperTest extends TestCase {
    public function test_get_configs_le_as_opcoes_do_give(): void {
        $this->given_options( array(
            'lkn_give_cryptocurrency_bitcoin_wallet'      => 'wallet-btc',
            'lkn_give_cryptocurrency_ethereum_wallet'     => 'wallet-eth',
            'lkn_give_cryptocurrency_enable_error_margin' => 'enabled',
            'lkn_give_cryptocurrency_error_margin'        => 3,
            'lkn_give_cryptocurrency_debug'               => 'enabled',
            'lkn_give_cryptocurrency_debug_advanced'      => 'enabled',
        ) );

        $configs = LkngcCryptocurrencyForGivewpHelper::get_configs();

        $this->assertSame( 'wallet-btc', $configs['bitcoin_wallet'] );
        $this->assertSame( 'wallet-eth', $configs['ethereum_wallet'] );
        $this->assertSame( 'enabled', $configs['enable_error_margin'] );
        $this->assertSame( 3, $configs['error_margin'] );
        $this->assertSame( 'enabled', $configs['debug'] );
        $this->assertSame( 'enabled', $configs['debug_advanced'] );
    }

    public function test_get_configs_monta_o_caminho_do_log_com_a_data_atual(): void {
        $configs = LkngcCryptocurrencyForGivewpHelper::get_configs();

        $this->assertSame(
            LKNGC_GIVE_CRYPTOCURRENCY_FOR_GIVEWP_DIR . 'Includes/logs',
            $configs['basePath']
        );
        $this->assertMatchesRegularExpression(
            '#^' . preg_quote( LKNGC_GIVE_CRYPTOCURRENCY_FOR_GIVEWP_DIR . 'Includes/logs/', '#' ) . '\d{2}\.\d{2}\.\d{4}-\d{2}\.\d{2}\.\d{2}\.log$#',
            $configs['base']
        );
    }

    public function test_get_wallets_devolve_as_duas_carteiras(): void {
        $this->given_options( array(
            'lkn_give_cryptocurrency_bitcoin_wallet'  => 'wallet-btc',
            'lkn_give_cryptocurrency_ethereum_wallet' => 'wallet-eth',
        ) );

        $this->assertSame(
            array( 'bitcoin' => 'wallet-btc', 'ethereum' => 'wallet-eth' ),
            LkngcCryptocurrencyForGivewpHelper::get_wallets()
        );
    }

    public function test_plugin_row_meta_adiciona_o_link_de_configuracoes(): void {
        $meta = LkngcCryptocurrencyForGivewpHelper::plugin_row_meta( array( 'deactivate' => '<a>Desativar</a>' ) );

        $this->assertArrayHasKey( 'setting', $meta );
        $this->assertStringContainsString( 'page=give-settings', $meta['setting'] );
        $this->assertStringContainsString( 'tab=gateways', $meta['setting'] );
        $this->assertStringContainsString( 'section=lkngc-cryptocurrency-for-givewp', $meta['setting'] );
        $this->assertStringContainsString( 'Configurações', $meta['setting'] );
    }

    public function test_plugin_row_meta_preserva_os_links_que_ja_existiam(): void {
        $meta = LkngcCryptocurrencyForGivewpHelper::plugin_row_meta( array(
            'deactivate' => '<a>Desativar</a>',
            'settings'   => '<a>Configurações do Give</a>',
        ) );

        $this->assertStringContainsString( 'Desativar', $meta['deactivate'] );
        $this->assertStringContainsString( 'Configurações do Give', $meta['settings'] );
    }

    public function test_dependency_notice_cita_o_give_e_a_versao_minima(): void {
        ob_start();
        LkngcCryptocurrencyForGivewpHelper::dependency_notice();
        $html = ob_get_clean();

        $this->assertStringContainsString( 'notice-error', $html );
        $this->assertStringContainsString( 'https://givewp.com', $html );
        $this->assertStringContainsString( LKNGC_GIVE_CRYPTOCURRENCY_FOR_GIVEWP_MIN_GIVE_VERSION, $html );
        $this->assertStringContainsString( 'Erro de ativação', $html );
    }

    public function test_log_nao_grava_nada_com_o_debug_desligado(): void {
        $this->given_options( array( 'lkn_give_cryptocurrency_debug' => 'disabled' ) );

        LkngcCryptocurrencyForGivewpHelper::log( 10, array( 'foo' => 'bar' ) );

        $this->assertCount( 0, Lkngc_Give_Stub_Store::$logs );
    }

    public function test_log_grava_no_log_do_give_com_o_debug_ligado(): void {
        $this->given_options( array( 'lkn_give_cryptocurrency_debug' => 'enabled' ) );

        LkngcCryptocurrencyForGivewpHelper::log( 10, array( 'foo' => 'bar' ) );

        $this->assertCount( 1, Lkngc_Give_Stub_Store::$logs );

        $log = Lkngc_Give_Stub_Store::$logs[0];
        $this->assertSame( 'Cryptocurrency Payment Log', $log['message'] );
        $this->assertSame( 'Cryptocurrency Payment for GiveWP plugin', $log['context']['source'] );
        $this->assertSame( 10, $log['context']['donation_id'] );
        $this->assertSame( array( 'foo' => 'bar' ), $log['context']['data'] );
    }
}
