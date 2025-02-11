<?php

namespace Lkngc\CryptocurrencyForGivewp\PublicView;

use Lkngc\CryptocurrencyForGivewp\Includes\LkngcCryptocurrencyForGivewpHelper;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.linknacional.com.br/
 * @since      3.0.0
 *
 * @package    Lkn_Give_Cryptocurrency
 * @subpackage Lkn_Give_Cryptocurrency/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Lkn_Give_Cryptocurrency
 * @subpackage Lkn_Give_Cryptocurrency/public
 * @author     Link Nacional <ticket@linknacional.com>
 */
final class LkngcCryptocurrencyForGivewpPublic {
    /**
     * The ID of this plugin.
     *
     * @since    3.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    3.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    3.0.0
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts(): void {
        wp_enqueue_script(
            'lkngc-cryptocurrency-for-givewp-public-js',
            plugin_dir_url(__FILE__) . 'js/lkngc-cryptocurrency-for-givewp-public.js',
            array('jquery'),
            $this->version,
            true
        );

        $wallets = LkngcCryptocurrencyForGivewpHelper::get_wallets();
        wp_localize_script('lkngc-cryptocurrency-for-givewp-public-js', 'wallet', $wallets);

        wp_enqueue_script(
            'lkngc-cryptocurrency-for-givewp-qrcode-js',
            plugin_dir_url(__FILE__) . 'js/qrcode.min.js',
            array('jquery'),
            $this->version,
            true
        );
    }

    /**
     * Register the CSS for the public-facing side of the site.
     *
     * @since    3.0.0
     */
    public function enqueue_styles(): void {
        wp_enqueue_style(
            'lkngc-cryptocurrency-for-givewp-public-css',
            plugin_dir_url(__FILE__) . 'css/lkngc-cryptocurrency-for-givewp-public.css',
            array(),
            $this->version,
            'all'
        );
    }
}
