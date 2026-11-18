<?php
/**
 * Testes dos assets do front-end.
 *
 * @package Lkngc\CryptocurrencyForGivewp\Tests
 */

namespace Lkngc\CryptocurrencyForGivewp\Tests;

use Lkngc\CryptocurrencyForGivewp\PublicView\LkngcCryptocurrencyForGivewpPublic;

/**
 * @covers \Lkngc\CryptocurrencyForGivewp\PublicView\LkngcCryptocurrencyForGivewpPublic
 */
final class PublicAssetsTest extends TestCase {
    private function public(): LkngcCryptocurrencyForGivewpPublic {
        return new LkngcCryptocurrencyForGivewpPublic(
            'lkngc-cryptocurrency-for-givewp',
            LKNGC_GIVE_CRYPTOCURRENCY_FOR_GIVEWP_VERSION
        );
    }

    protected function setUp(): void {
        parent::setUp();
        $this->given_default_configs();
    }

    public function test_enqueue_scripts_registra_o_script_publico(): void {
        $this->public()->enqueue_scripts();

        $handle = 'lkngc-cryptocurrency-for-givewp-public-js';

        $this->assertTrue( wp_script_is( $handle, 'enqueued' ) );
        $this->assertStringContainsString(
            'js/lkngc-cryptocurrency-for-givewp-public.js',
            wp_scripts()->registered[ $handle ]->src
        );
        $this->assertSame(
            LKNGC_GIVE_CRYPTOCURRENCY_FOR_GIVEWP_VERSION,
            wp_scripts()->registered[ $handle ]->ver
        );
    }

    public function test_enqueue_scripts_localiza_as_carteiras(): void {
        $this->public()->enqueue_scripts();

        $data = wp_scripts()->get_data( 'lkngc-cryptocurrency-for-givewp-public-js', 'data' );

        $this->assertStringContainsString( 'wallet', $data );
        $this->assertStringContainsString( '1BoatSLRHtKNngkdXEeobR76b53LETtpyT', $data );
        $this->assertStringContainsString( '0x0000000000000000000000000000000000000001', $data );
    }

    public function test_enqueue_scripts_registra_a_biblioteca_de_qrcode(): void {
        $this->public()->enqueue_scripts();

        $this->assertTrue( wp_script_is( 'lkngc-cryptocurrency-for-givewp-qrcode-js', 'enqueued' ) );
        $this->assertStringContainsString(
            'js/qrcode.min.js',
            wp_scripts()->registered['lkngc-cryptocurrency-for-givewp-qrcode-js']->src
        );
    }

    public function test_enqueue_styles_registra_o_css_publico(): void {
        $this->public()->enqueue_styles();

        $handle = 'lkngc-cryptocurrency-for-givewp-public-css';

        $this->assertTrue( wp_style_is( $handle, 'enqueued' ) );
        $this->assertStringContainsString(
            'css/lkngc-cryptocurrency-for-givewp-public.css',
            wp_styles()->registered[ $handle ]->src
        );
        $this->assertSame(
            LKNGC_GIVE_CRYPTOCURRENCY_FOR_GIVEWP_VERSION,
            wp_styles()->registered[ $handle ]->ver
        );
    }
}
