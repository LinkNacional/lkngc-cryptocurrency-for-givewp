<?php
/**
 * Base dos testes integrados do plugin.
 *
 * @package Lkngc\CryptocurrencyForGivewp\Tests
 */

namespace Lkngc\CryptocurrencyForGivewp\Tests;

use Lkngc_Give_Stub_Store;
use ReflectionClass;
use WP_UnitTestCase;

abstract class TestCase extends WP_UnitTestCase {
    /**
     * URLs capturadas pelo mock de HTTP, na ordem das chamadas.
     *
     * @var array<int, string>
     */
    protected $http_requests = array();

    /**
     * Args das requisições capturadas pelo mock de HTTP.
     *
     * @var array<int, array<string, mixed>>
     */
    protected $http_args = array();

    /**
     * Mapa "substring da URL" => resposta do mock.
     *
     * @var array<string, array{code:int, body:string}>
     */
    private $http_map = array();

    protected function setUp(): void {
        parent::setUp();

        Lkngc_Give_Stub_Store::reset();

        $this->http_requests = array();
        $this->http_args     = array();
        $this->http_map      = array();

        // Sem rede nos testes: qualquer requisição não mapeada vira WP_Error.
        add_filter( 'pre_http_request', array( $this, 'filter_pre_http_request' ), 10, 3 );
    }

    protected function tearDown(): void {
        remove_filter( 'pre_http_request', array( $this, 'filter_pre_http_request' ), 10 );

        parent::tearDown();
    }

    /**
     * Mock de `wp_remote_get()`: responde pelas substrings cadastradas.
     *
     * @param array<string, array{code:int, body:string}> $map Substring da URL => resposta.
     *
     * @return void
     */
    protected function mock_http( array $map ): void {
        $this->http_map = $map;
    }

    /**
     * Callback do filtro `pre_http_request`.
     *
     * @param mixed                $preempt Resposta antecipada.
     * @param array<string, mixed> $args    Args da requisição.
     * @param string               $url     URL requisitada.
     *
     * @return mixed
     */
    public function filter_pre_http_request( $preempt, $args, $url ) {
        $this->http_requests[] = $url;
        $this->http_args[]     = $args;

        foreach ( $this->http_map as $needle => $response ) {
            if ( false !== strpos( $url, $needle ) ) {
                return array(
                    'headers'  => array(),
                    'body'     => $response['body'],
                    'response' => array(
                        'code'    => $response['code'],
                        'message' => 'OK',
                    ),
                    'cookies'  => array(),
                    'filename' => null,
                );
            }
        }

        return new \WP_Error( 'no_mock', 'Sem mock para ' . $url );
    }

    /**
     * Quantidade de requisições HTTP feitas no teste.
     */
    protected function http_request_count(): int {
        return count( $this->http_requests );
    }

    /**
     * Última URL requisitada.
     */
    protected function last_http_url(): string {
        return (string) end( $this->http_requests );
    }

    /**
     * Atalho para definir várias opções do Give de uma vez.
     *
     * @param array<string, mixed> $options Chave => valor.
     *
     * @return void
     */
    protected function given_options( array $options ): void {
        foreach ( $options as $key => $value ) {
            Lkngc_Give_Stub_Store::$options[ $key ] = $value;
        }
    }

    /**
     * Configuração "padrão" do plugin (debug desligado, sem margem de erro,
     * carteiras preenchidas).
     *
     * @param array<string, mixed> $overrides Opções extras/sobrescritas.
     *
     * @return void
     */
    protected function given_default_configs( array $overrides = array() ): void {
        $this->given_options( array_merge(
            array(
                'lkn_give_cryptocurrency_bitcoin_wallet'     => '1BoatSLRHtKNngkdXEeobR76b53LETtpyT',
                'lkn_give_cryptocurrency_ethereum_wallet'    => '0x0000000000000000000000000000000000000001',
                'lkn_give_cryptocurrency_enable_error_margin' => 'disabled',
                'lkn_give_cryptocurrency_error_margin'       => 0,
                'lkn_give_cryptocurrency_debug'              => 'disabled',
                'lkn_give_cryptocurrency_debug_advanced'     => 'disabled',
                'decimal_separator'                          => ',',
                'thousands_separator'                        => '.',
                'currency'                                   => 'BRL',
            ),
            $overrides
        ) );
    }

    /**
     * Meta gravada por `give_update_payment_meta()`.
     *
     * @param int    $donation_id ID da doação.
     * @param string $key         Chave.
     *
     * @return mixed
     */
    protected function stored_meta( int $donation_id, string $key ) {
        return isset( Lkngc_Give_Stub_Store::$meta[ $donation_id ][ $key ] )
            ? Lkngc_Give_Stub_Store::$meta[ $donation_id ][ $key ]
            : null;
    }

    /**
     * JSON gravado na meta de resposta do gateway.
     *
     * @param int    $donation_id ID da doação.
     * @param string $key         Chave da meta.
     *
     * @return array<string, mixed>|null
     */
    protected function stored_payment_response( int $donation_id, string $key = 'lkngc_cryptocurrency_for_givewp_response' ): ?array {
        $raw = $this->stored_meta( $donation_id, $key );

        if ( ! is_string( $raw ) ) {
            return null;
        }

        $decoded = json_decode( $raw, true );

        return is_array( $decoded ) ? $decoded : null;
    }

    /**
     * Cria uma doação no stub (`Give\Donations\Models\Donation`) e a registra
     * no store, para que `Donation::find()` a encontre.
     *
     * @param int $donation_id ID da doação.
     *
     * @return \Give\Donations\Models\Donation
     */
    protected function given_donation( int $donation_id ) {
        $donation = new \Give\Donations\Models\Donation( $donation_id );
        $donation->save();

        return $donation;
    }

    /**
     * Resposta da BlockCypher (formato `/{coin}/main/addrs/{wallet}/full`).
     *
     * @param array<int, array{total:int, fees:int, addresses:array<int, string>, hash?:string}> $txs Transações.
     * @param int|null                                                                            $n_tx Força o `n_tx` (default: count($txs)).
     *
     * @return string
     */
    protected function blockcypher_body( array $txs, ?int $n_tx = null ): string {
        $formatted = array();

        foreach ( $txs as $index => $tx ) {
            $outputs = array();

            foreach ( $tx['addresses'] as $address ) {
                $outputs[] = array( 'addresses' => array( $address ) );
            }

            $formatted[] = array(
                'total'   => $tx['total'],
                'fees'    => $tx['fees'],
                'hash'    => isset( $tx['hash'] ) ? $tx['hash'] : 'hash-' . $index,
                'outputs' => $outputs,
            );
        }

        return wp_json_encode( array(
            'n_tx' => null === $n_tx ? count( $formatted ) : $n_tx,
            'txs'  => $formatted,
        ) );
    }

    /**
     * Lê uma propriedade protegida/privada (ex.: `$loader` de classes core).
     *
     * @param object $object   Objeto.
     * @param string $property Nome da propriedade.
     *
     * @return mixed
     */
    protected function read_property( $object, string $property ) {
        $reflection = new ReflectionClass( $object );
        $prop       = $reflection->getProperty( $property );
        $prop->setAccessible( true );

        return $prop->getValue( $object );
    }
}
