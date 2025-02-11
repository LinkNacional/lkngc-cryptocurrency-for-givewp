<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also Includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.linknacional.com.br/
 * @since             1.0.0
 * @package           Lkngc_Give_Cryptocurrency
 *
 * @wordpress-plugin
 * Plugin Name:       Cryptocurrency Payment for GiveWP
 * Plugin URI:        https://www.linknacional.com.br/wordpress/givewp/criptomoedas/
 * Description:       Payment via Cryptocurrencies.
 * Version:           4.0.2
 * Author:            Link Nacional
 * Author URI:        https://www.linknacional.com.br/
 * Requires Plugins: give
 * License:           GPL-3.0+
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       lkngc-cryptocurrency-for-givewp
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    die;
}

require_once 'vendor/autoload.php';
// use Lkngc\CryptocurrencyForGivewp\Includes\Bootstrap\LkngcCryptocurrencyForGivewp;

use Lkngc\CryptocurrencyForGivewp\Includes\LkngcCryptocurrencyForGivewp;
use Lkngc\CryptocurrencyForGivewp\Includes\LkngcCryptocurrencyForGivewpActivator;
use Lkngc\CryptocurrencyForGivewp\Includes\LkngcCryptocurrencyForGivewpDeactivator;

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('LKNGC_GIVE_CRYPTOCURRENCY_FOR_GIVEWP_VERSION', '4.0.2');

define('LKNGC_GIVE_CRYPTOCURRENCY_FOR_GIVEWP_MIN_GIVE_VERSION', '2.3.0');

define('LKNGC_GIVE_CRYPTOCURRENCY_FOR_GIVEWP_FILE', __FILE__);

define('LKNGC_GIVE_CRYPTOCURRENCY_FOR_GIVEWP_DIR', plugin_dir_path(LKNGC_GIVE_CRYPTOCURRENCY_FOR_GIVEWP_FILE));

define('LKNGC_GIVE_CRYPTOCURRENCY_FOR_GIVEWP_URL', plugin_dir_url(LKNGC_GIVE_CRYPTOCURRENCY_FOR_GIVEWP_FILE));

define('LKNGC_GIVE_CRYPTOCURRENCY_FOR_GIVEWP_BASENAME', plugin_basename(LKNGC_GIVE_CRYPTOCURRENCY_FOR_GIVEWP_FILE));

define('LKNGC_GIVE_CRYPTOCURRENCY_FOR_GIVEWP_TEXT_DOMAIN', 'lkngc-cryptocurrency-for-givewp');

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    4.0.0
 */
function lkngc_cryptocurrency_for_givewp_activate(): void {
    LkngcCryptocurrencyForGivewpActivator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-lknmp-mercadopago-for-givewp-deactivator.php
 */
function lkngc_cryptocurrency_for_givewp_deactivate(): void {
    LkngcCryptocurrencyForGivewpDeactivator::deactivate();
}

register_activation_hook( __FILE__, 'lkngc_cryptocurrency_for_givewp_activate' );
register_deactivation_hook( __FILE__, 'lkngc_cryptocurrency_for_givewp_deactivate');

function lkngc_cryptocurrency_for_givewp_run(): void {
    $plugin = new LkngcCryptocurrencyForGivewp();
    $plugin->run();
}

lkngc_cryptocurrency_for_givewp_run();