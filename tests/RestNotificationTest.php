<?php
/**
 * Testes da rota REST usada pelo botão "Consult Transaction" do wp-admin
 * (`lkngc-cryptocurrency-for-givewp-verification/v1/notification`).
 *
 * @package Lkngc\CryptocurrencyForGivewp\Tests
 */

namespace Lkngc\CryptocurrencyForGivewp\Tests;

use Lkngc_Give_Stub_Store;
use WP_REST_Request;

/**
 * @covers \Lkngc\CryptocurrencyForGivewp\Includes\CryptoApi::btn_transaction_verification
 * @covers \Lkngc\CryptocurrencyForGivewp\Includes\LkngcCryptocurrencyForGivewp::lkngc_give_crypto_register_routes
 */
final class RestNotificationTest extends TestCase {
    private const ROUTE = '/lkngc-cryptocurrency-for-givewp-verification/v1/notification';

    private const WALLET_BTC = '1BoatSLRHtKNngkdXEeobR76b53LETtpyT';

    protected function setUp(): void {
        parent::setUp();
        $this->given_default_configs();
    }

    /**
     * Token que o plugin espera (mesma expressão do admin e da rota).
     */
    private function valid_token(): string {
        return wp_hash( gmdate( 'dmYH' ) . 'notification' );
    }

    /**
     * Dispara a rota com os parâmetros informados.
     *
     * @param array<string, mixed> $params Parâmetros do corpo.
     *
     * @return mixed Dados da resposta.
     */
    private function dispatch( array $params ) {
        $request = new WP_REST_Request( 'POST', self::ROUTE );
        $request->set_body_params( $params );

        $response = rest_do_request( $request );

        if ( $response->is_error() ) {
            return $response->as_error();
        }

        return $response->get_data();
    }

    public function test_a_rota_e_registrada_como_post(): void {
        $routes = rest_get_server()->get_routes();

        $this->assertArrayHasKey( self::ROUTE, $routes );
        $this->assertArrayHasKey( 'POST', $routes[ self::ROUTE ][0]['methods'] );
        $this->assertArrayNotHasKey( 'GET', $routes[ self::ROUTE ][0]['methods'] );
    }

    public function test_sem_token_autenticado_a_requisicao_e_recusada(): void {
        $data = $this->dispatch( array(
            'donation_id'  => '1',
            'crypt_value'  => '0.00010000',
            'wallet_regist' => self::WALLET_BTC,
            'crypto_type'  => 'btc',
        ) );

        $this->assertSame( 'Unauthenticated session', $data['return_response'] );
        $this->assertSame( 0, $this->http_request_count(), 'Não deve consultar a API sem token válido.' );
    }

    public function test_token_de_outro_horario_e_recusado(): void {
        $data = $this->dispatch( array(
            'token'        => wp_hash( gmdate( 'dmYH' ) . 'outra-coisa' ),
            'donation_id'  => '1',
            'crypt_value'  => '0.00010000',
            'wallet_regist' => self::WALLET_BTC,
            'crypto_type'  => 'btc',
        ) );

        $this->assertSame( 'Unauthenticated session', $data['return_response'] );
    }

    public function test_pagamento_confirmado_marca_a_doacao_como_completa(): void {
        $donation = $this->given_donation( 55 );

        Lkngc_Give_Stub_Store::$meta[55]['_give_payment_gateway'] = 'lkngc-cryptocurrencyforgivewp-bitcoin';

        $this->mock_http( array(
            'api.blockcypher.com' => array(
                'code' => 200,
                'body' => $this->blockcypher_body( array(
                    array(
                        'total'     => 10000,
                        'fees'      => 0,
                        'addresses' => array( self::WALLET_BTC ),
                    ),
                ) ),
            ),
        ) );

        $data = $this->dispatch( array(
            'token'        => $this->valid_token(),
            'donation_id'  => '55',
            'crypt_value'  => '0.00010000',
            'wallet_regist' => self::WALLET_BTC,
            'crypto_type'  => 'btc',
        ) );

        $this->assertSame( '[Query result]: Payment confirmed', $data['return_response'] );
        $this->assertSame( 'complete', $donation->status->getValue() );
    }

    public function test_transacao_nao_encontrada_devolve_mensagem_de_erro(): void {
        $this->given_donation( 56 );

        $this->mock_http( array(
            'api.blockcypher.com' => array(
                'code' => 200,
                'body' => $this->blockcypher_body( array(
                    array(
                        'total'     => 999,
                        'fees'      => 0,
                        'addresses' => array( 'outra-carteira' ),
                    ),
                ) ),
            ),
        ) );

        $data = $this->dispatch( array(
            'token'        => $this->valid_token(),
            'donation_id'  => '56',
            'crypt_value'  => '0.00010000',
            'wallet_regist' => self::WALLET_BTC,
            'crypto_type'  => 'btc',
        ) );

        $this->assertSame(
            '[Query result]: Unrecognized transaction on blockchain',
            $data['return_response']
        );
    }

    public function test_sem_transacoes_devolve_mensagem_de_erro(): void {
        $this->given_donation( 57 );

        $this->mock_http( array(
            'api.blockcypher.com' => array(
                'code' => 200,
                'body' => $this->blockcypher_body( array() ),
            ),
        ) );

        $data = $this->dispatch( array(
            'token'        => $this->valid_token(),
            'donation_id'  => '57',
            'crypt_value'  => '0.00010000',
            'wallet_regist' => self::WALLET_BTC,
            'crypto_type'  => 'btc',
        ) );

        $this->assertSame(
            '[Query result]: Unrecognized transaction on blockchain',
            $data['return_response']
        );
    }

    /**
     * Sem os parâmetros obrigatórios a rota precisa responder (antes havia um
     * `exit` que matava o processo e deixava a resposta vazia).
     */
    public function test_requisicao_sem_parametros_obrigatorios_responde_json(): void {
        $data = $this->dispatch( array( 'token' => $this->valid_token() ) );

        $this->assertSame(
            '[Query result]: Unrecognized transaction on blockchain',
            $data['return_response']
        );
    }

    /**
     * A API fora do ar não pode quebrar a resposta: a doação simplesmente não
     * é confirmada.
     */
    public function test_api_fora_do_ar_responde_sem_confirmar(): void {
        $donation = $this->given_donation( 58 );

        $data = $this->dispatch( array(
            'token'        => $this->valid_token(),
            'donation_id'  => '58',
            'crypt_value'  => '0.00010000',
            'wallet_regist' => self::WALLET_BTC,
            'crypto_type'  => 'btc',
        ) );

        $this->assertSame(
            '[Query result]: Unrecognized transaction on blockchain',
            $data['return_response']
        );
        // Sem resposta da API a doação nunca é confirmada.
        $this->assertNull( $donation->status );
    }
}
