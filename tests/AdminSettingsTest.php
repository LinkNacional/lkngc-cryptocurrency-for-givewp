<?php
/**
 * Testes do admin: seção de configurações no Give, script do wp-admin e bloco
 * de detalhes da doação.
 *
 * @package Lkngc\CryptocurrencyForGivewp\Tests
 */

namespace Lkngc\CryptocurrencyForGivewp\Tests;

use Lkngc\CryptocurrencyForGivewp\Admin\LkngcCryptocurrencyForGivewpAdmin;
use Lkngc_Give_Stub_Store;

/**
 * @covers \Lkngc\CryptocurrencyForGivewp\Admin\LkngcCryptocurrencyForGivewpAdmin
 */
final class AdminSettingsTest extends TestCase {
    private const SECTION = 'lkngc-cryptocurrency-for-givewp';

    private function admin(): LkngcCryptocurrencyForGivewpAdmin {
        return new LkngcCryptocurrencyForGivewpAdmin( self::SECTION, LKNGC_GIVE_CRYPTOCURRENCY_FOR_GIVEWP_VERSION );
    }

    /**
     * IDs dos campos devolvidos para a nossa seção.
     *
     * @return array<int, string>
     */
    private function setting_ids( array $settings ): array {
        $ids = array();

        foreach ( $settings as $setting ) {
            if ( isset( $setting['id'] ) ) {
                $ids[] = $setting['id'];
            }
        }

        return $ids;
    }

    public function test_registra_a_secao_criptocurrencies_nas_configuracoes_do_give(): void {
        $sections = $this->admin()->lkngc_new_setting_section( array( 'gateways' => 'Gateways' ) );

        $this->assertArrayHasKey( self::SECTION, $sections );
        $this->assertSame( 'CriptoCurrencies', $sections[ self::SECTION ] );
        $this->assertSame( 'Gateways', $sections['gateways'] );
    }

    public function test_adiciona_os_campos_da_secao(): void {
        Lkngc_Give_Stub_Store::$current_section = self::SECTION;

        $settings = $this->admin()->lkngc_add_setting_into_new_section( array() );

        $this->assertSame(
            array(
                'lkn_give_cryptocurrency',
                'lkn_give_cryptocurrency_bitcoin_wallet',
                'lkn_give_cryptocurrency_ethereum_wallet',
                'lkn_give_cryptocurrency_enable_error_margin',
                'lkn_give_cryptocurrency_error_margin',
                'lkn_give_cryptocurrency_debug',
                'lkn_give_cryptocurrency_debug_advanced',
                'lkn_give_cryptocurrency',
            ),
            $this->setting_ids( $settings )
        );

        $this->assertSame( 'title', $settings[0]['type'] );
        $this->assertSame( 'sectionend', $settings[ count( $settings ) - 1 ]['type'] );
    }

    public function test_os_radios_comecam_desligados(): void {
        Lkngc_Give_Stub_Store::$current_section = self::SECTION;

        $settings = $this->admin()->lkngc_add_setting_into_new_section( array() );
        $radios   = array();

        foreach ( $settings as $setting ) {
            if ( isset( $setting['type'] ) && 'radio' === $setting['type'] ) {
                $radios[ $setting['id'] ] = $setting;
            }
        }

        foreach ( array(
            'lkn_give_cryptocurrency_enable_error_margin',
            'lkn_give_cryptocurrency_debug',
            'lkn_give_cryptocurrency_debug_advanced',
        ) as $id ) {
            $this->assertArrayHasKey( $id, $radios );
            $this->assertSame( 'disabled', $radios[ $id ]['default'] );
            $this->assertSame( array( 'enabled', 'disabled' ), array_keys( $radios[ $id ]['options'] ) );
        }
    }

    public function test_campos_de_carteira_sao_texto_e_de_margem_e_numero(): void {
        Lkngc_Give_Stub_Store::$current_section = self::SECTION;

        $by_id = array();

        foreach ( $this->admin()->lkngc_add_setting_into_new_section( array() ) as $setting ) {
            if ( isset( $setting['id'] ) ) {
                $by_id[ $setting['id'] ] = $setting;
            }
        }

        $this->assertSame( 'text', $by_id['lkn_give_cryptocurrency_bitcoin_wallet']['type'] );
        $this->assertSame( 'text', $by_id['lkn_give_cryptocurrency_ethereum_wallet']['type'] );
        $this->assertSame( 'number', $by_id['lkn_give_cryptocurrency_error_margin']['type'] );
    }

    public function test_nao_altera_os_campos_de_outras_secoes(): void {
        Lkngc_Give_Stub_Store::$current_section = 'payment_gateways';

        $original = array( array( 'id' => 'algum_campo', 'type' => 'text' ) );

        $this->assertSame( $original, $this->admin()->lkngc_add_setting_into_new_section( $original ) );
    }

    public function test_enqueue_scripts_registra_o_script_com_a_versao_do_plugin(): void {
        $this->admin()->enqueue_scripts();

        $handle = self::SECTION;

        $this->assertTrue( wp_script_is( $handle, 'enqueued' ) );
        $this->assertStringContainsString(
            'js/lkngc-cryptocurrency-for-givewp-admin.js',
            wp_scripts()->registered[ $handle ]->src
        );
        $this->assertSame(
            LKNGC_GIVE_CRYPTOCURRENCY_FOR_GIVEWP_VERSION,
            wp_scripts()->registered[ $handle ]->ver
        );
    }

    public function test_enqueue_scripts_localiza_o_token_da_rota(): void {
        $this->admin()->enqueue_scripts();

        $data = wp_scripts()->get_data( self::SECTION, 'data' );

        $this->assertIsString( $data );
        $this->assertStringContainsString( 'lknCryptoGlobal', $data );
        $this->assertStringContainsString( wp_hash( gmdate( 'dmYH' ) . 'notification' ), $data );
        $this->assertStringContainsString( 'msg_error', $data );
    }

    public function test_give_inactive_notice_aponta_para_o_givewp(): void {
        ob_start();
        LkngcCryptocurrencyForGivewpAdmin::give_inactive_notice();
        $html = ob_get_clean();

        $this->assertStringContainsString( 'https://givewp.com', $html );
        $this->assertStringContainsString( 'Activation Error', $html );
    }

    public function test_donation_details_nao_imprime_nada_sem_metadados(): void {
        ob_start();
        $this->admin()->lkngc_cryptocurrency_for_givewp_donation_details( 123 );
        $html = ob_get_clean();

        $this->assertSame( '', $html );
    }

    public function test_donation_details_nao_imprime_nada_com_metadados_invalidos(): void {
        Lkngc_Give_Stub_Store::$meta[124]['lkngc_cryptocurrency_for_givewp_response'] = 'nao-e-json';

        ob_start();
        $this->admin()->lkngc_cryptocurrency_for_givewp_donation_details( 124 );
        $html = ob_get_clean();

        $this->assertSame( '', $html );
    }

    public function test_donation_details_imprime_os_dados_da_transacao(): void {
        Lkngc_Give_Stub_Store::$meta[125]['lkngc_cryptocurrency_for_givewp_response'] = wp_json_encode( array(
            'wallet'        => 'carteira-x',
            'status'        => 'Completed payment',
            'transactionId' => 'hash-abc',
            'totalCrypto'   => '0.00010000',
            'crypto'        => 'btc',
        ) );

        ob_start();
        $this->admin()->lkngc_cryptocurrency_for_givewp_donation_details( 125 );
        $html = ob_get_clean();

        // O partial depende de `$args` existir no escopo do include.
        $this->assertStringContainsString( 'Completed payment', $html );
        $this->assertStringContainsString( 'hash-abc', $html );
        $this->assertStringContainsString( 'carteira-x', $html );
        $this->assertStringContainsString( '0.00010000', $html );
        $this->assertStringContainsString( 'btc', $html );
        $this->assertStringContainsString( 'Consult Transaction', $html );
    }

    public function test_donation_details_escapa_o_conteudo_da_meta(): void {
        Lkngc_Give_Stub_Store::$meta[126]['lkngc_cryptocurrency_for_givewp_response'] = wp_json_encode( array(
            'wallet'        => '<script>alert(1)</script>',
            'status'        => 'Pending payment',
            'transactionId' => '0',
            'totalCrypto'   => '0.00010000',
            'crypto'        => 'btc',
        ) );

        ob_start();
        $this->admin()->lkngc_cryptocurrency_for_givewp_donation_details( 126 );
        $html = ob_get_clean();

        $this->assertStringNotContainsString( '<script>', $html );
        $this->assertStringContainsString( '&lt;script&gt;', $html );
    }
}
