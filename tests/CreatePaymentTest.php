<?php
/**
 * Testes da criação de pagamento (`createPayment`) dos gateways do Give v3.
 *
 * @package Lkngc\CryptocurrencyForGivewp\Tests
 */

namespace Lkngc\CryptocurrencyForGivewp\Tests;

use Give\Donations\Models\Donation;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\PaymentGateways\Commands\PaymentPending;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Lkngc\CryptocurrencyForGivewp\PublicView\LkngcCryptocurrencyForGivewpBitcoin;
use Lkngc\CryptocurrencyForGivewp\PublicView\LkngcCryptocurrencyForGivewpEthereum;
use Lkngc_Give_Stub_Store;

/**
 * @covers \Lkngc\CryptocurrencyForGivewp\PublicView\LkngcCryptocurrencyForGivewpBitcoin::createPayment
 * @covers \Lkngc\CryptocurrencyForGivewp\PublicView\LkngcCryptocurrencyForGivewpEthereum::createPayment
 */
final class CreatePaymentTest extends TestCase {
    private const WALLET_BTC = '1BoatSLRHtKNngkdXEeobR76b53LETtpyT';

    private const WALLET_ETH = '0x0000000000000000000000000000000000000001';

    protected function setUp(): void {
        parent::setUp();
        $this->given_default_configs();
    }

    /**
     * @return array<string, array{0:string, 1:string, 2:string, 3:string}>
     */
    public function currency_provider(): array {
        return array(
            'bitcoin'  => array( LkngcCryptocurrencyForGivewpBitcoin::class, 'btc', 'bitcoin', self::WALLET_BTC ),
            'ethereum' => array( LkngcCryptocurrencyForGivewpEthereum::class, 'eth', 'ethereum', self::WALLET_ETH ),
        );
    }

    /**
     * Mock da BlockCypher com a resposta informada.
     *
     * @param array<int, array{total:int, fees:int, addresses:array<int, string>, hash?:string}> $txs Transações.
     *
     * @return void
     */
    private function mock_blockcypher( array $txs ): void {
        $this->mock_http( array(
            'api.blockcypher.com' => array(
                'code' => 200,
                'body' => $this->blockcypher_body( $txs ),
            ),
        ) );
    }

    /**
     * @dataProvider currency_provider
     */
    public function test_sem_transacoes_devolve_pagamento_pendente(
        string $class,
        string $coin,
        string $scheme
    ): void {
        $this->mock_blockcypher( array() );

        $donation = new Donation( 500 );
        $command  = ( new $class() )->createPayment( $donation, array( 'cryptoValue' => '10000' ) );

        $this->assertInstanceOf( PaymentPending::class, $command );

        $meta = $this->stored_payment_response( 500 );
        $this->assertSame( 'Pending payment', $meta['status'] );
        $this->assertSame( '0', $meta['transactionId'] );
        $this->assertSame( $coin, $meta['crypto'] );
    }

    /**
     * @dataProvider currency_provider
     */
    public function test_consulta_a_url_da_moeda_correta(
        string $class,
        string $coin,
        string $scheme,
        string $wallet
    ): void {
        $this->mock_blockcypher( array() );

        ( new $class() )->createPayment( new Donation( 501 ), array( 'cryptoValue' => '1' ) );

        $this->assertSame(
            'https://api.blockcypher.com/v1/' . $coin . '/main/addrs/' . $wallet . '/full?limit=5',
            $this->last_http_url()
        );
    }

    /**
     * @dataProvider currency_provider
     */
    public function test_transacao_com_o_valor_exato_conclui_a_doacao(
        string $class,
        string $coin,
        string $scheme,
        string $wallet
    ): void {
        $this->mock_blockcypher( array(
            array(
                'total'     => 10000,
                'fees'      => 0,
                'addresses' => array( $wallet ),
                'hash'      => 'hash-' . $coin,
            ),
        ) );

        $donation = new Donation( 502 );
        $command  = ( new $class() )->createPayment( $donation, array( 'cryptoValue' => '10000' ) );

        $this->assertInstanceOf( PaymentComplete::class, $command );
        $this->assertSame( 'complete', $donation->status->getValue() );
        $this->assertTrue( $donation->saved );

        $meta = $this->stored_payment_response( 502 );
        $this->assertSame( 'Completed payment', $meta['status'] );
        $this->assertSame( 'hash-' . $coin, $meta['transactionId'] );
    }

    /**
     * O valor da meta é o que veio do formulário (string), não o convertido.
     *
     * @dataProvider currency_provider
     */
    public function test_guarda_o_valor_original_da_meta(
        string $class,
        string $coin,
        string $scheme,
        string $wallet
    ): void {
        $this->mock_blockcypher( array(
            array(
                'total'     => 10000,
                'fees'      => 0,
                'addresses' => array( $wallet ),
            ),
        ) );

        ( new $class() )->createPayment( new Donation( 503 ), array( 'cryptoValue' => '10000' ) );

        $this->assertSame( '10000', $this->stored_payment_response( 503 )['totalCrypto'] );
    }

    /**
     * Comportamento atual: o valor vindo do front-end em formato decimal
     * ("0,0001" / "0.0001") nunca casa com o total em satoshis/wei. A doação
     * fica pendente e a confirmação depende do botão do wp-admin.
     *
     * @dataProvider currency_provider
     */
    public function test_valor_decimal_do_frontend_nao_confirma_a_doacao(
        string $class,
        string $coin,
        string $scheme,
        string $wallet
    ): void {
        $this->mock_blockcypher( array(
            array(
                'total'     => 10000,
                'fees'      => 0,
                'addresses' => array( $wallet ),
            ),
        ) );

        $donation = new Donation( 504 );
        $command  = ( new $class() )->createPayment( $donation, array( 'cryptoValue' => '0,0001' ) );

        $this->assertInstanceOf( PaymentPending::class, $command );
        $this->assertSame( 'pending', $donation->status->getValue() );
    }

    /**
     * @dataProvider currency_provider
     */
    public function test_sem_transacao_equivalente_mantem_a_doacao_pendente(
        string $class,
        string $coin,
        string $scheme,
        string $wallet
    ): void {
        $this->mock_blockcypher( array(
            array(
                'total'     => 999,
                'fees'      => 1,
                'addresses' => array( $wallet ),
            ),
        ) );

        $donation = new Donation( 505 );
        $command  = ( new $class() )->createPayment( $donation, array( 'cryptoValue' => '10000' ) );

        $this->assertInstanceOf( PaymentPending::class, $command );
        $this->assertSame( 'pending', $donation->status->getValue() );
        $this->assertTrue( $donation->saved );
    }

    /**
     * @dataProvider currency_provider
     */
    public function test_resposta_invalida_da_api_falha_a_doacao(
        string $class,
        string $coin,
        string $scheme,
        string $wallet
    ): void {
        $this->mock_http( array(
            'api.blockcypher.com' => array(
                'code' => 200,
                'body' => 'isto-nao-e-json',
            ),
        ) );

        $donation = new Donation( 506 );

        try {
            ( new $class() )->createPayment( $donation, array( 'cryptoValue' => '10000' ) );
            $this->fail( 'Esperava uma PaymentGatewayException.' );
        } catch ( PaymentGatewayException $e ) {
            $this->assertSame( 'Invalid JSON response from API', $e->getMessage() );
        }

        $this->assertSame( 'failed', $donation->status->getValue() );
        $this->assertCount( 1, Lkngc_Give_Stub_Store::$notes );
        $this->assertStringContainsString(
            'Falha na doação',
            Lkngc_Give_Stub_Store::$notes[0]['content']
        );
    }

    /**
     * @dataProvider currency_provider
     */
    public function test_api_fora_do_ar_falha_a_doacao(
        string $class,
        string $coin,
        string $scheme,
        string $wallet
    ): void {
        // Sem mock: WP_Error → connect_query devolve false → JSON inválido.
        $donation = new Donation( 507 );

        try {
            ( new $class() )->createPayment( $donation, array( 'cryptoValue' => '10000' ) );
            $this->fail( 'Esperava uma PaymentGatewayException.' );
        } catch ( PaymentGatewayException $e ) {
            $this->assertSame( 'Invalid JSON response from API', $e->getMessage() );
        }

        $this->assertSame( 'failed', $donation->status->getValue() );
    }

    /**
     * @dataProvider currency_provider
     */
    public function test_refund_devolve_o_comando_de_estorno(
        string $class
    ): void {
        $command = ( new $class() )->refundDonation( new Donation( 508 ) );

        $this->assertInstanceOf(
            \Give\Framework\PaymentGateways\Commands\PaymentRefunded::class,
            $command
        );
    }
}
