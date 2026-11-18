<?php
/**
 * Testes do núcleo do plugin: constantes, loader, hooks registrados,
 * internacionalização e ciclo de ativação/desativação.
 *
 * @package Lkngc\CryptocurrencyForGivewp\Tests
 */

namespace Lkngc\CryptocurrencyForGivewp\Tests;

use Lkngc\CryptocurrencyForGivewp\Includes\LkngcCryptocurrencyForGivewp;
use Lkngc\CryptocurrencyForGivewp\Includes\LkngcCryptocurrencyForGivewpActivator;
use Lkngc\CryptocurrencyForGivewp\Includes\LkngcCryptocurrencyForGivewpDeactivator;
use Lkngc\CryptocurrencyForGivewp\Includes\LkngcCryptocurrencyForGivewpLoader;
use Lkngc\CryptocurrencyForGivewp\Includes\LkngcCryptocurrencyForGivewpi18n;

/**
 * @covers \Lkngc\CryptocurrencyForGivewp\Includes\LkngcCryptocurrencyForGivewp
 * @covers \Lkngc\CryptocurrencyForGivewp\Includes\LkngcCryptocurrencyForGivewpLoader
 * @covers \Lkngc\CryptocurrencyForGivewp\Includes\LkngcCryptocurrencyForGivewpi18n
 * @covers \Lkngc\CryptocurrencyForGivewp\Includes\LkngcCryptocurrencyForGivewpActivator
 * @covers \Lkngc\CryptocurrencyForGivewp\Includes\LkngcCryptocurrencyForGivewpDeactivator
 */
final class PluginCoreTest extends TestCase {
    public function test_constantes_do_plugin(): void {
        $this->assertSame( '4.0.4', LKNGC_GIVE_CRYPTOCURRENCY_FOR_GIVEWP_VERSION );
        $this->assertSame( '2.3.0', LKNGC_GIVE_CRYPTOCURRENCY_FOR_GIVEWP_MIN_GIVE_VERSION );
        $this->assertSame( 'lkngc-cryptocurrency-for-givewp', LKNGC_GIVE_CRYPTOCURRENCY_FOR_GIVEWP_TEXT_DOMAIN );
        $this->assertSame(
            'lkngc-cryptocurrency-for-givewp/lkngc-cryptocurrency-for-givewp.php',
            LKNGC_GIVE_CRYPTOCURRENCY_FOR_GIVEWP_BASENAME
        );
        $this->assertStringEndsWith( '/lkngc-cryptocurrency-for-givewp/', LKNGC_GIVE_CRYPTOCURRENCY_FOR_GIVEWP_DIR );
        $this->assertStringEndsWith( '/lkngc-cryptocurrency-for-givewp/', LKNGC_GIVE_CRYPTOCURRENCY_FOR_GIVEWP_URL );
    }

    public function test_versao_da_classe_core_e_a_do_cabecalho(): void {
        $core = new LkngcCryptocurrencyForGivewp();

        $this->assertSame( LKNGC_GIVE_CRYPTOCURRENCY_FOR_GIVEWP_VERSION, $core->get_version() );
        $this->assertSame( 'lkngc-cryptocurrency-for-givewp', $core->get_plugin_name() );
    }

    public function test_run_registra_os_hooks_do_plugin(): void {
        $core = new LkngcCryptocurrencyForGivewp();
        $core->run();

        $this->assertInstanceOf( LkngcCryptocurrencyForGivewpLoader::class, $core->get_loader() );

        $this->assertNotFalse( has_action( 'plugins_loaded' ) );
        $this->assertNotFalse( has_action( 'admin_enqueue_scripts' ) );
        $this->assertNotFalse( has_action( 'give_view_donation_details_billing_after' ) );
        $this->assertNotFalse( has_action( 'givewp_register_payment_gateway' ) );
        $this->assertNotFalse( has_action( 'rest_api_init' ) );
        $this->assertNotFalse( has_action( 'wp_enqueue_scripts' ) );
        $this->assertNotFalse( has_filter( 'give_get_settings_gateways' ) );
        $this->assertNotFalse( has_filter( 'give_get_sections_gateways' ) );
        $this->assertNotFalse(
            has_filter( 'plugin_action_links_' . LKNGC_GIVE_CRYPTOCURRENCY_FOR_GIVEWP_BASENAME )
        );
    }

    public function test_registra_os_dois_gateways_no_give(): void {
        $core = new LkngcCryptocurrencyForGivewp();

        $registry = new class() {
            /** @var array<int, string> */
            public $gateways = array();

            /**
             * @param string $class_name Classe do gateway.
             *
             * @return void
             */
            public function registerGateway( $class_name ) {
                $this->gateways[] = $class_name;
            }
        };

        $core->new_gateway_register( $registry );

        $this->assertSame(
            array(
                'Lkngc\CryptocurrencyForGivewp\PublicView\LkngcCryptocurrencyForGivewpBitcoin',
                'Lkngc\CryptocurrencyForGivewp\PublicView\LkngcCryptocurrencyForGivewpEthereum',
            ),
            $registry->gateways
        );
    }

    public function test_loader_registra_actions_e_filters_com_prioridade_e_args(): void {
        $loader    = new LkngcCryptocurrencyForGivewpLoader();
        $component = new class() {
            /**
             * @return void
             */
            public function callback_action() {
            }

            /**
             * @return void
             */
            public function callback_filter() {
            }
        };

        $loader->add_action( 'lkngc_action_teste', $component, 'callback_action', 20, 2 );
        $loader->add_filter( 'lkngc_filter_teste', $component, 'callback_filter' );
        $loader->run();

        $this->assertSame( 20, has_action( 'lkngc_action_teste', array( $component, 'callback_action' ) ) );
        $this->assertSame( 10, has_filter( 'lkngc_filter_teste', array( $component, 'callback_filter' ) ) );
    }

    public function test_i18n_carrega_o_textdomain_sem_erro(): void {
        $i18n = new LkngcCryptocurrencyForGivewpi18n();

        $this->assertTrue( method_exists( $i18n, 'load_plugin_textdomain' ) );

        // O método não retorna nada; o que importa é não disparar aviso/erro.
        $this->assertNull( $i18n->load_plugin_textdomain() );
    }

    public function test_arquivos_de_traducao_existem(): void {
        $languages = LKNGC_GIVE_CRYPTOCURRENCY_FOR_GIVEWP_DIR . 'languages/';

        $this->assertFileExists( $languages . 'lkngc-cryptocurrency-for-givewp.pot' );
        $this->assertFileExists( $languages . 'lkngc-cryptocurrency-for-givewp-pt_BR.po' );
        $this->assertFileExists( $languages . 'lkngc-cryptocurrency-for-givewp-pt_BR.mo' );
    }

    public function test_ativacao_e_desativacao_nao_quebram(): void {
        $this->assertNull( LkngcCryptocurrencyForGivewpActivator::activate() );
        $this->assertNull( LkngcCryptocurrencyForGivewpDeactivator::deactivate() );
    }
}
