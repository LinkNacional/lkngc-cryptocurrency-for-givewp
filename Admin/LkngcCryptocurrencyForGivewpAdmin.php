<?php

namespace Lkngc\CryptocurrencyForGivewp\Admin;

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    Lkngc_Give_CryptoCurrency
 * @subpackage Lkngc_Give_CryptoCurrency/admin
 * @author     Link Nacional <ticket@linknacional.com>
 */
final class LkngcCryptocurrencyForGivewpAdmin {
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version
     */
    private $version;

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    3.0.0
     * @access   protected
     * @var      \Lkngc\CryptocurrencyForGivewp\Includes\LkngcCryptocurrencyForGivewpLoader    $loader
     */
    protected $loader;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name
     * @param    string    $version
     */
    public function __construct($plugin_name, $loader) {
        $this->plugin_name = $plugin_name;
        $this->loader = $loader;
    }

    /**
     * Function to set the admin settings of plugin, and merge with pro plugin settings.
     *
     * @param    array    $settings
     * @return   array    $settings
     */
    public function lkngc_add_setting_into_new_section($settings) {
        switch (give_get_current_setting_section()) {
            case 'lkngc-cryptocurrency-for-givewp':

                $settings[] = array(
                    'type' => 'title',
                    'id' => 'lkn_give_cryptocurrency',
                );

                $settings[] = array(
                    'name' => __('Bitcoin Wallet', 'lkngc-cryptocurrency-for-givewp'),
                    'id' => 'lkn_give_cryptocurrency_bitcoin_wallet',
                    'desc' => __('The wallet where the Bitcoins received from the form will be stored', 'lkngc-cryptocurrency-for-givewp'),
                    'type' => 'text',
                );

                $settings[] = array(
                    'name' => __('Ethereum Wallet', 'lkngc-cryptocurrency-for-givewp'),
                    'id' => 'lkn_give_cryptocurrency_ethereum_wallet',
                    'desc' => __('The wallet where the Ether received from the form will be stored', 'lkngc-cryptocurrency-for-givewp'),
                    'type' => 'text',
                );

                $settings[] = array(
                    'name' => __('Enable error margin', 'lkngc-cryptocurrency-for-givewp'),
                    'id' => 'lkn_give_cryptocurrency_enable_error_margin',
                    'desc' => __('Enable error margin for transaction verification.', 'lkngc-cryptocurrency-for-givewp'),
                    'type' => 'radio',
                    'default' => 'disabled',
                    'options' => array(
                        'enabled' => __('Enable', 'lkngc-cryptocurrency-for-givewp'),
                        'disabled' => __('Disable', 'lkngc-cryptocurrency-for-givewp'),
                    ),
                );

                $settings[] = array(
                    'name' => __('Error margin', 'lkngc-cryptocurrency-for-givewp'),
                    'id' => 'lkn_give_cryptocurrency_error_margin',
                    'desc' => __('Define the error margin for transaction verification (ex: 2, for 2%).', 'lkngc-cryptocurrency-for-givewp'),
                    'type' => 'number',
                );

                $settings[] = array(
                    'name' => __('Debug Mode', 'lkngc-cryptocurrency-for-givewp'),
                    'id' => 'lkn_give_cryptocurrency_debug',
                    'desc' => __('Enable environment for Debug.', 'lkngc-cryptocurrency-for-givewp'),
                    'type' => 'radio',
                    'default' => 'disabled',
                    'options' => array(
                        'enabled' => __('Enable', 'lkngc-cryptocurrency-for-givewp'),
                        'disabled' => __('Disable', 'lkngc-cryptocurrency-for-givewp'),
                    ),
                );

                $settings[] = array(
                    'name' => __('Advanced Debug Mode', 'lkngc-cryptocurrency-for-givewp'),
                    'id' => 'lkn_give_cryptocurrency_debug_advanced',
                    'desc' => __('Enable environment for Debug in console JS', 'lkngc-cryptocurrency-for-givewp'),
                    'type' => 'radio',
                    'default' => 'disabled',
                    'options' => array(
                        'enabled' => __('Enable', 'lkngc-cryptocurrency-for-givewp'),
                        'disabled' => __('Disable', 'lkngc-cryptocurrency-for-givewp'),
                    ),
                );

                $settings[] = array(
                    'id' => 'lkn_give_cryptocurrency',
                    'type' => 'sectionend',
                );

                // $pro_settings = apply_filters('lkn_give_cryptocurrency_pro_settings', array());

                // if (! empty($pro_settings)) {
                //     $settings = array_merge($pro_settings, $settings);
                // }

                break;
        }// // End switch()

        return $settings;
    }

    /**
     * Add new section to "General" setting tab
     *
     * @param $sections
     *
     * @return array
     */
    public function lkngc_new_setting_section($sections) {
        $sections['lkngc-cryptocurrency-for-givewp'] = 'CriptoCurrencies';
        return $sections;
    }

    /**
     * Function to show the Donation Details in Donation Area.
     *
     * @param    int    $payment_id
     * @return   void
     */
    public function lkngc_cryptocurrency_for_givewp_donation_details(int $payment_id): void {
        $metadata = json_decode(give_get_meta($payment_id, "lkngc_cryptocurrency_for_givewp_response", true));

        if (isset($metadata)) {
            load_template(
                LKNGC_GIVE_CRYPTOCURRENCY_FOR_GIVEWP_DIR . 'Admin/partials/DonationDetailsTemplate.php',
                true,
                array(
                    'box_title' => __('Donation Details', 'lkngc-cryptocurrency-for-givewp'),
                    'return_msg_label' => __('Return Message:', 'lkngc-cryptocurrency-for-givewp'),
                    'return_msg' => $metadata->status,
                    'return_id_label' => __('Return identifier:', 'lkngc-cryptocurrency-for-givewp'),
                    'return_id' => $metadata->transactionId,
                    'crypto_value_label' => __('Criptocurrency value:', 'lkngc-cryptocurrency-for-givewp'),
                    'crypto_value' => $metadata->totalCrypto,
                    'wallet_label' => __('Wallet:', 'lkngc-cryptocurrency-for-givewp'),
                    'wallet' => $metadata->wallet,
                    'button_label' => __('Consult Transaction', 'lkngc-cryptocurrency-for-givewp'),
                    'cripto_label' => __('Criptocurrency:', 'lkngc-cryptocurrency-for-givewp'),
                    'crypto_type' => $metadata->crypto,
                )
            );
        }
    }

    /**
     * Notice for No Core Activation.
     *
     * @since 1.0.0
     */
    public static function give_inactive_notice(): void {
        // Admin notice.
        $message = sprintf(
            '<div class="notice notice-error"><p><strong>%1$s</strong> %2$s <a href="%3$s" target="_blank">%4$s</a> %5$s.</p></div>',
            __('Activation Error:', 'lkngc-cryptocurrency-for-givewp'),
            __('You must have', 'lkngc-cryptocurrency-for-givewp'),
            'https://givewp.com',
            'Give',
            __('plugin installed and activated for the Give Cryptocurrency add-on to activate', 'lkngc-cryptocurrency-for-givewp')
        );

        echo wp_kses_post($message);
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts(): void {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Lkn_Form_Customization_for_Give_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Lkn_Form_Customization_for_Give_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/lkngc-cryptocurrency-for-givewp-admin.js', array('jquery'), $this->version, true);

        $lknCryptoArray = array(
            'token' => wp_hash(gmdate('dmYH') . 'notification'),
            'msg_error' => __('[Query result]: Request error - Unable to verify', 'lkngc-cryptocurrency-for-givewp')
        );

        wp_localize_script($this->plugin_name, 'lknCryptoGlobal', $lknCryptoArray);
    }
}
