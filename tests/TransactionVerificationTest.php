<?php
/**
 * Testes da verificação de transação (`CryptoApi::transaction_verification`):
 * a regra que decide entre doação pendente e completa.
 *
 * @package Lkngc\CryptocurrencyForGivewp\Tests
 */

namespace Lkngc\CryptocurrencyForGivewp\Tests;

use Lkngc\CryptocurrencyForGivewp\Includes\CryptoApi;
use Lkngc_Give_Stub_Store;

/**
 * @covers \Lkngc\CryptocurrencyForGivewp\Includes\CryptoApi::transaction_verification
 */
final class TransactionVerificationTest extends TestCase {
    private const WALLET_BTC = '1BoatSLRHtKNngkdXEeobR76b53LETtpyT';

    private const WALLET_ETH = '0x0000000000000000000000000000000000000001';

    /**
     * Meta usada por `transaction_verification()`.
     *
     * Atenção: é diferente da meta usada pelos gateways
     * (`lkngc_cryptocurrency_for_givewp_response`).
     */
    private const META_KEY = 'lkn_give_cryptocurrency_response';

    protected function setUp(): void {
        parent::setUp();
        $this->given_default_configs();
    }

    /**
     * Instala a resposta da BlockCypher para a carteira/moeda em uso.
     *
     * @param array<int, array{total:int, fees:int, addresses:array<int, string>, hash?:string}> $txs Transações.
     * @param int|null                                                                            $n_tx `n_tx` da resposta.
     *
     * @return void
     */
    private function mock_blockcypher( array $txs, ?int $n_tx = null ): void {
        $this->mock_http( array(
            'api.blockcypher.com' => array(
                'code' => 200,
                'body' => $this->blockcypher_body( $txs, $n_tx ),
            ),
        ) );
    }

    public function test_sem_transacoes_marca_a_doacao_como_pendente(): void {
        $this->mock_blockcypher( array() );

        CryptoApi::transaction_verification( 77, 'bitcoin', '0.00010000', self::WALLET_BTC, 'btc' );

        $meta = $this->stored_payment_response( 77, self::META_KEY );

        $this->assertSame( 'Pending payment', $meta['status'] );
        $this->assertSame( '0', $meta['transactionId'] );
        $this->assertSame( 'btc', $meta['cripto'] );
        $this->assertSame( 'bitcoin', $meta['gatewayId'] );
        $this->assertSame( self::WALLET_BTC, $meta['wallet'] );
        $this->assertSame( '0.00010000', $meta['totalCrypto'] );

        // Nunca conclui a doação e sempre redireciona para a página de sucesso.
        $this->assertSame( array(), Lkngc_Give_Stub_Store::$status_updates );
        $this->assertCount( 1, Lkngc_Give_Stub_Store::$success_pages );
    }

    public function test_transacao_com_valor_e_carteira_corretos_conclui_a_doacao(): void {
        $this->mock_blockcypher( array(
            array(
                'total'     => 10000,
                'fees'      => 0,
                'addresses' => array( self::WALLET_BTC ),
                'hash'      => 'tx-hash-1',
            ),
        ) );

        CryptoApi::transaction_verification( 78, 'bitcoin', '0.00010000', self::WALLET_BTC, 'btc' );

        $meta = $this->stored_payment_response( 78, self::META_KEY );

        $this->assertSame( 'Completed', $meta['status'] );
        $this->assertSame( 'tx-hash-1', $meta['transactionId'] );
        $this->assertSame(
            array( array( 'donation_id' => 78, 'status' => 'complete' ) ),
            Lkngc_Give_Stub_Store::$status_updates
        );
    }

    public function test_soma_as_taxas_ao_valor_da_transacao(): void {
        // 9.000 + 1.000 de taxa = 10.000 (o valor doação considerando 8 casas).
        $this->mock_blockcypher( array(
            array(
                'total'     => 9000,
                'fees'      => 1000,
                'addresses' => array( self::WALLET_BTC ),
            ),
        ) );

        CryptoApi::transaction_verification( 79, 'bitcoin', '0.00010000', self::WALLET_BTC, 'btc' );

        $this->assertSame( 'Completed', $this->stored_payment_response( 79, self::META_KEY )['status'] );
    }

    public function test_carteira_diferente_mantem_a_doacao_pendente(): void {
        $this->mock_blockcypher( array(
            array(
                'total'     => 10000,
                'fees'      => 0,
                'addresses' => array( 'outra-carteira-qualquer' ),
            ),
        ) );

        CryptoApi::transaction_verification( 80, 'bitcoin', '0.00010000', self::WALLET_BTC, 'btc' );

        $this->assertSame( 'Pending payment', $this->stored_payment_response( 80, self::META_KEY )['status'] );
        $this->assertSame( array(), Lkngc_Give_Stub_Store::$status_updates );
    }

    public function test_valor_fora_da_faixa_mantem_a_doacao_pendente(): void {
        $this->mock_blockcypher( array(
            array(
                'total'     => 500000,
                'fees'      => 0,
                'addresses' => array( self::WALLET_BTC ),
            ),
        ) );

        CryptoApi::transaction_verification( 81, 'bitcoin', '0.00010000', self::WALLET_BTC, 'btc' );

        $this->assertSame( 'Pending payment', $this->stored_payment_response( 81, self::META_KEY )['status'] );
    }

    public function test_valor_levemente_menor_nao_passa_na_margem_desligada(): void {
        $this->mock_blockcypher( array(
            array(
                'total'     => 100000,
                'fees'      => 0,
                'addresses' => array( self::WALLET_BTC ),
            ),
        ) );

        // 99.000 contra 100.000: 1% de diferença.
        CryptoApi::transaction_verification( 82, 'bitcoin', '0.00099000', self::WALLET_BTC, 'btc' );

        $this->assertSame( 'Pending payment', $this->stored_payment_response( 82, self::META_KEY )['status'] );
    }

    public function test_margem_de_erro_ligada_aceita_o_valor_levemente_menor(): void {
        $this->given_default_configs( array(
            'lkn_give_cryptocurrency_enable_error_margin' => 'enabled',
            'lkn_give_cryptocurrency_error_margin'        => 2,
        ) );

        $this->mock_blockcypher( array(
            array(
                'total'     => 100000,
                'fees'      => 0,
                'addresses' => array( self::WALLET_BTC ),
            ),
        ) );

        CryptoApi::transaction_verification( 83, 'bitcoin', '0.00099000', self::WALLET_BTC, 'btc' );

        $this->assertSame( 'Completed', $this->stored_payment_response( 83, self::META_KEY )['status'] );
    }

    public function test_margem_de_erro_nao_aceita_valor_muito_distante(): void {
        $this->given_default_configs( array(
            'lkn_give_cryptocurrency_enable_error_margin' => 'enabled',
            'lkn_give_cryptocurrency_error_margin'        => 2,
        ) );

        $this->mock_blockcypher( array(
            array(
                'total'     => 100000,
                'fees'      => 0,
                'addresses' => array( self::WALLET_BTC ),
            ),
        ) );

        CryptoApi::transaction_verification( 84, 'bitcoin', '0.00050000', self::WALLET_BTC, 'btc' );

        $this->assertSame( 'Pending payment', $this->stored_payment_response( 84, self::META_KEY )['status'] );
    }

    public function test_ethereum_usa_18_casas_decimais(): void {
        $this->mock_blockcypher( array(
            array(
                'total'     => 1000,
                'fees'      => 0,
                'addresses' => array( self::WALLET_ETH ),
            ),
        ) );

        // 1e-15 ETH = 1000 wei.
        CryptoApi::transaction_verification( 85, 'ethereum', '0.000000000000001', self::WALLET_ETH, 'eth' );

        $meta = $this->stored_payment_response( 85, self::META_KEY );

        $this->assertSame( 'Completed', $meta['status'] );
        $this->assertSame( 'eth', $meta['cripto'] );
    }

    public function test_verifica_apenas_as_cinco_transacoes_mais_recentes(): void {
        $txs = array();

        for ( $index = 0; $index < 5; $index++ ) {
            $txs[] = array(
                'total'     => 1,
                'fees'      => 0,
                'addresses' => array( 'outra-carteira-' . $index ),
            );
        }

        // A transação que casaria fica na 6ª posição: fora do limite de consulta.
        $txs[] = array(
            'total'     => 10000,
            'fees'      => 0,
            'addresses' => array( self::WALLET_BTC ),
        );

        $this->mock_blockcypher( $txs, 6 );

        CryptoApi::transaction_verification( 86, 'bitcoin', '0.00010000', self::WALLET_BTC, 'btc' );

        $this->assertSame( 'Pending payment', $this->stored_payment_response( 86, self::META_KEY )['status'] );
    }

    public function test_api_fora_do_ar_nao_quebra_e_mantem_pendente(): void {
        // Sem mock: WP_Error → corpo vazio → json_decode(null).
        CryptoApi::transaction_verification( 87, 'bitcoin', '0.00010000', self::WALLET_BTC, 'btc' );

        $this->assertSame( 'Pending payment', $this->stored_payment_response( 87, self::META_KEY )['status'] );
        $this->assertSame( array(), Lkngc_Give_Stub_Store::$status_updates );
        $this->assertCount( 1, Lkngc_Give_Stub_Store::$success_pages );
    }

    public function test_grava_o_log_quando_o_debug_esta_ligado(): void {
        $this->given_default_configs( array( 'lkn_give_cryptocurrency_debug' => 'enabled' ) );

        $this->mock_blockcypher( array(
            array(
                'total'     => 10000,
                'fees'      => 0,
                'addresses' => array( self::WALLET_BTC ),
            ),
        ) );

        CryptoApi::transaction_verification( 88, 'bitcoin', '0.00010000', self::WALLET_BTC, 'btc' );

        $this->assertNotEmpty( Lkngc_Give_Stub_Store::$logs );
        $this->assertSame( 88, Lkngc_Give_Stub_Store::$logs[0]['context']['donation_id'] );
    }
}
