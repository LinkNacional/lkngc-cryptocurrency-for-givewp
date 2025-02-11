<?php

namespace Lkngc\CryptocurrencyForGivewp\Includes;

use Lkngc\CryptocurrencyForGivewp\Admin\LkngcCryptocurrencyForGivewpAdmin;
use Lkngc\CryptocurrencyForGivewp\PublicView\LkngcCryptocurrencyForGivewpBitcoin;
use Lkngc\CryptocurrencyForGivewp\PublicView\LkngcCryptocurrencyForGivewpEthereum;
use Lkngc\CryptocurrencyForGivewp\PublicView\LkngcCryptocurrencyForGivewpPublic;

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.linknacional.com.br/wordpress/givewp/
 * @since      1.0.0
 *
 * @package    Lknmp_Mercadopago_For_Givewp
 * @subpackage Lknmp_Mercadopago_For_Givewp/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Lknmp_Mercadopago_For_Givewp
 * @subpackage Lknmp_Mercadopago_For_Givewp/includes
 * @author     Link Nacional <contato@linknacional>
 */
final class LkngcCryptocurrencyForGivewp {
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      LkngcCryptocurrencyForGivewpLoader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string();ing    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if (defined('LKNGC_GIVE_CRYPTO_CURRENCY_VERSION')) {
            $this->version = LKNGC_GIVE_CRYPTOCURRENCY_FOR_GIVEWP_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'lkngc-cryptocurrency-for-givewp';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Lkngc_CryptocurrencyForGivewp_Loader. Orchestrates the hooks of the plugin.
     * - Lkngc_CryptocurrencyForGivewp_i18n. Defines internationalization functionality.
     * - Lkngc_CryptocurrencyForGivewp_Admin. Defines all hooks for the admin area.
     * - Lkngc_CryptocurrencyForGivewp_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies(): void {
        $this->loader = new LkngcCryptocurrencyForGivewpLoader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Lknmp_Mercadopago_For_Givewp_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale(): void {
        $plugin_i18n = new LkngcCryptocurrencyForGivewpi18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks(): void {
        $plugin_admin = new LkngcCryptocurrencyForGivewpAdmin($this->get_plugin_name(), $this->get_version());
        $this->loader->add_filter('plugin_action_links_' . LKNGC_GIVE_CRYPTOCURRENCY_FOR_GIVEWP_BASENAME, 'Lkngc\CryptocurrencyForGivewp\Includes\LkngcCryptocurrencyForGivewpHelper', 'plugin_row_meta', 10, 2);
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('give_view_donation_details_billing_after', $plugin_admin, 'lkngc_cryptocurrency_for_givewp_donation_details');
        $this->loader->add_action('givewp_register_payment_gateway', $this, 'new_gateway_register');
        $this->loader->add_filter('give_get_settings_gateways', $plugin_admin, 'lkngc_add_setting_into_new_section');
        $this->loader->add_filter('give_get_sections_gateways', $plugin_admin, 'lkngc_new_setting_section');
        $this->loader->add_action( 'rest_api_init', $this, 'lkngc_give_crypto_register_routes' );
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks(): void {
        $plugin_public = new LkngcCryptocurrencyForGivewpPublic($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        //$this->loader->add_action('givewp_register_payment_gateway', $this, 'new_gateway_register');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run(): void {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    LkngcCryptocurrencyForGivewpLoader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }

    /**
     * Register gateway to new GiveWP v3
     *
     * @since 3.0.0
     *
     * @param  PaymentGatewayRegister $paymentGatewayRegister
     *
     * @return void
     */
    public function new_gateway_register($paymentGatewayRegister) :void {
        // TODO: Futura implementação - utilizar um Abstract no Gateway
        $paymentGatewayRegister->registerGateway('Lkngc\CryptocurrencyForGivewp\PublicView\LkngcCryptocurrencyForGivewpBitcoin');
        $paymentGatewayRegister->registerGateway('Lkngc\CryptocurrencyForGivewp\PublicView\LkngcCryptocurrencyForGivewpEthereum');
    }

    public function lkngc_give_crypto_register_routes(): void {
        register_rest_route('lkngc-cryptocurrency-for-givewp-verification/v1', '/notification', array(
            'methods' => 'POST',
            'callback' => array('Lkngc\CryptocurrencyForGivewp\Includes\CryptoApi', 'btn_transaction_verification'),
            'permission_callback' => __return_empty_string(),
        ));
    }
}