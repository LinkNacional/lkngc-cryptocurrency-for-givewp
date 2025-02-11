<?php

namespace Lkngc\CryptocurrencyForGivewp\Includes;
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.linknacional.com.br/wordpress/givewp/
 * @since      1.0.0
 *
 * @package   LkngcCryptocurrencyForGivewp
 * @subpackage LkngcCryptocurrencyForGivewp/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    LkngcCryptocurrencyForGivewp
 * @subpackage LkngcCryptocurrencyForGivewp/includes
 * @author     Link Nacional <contato@linknacional>
 */
final class LkngcCryptocurrencyForGivewpi18n {
    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain(): void {
        load_plugin_textdomain(
            'lkngc-cryptocurrency-for-givewp',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }
}
