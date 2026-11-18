<?php
/**
 * Testes dos gateways do Give v3 (Bitcoin e Ethereum): identificação, markup do
 * formulário clássico e assets enfileirados.
 *
 * @package Lkngc\CryptocurrencyForGivewp\Tests
 */

namespace Lkngc\CryptocurrencyForGivewp\Tests;

use Lkngc\CryptocurrencyForGivewp\PublicView\LkngcCryptocurrencyForGivewpBitcoin;
use Lkngc\CryptocurrencyForGivewp\PublicView\LkngcCryptocurrencyForGivewpEthereum;
use Lkngc_Give_Stub_Store;

/**
 * @covers \Lkngc\CryptocurrencyForGivewp\PublicView\LkngcCryptocurrencyForGivewpBitcoin
 * @covers \Lkngc\CryptocurrencyForGivewp\PublicView\LkngcCryptocurrencyForGivewpEthereum
 */
final class PaymentGatewayTest extends TestCase {
    private const WALLET_BTC = '1BoatSLRHtKNngkdXEeobR76b53LETtpyT';

    private const WALLET_ETH = '0x0000000000000000000000000000000000000001';

    protected function setUp(): void {
        parent::setUp();
        $this->given_default_configs();
    }

    /**
     * Cenários das duas moedas.
     *
     * @return array<string, array{0:string, 1:string, 2:string, 3:string, 4:string}>
     */
    public function currency_provider(): array {
        return array(
            'bitcoin'  => array(
                LkngcCryptocurrencyForGivewpBitcoin::class,
                'lkngc-cryptocurrencyforgivewp-bitcoin',
                'Crypto Bitcoin',
                'bitcoin',
                self::WALLET_BTC,
            ),
            'ethereum' => array(
                LkngcCryptocurrencyForGivewpEthereum::class,
                'lkngc-cryptocurrencyforgivewp-ethereum',
                'Crypto Ethereum',
                'ethereum',
                self::WALLET_ETH,
            ),
        );
    }

    /**
     * @dataProvider currency_provider
     */
    public function test_id_do_gateway( string $class, string $expected_id ): void {
        $this->assertSame( $expected_id, $class::id() );
        $this->assertSame( $expected_id, ( new $class() )->getId() );
    }

    /**
     * @dataProvider currency_provider
     */
    public function test_nome_do_gateway( string $class, string $expected_id, string $expected_name ): void {
        $gateway = new $class();

        $this->assertSame( $expected_name, $gateway->getName() );
        $this->assertSame( $expected_name, $gateway->getPaymentMethodLabel() );
    }

    /**
     * @dataProvider currency_provider
     */
    public function test_markup_do_formulario_classico_traz_o_qrcode_e_a_carteira(
        string $class,
        string $expected_id,
        string $expected_name,
        string $scheme,
        string $wallet
    ): void {
        Lkngc_Give_Stub_Store::$form_templates[10] = 'legacy';

        $html = ( new $class() )->getLegacyFormFieldMarkup( 10, array() );

        $this->assertStringContainsString( $scheme . ':' . $wallet, $html );
        $this->assertStringContainsString( 'id="qrcode"', $html );
        $this->assertStringContainsString( $wallet, $html );
        $this->assertStringContainsString( 'name="gatewayData[cryptoValue]"', $html );
        $this->assertStringNotContainsString( 'is not enabled', $html );
    }

    /**
     * @dataProvider currency_provider
     */
    public function test_formulario_novo_avisa_que_a_criptomoeda_nao_esta_habilitada(
        string $class
    ): void {
        Lkngc_Give_Stub_Store::$form_templates[11] = 'multi-step';

        $html = ( new $class() )->getLegacyFormFieldMarkup( 11, array() );

        $this->assertStringContainsString(
            'CryptoCurrency is not enabled for the classic and multistep form!',
            $html
        );
        $this->assertStringNotContainsString( 'id="qrcode"', $html );
    }

    /**
     * Formulário sem a meta `_give_form_template` é tratado como clássico.
     *
     * @dataProvider currency_provider
     */
    public function test_formulario_sem_meta_de_template_usa_o_markup_classico(
        string $class,
        string $expected_id,
        string $expected_name,
        string $scheme,
        string $wallet
    ): void {
        Lkngc_Give_Stub_Store::$form_templates[12] = null;

        $html = ( new $class() )->getLegacyFormFieldMarkup( 12, array() );

        $this->assertStringContainsString( 'id="qrcode"', $html );
        $this->assertStringContainsString( $wallet, $html );
    }

    /**
     * @dataProvider currency_provider
     */
    public function test_enqueue_script_registra_assets_e_a_carteira(
        string $class,
        string $expected_id,
        string $expected_name,
        string $scheme,
        string $wallet
    ): void {
        ( new $class() )->enqueueScript( 10 );

        $script_handle = 'lkngc-' . $scheme . '-script-js';

        $this->assertTrue( wp_script_is( $script_handle, 'enqueued' ) );
        $this->assertStringContainsString(
            'js/' . $scheme . '-script.js',
            wp_scripts()->registered[ $script_handle ]->src
        );
        $this->assertSame(
            LKNGC_GIVE_CRYPTOCURRENCY_FOR_GIVEWP_VERSION,
            wp_scripts()->registered[ $script_handle ]->ver
        );

        $data = wp_scripts()->get_data( $script_handle, 'data' );

        $this->assertStringContainsString( 'wallet' . ucfirst( $scheme ), $data );
        $this->assertStringContainsString( $wallet, $data );
        $this->assertStringContainsString( 'decimal_separator', $data );
        $this->assertStringContainsString( 'currency', $data );

        $this->assertTrue( wp_style_is( 'lkngc-' . $scheme . '-give-cryptocurrency-public-css', 'enqueued' ) );
        $this->assertTrue( wp_script_is( 'lkngc-' . $scheme . '-give-cryptocurrency-qrcode-js', 'enqueued' ) );
    }

    /**
     * Carteira vazia/curta é enviada como string vazia e a mensagem de erro vai
     * junto para o JS.
     *
     * @dataProvider currency_provider
     */
    public function test_carteira_invalida_nao_e_enviada_para_o_frontend(
        string $class,
        string $expected_id,
        string $expected_name,
        string $scheme
    ): void {
        $this->given_default_configs( array(
            'lkn_give_cryptocurrency_' . $scheme . '_wallet' => 'curta',
        ) );

        ( new $class() )->enqueueScript( 10 );

        $data = wp_scripts()->get_data( 'lkngc-' . $scheme . '-script-js', 'data' );

        $this->assertStringContainsString( '"wallet' . ucfirst( $scheme ) . '":""', $data );
        $this->assertStringContainsString( 'MenssageError' . ucfirst( $scheme ), $data );
    }
}
