<?php
/**
 * Testes da consulta à API da BlockCypher (`CryptoApi::blockcypher_wallet_query`).
 *
 * @package Lkngc\CryptocurrencyForGivewp\Tests
 */

namespace Lkngc\CryptocurrencyForGivewp\Tests;

use Lkngc\CryptocurrencyForGivewp\Includes\CryptoApi;

/**
 * @covers \Lkngc\CryptocurrencyForGivewp\Includes\CryptoApi::blockcypher_wallet_query
 */
final class CryptoApiQueryTest extends TestCase {
    public function test_monta_a_url_de_consulta_do_bitcoin(): void {
        $this->mock_http( array(
            'api.blockcypher.com/v1/btc/main/addrs/' => array(
                'code' => 200,
                'body' => wp_json_encode( array( 'n_tx' => 1, 'txs' => array() ) ),
            ),
        ) );

        $result = CryptoApi::blockcypher_wallet_query( 'WALLET_BTC', 'btc', 5 );

        $this->assertSame(
            'https://api.blockcypher.com/v1/btc/main/addrs/WALLET_BTC/full?limit=5',
            $this->last_http_url()
        );
        $this->assertSame( 1, $result['n_tx'] );
        $this->assertSame( array(), $result['txs'] );
    }

    public function test_monta_a_url_de_consulta_do_ethereum(): void {
        $this->mock_http( array(
            'api.blockcypher.com/v1/eth/main/addrs/' => array(
                'code' => 200,
                'body' => wp_json_encode( array( 'n_tx' => 0, 'txs' => array() ) ),
            ),
        ) );

        CryptoApi::blockcypher_wallet_query( '0xabc', 'eth', 50 );

        $this->assertSame(
            'https://api.blockcypher.com/v1/eth/main/addrs/0xabc/full?limit=50',
            $this->last_http_url()
        );
    }

    public function test_usa_timeout_curto_e_os_headers_de_json(): void {
        $this->mock_http( array(
            'api.blockcypher.com' => array(
                'code' => 200,
                'body' => wp_json_encode( array( 'n_tx' => 0, 'txs' => array() ) ),
            ),
        ) );

        CryptoApi::blockcypher_wallet_query( 'WALLET', 'btc', 5 );

        $args = $this->http_args[0];

        $this->assertSame( '10', (string) $args['timeout'] );
        $this->assertSame( '5', (string) $args['redirection'] );
        $this->assertSame( '1.1', (string) $args['httpversion'] );
        $this->assertSame( array( 'Content-Type: application/json' ), $args['headers'] );
    }

    public function test_devolve_null_quando_a_api_falha(): void {
        // Sem mock: o filtro devolve um WP_Error, como um erro de rede.
        $result = CryptoApi::blockcypher_wallet_query( 'WALLET', 'btc', 5 );

        $this->assertNull( $result );
    }
}
