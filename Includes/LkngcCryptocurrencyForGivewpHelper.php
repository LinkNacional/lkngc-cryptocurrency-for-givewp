<?php

namespace Lkngc\CryptocurrencyForGivewp\Includes;
use Give\Log\Log;

// Exit, if accessed directly.
if ( ! defined('ABSPATH')) {
    exit;
}

/**
 * @see        https://www.linknacional.com.br/
 * @author     Link Nacional
 */
final class LkngcCryptocurrencyForGivewpHelper {
    /**
     * Get plugin settings.
     *
     * @since 1.0.0
     * @return array
     */
    final public static function get_configs() {
        $configs = array();

        $configs['basePath'] = LKNGC_GIVE_CRYPTOCURRENCY_FOR_GIVEWP_DIR . 'Includes/logs';
        $configs['base'] = $configs['basePath'] . '/' . gmdate('d.m.Y-H.i.s') . '.log';

        $configs['bitcoin_wallet'] = give_get_option('lkn_give_cryptocurrency_bitcoin_wallet');
        $configs['ethereum_wallet'] = give_get_option('lkn_give_cryptocurrency_ethereum_wallet');
        $configs['enable_error_margin'] = give_get_option('lkn_give_cryptocurrency_enable_error_margin');
        $configs['error_margin'] = give_get_option('lkn_give_cryptocurrency_error_margin');
        $configs['debug'] = give_get_option('lkn_give_cryptocurrency_debug');
        $configs['debug_advanced'] = give_get_option('lkn_give_cryptocurrency_debug_advanced');

        return $configs;
    }

    final public static function get_wallets() {
        $wallets = array();

        $wallets['bitcoin'] = give_get_option('lkn_give_cryptocurrency_bitcoin_wallet');
        $wallets['ethereum'] = give_get_option('lkn_give_cryptocurrency_ethereum_wallet');

        return $wallets;
    }

    /**
     * Check plugin environment and show plugin dependency notice.
     *
     * @since 1.0.0
     * @return bool|null
     */
    final public static function plugin_row_meta($plugin_meta) {
        $new_meta_links['setting'] = sprintf(
            '<a href="%1$s">%2$s</a>',
            admin_url('edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=lkngc-cryptocurrency-for-givewp'),
            'Configurações',
            'lkngc-cryptocurrency-for-givewp'
        );

        return array_merge($plugin_meta, $new_meta_links);
    }

    final public static function dependency_notice(): void {
        // Admin notice.
        $message = sprintf(
            '<div class="notice notice-error"><p><strong>%1$s</strong> %2$s <a href="%3$s" target="_blank">%4$s</a> %5$s %6$s+ %7$s.</p></div>',
            esc_html('Erro de ativação:'),
            esc_html('Você precisa ter o'),
            esc_url('https://givewp.com'),
            esc_html('Give'),
            esc_html('instalado e ativo versão'),
            esc_html(LKNGC_GIVE_CRYPTOCURRENCY_FOR_GIVEWP_MIN_GIVE_VERSION),
            esc_html('para o plugin CryptoCurrency Payment for GiveWP ativar')
        );

        echo wp_kses_post($message);
    }

    /**
     * Make log.
     *
     * @since 1.0.0
     *
     * @param   int       $donation_id
     * @param   array     $data
     * @return  void
     */
    public static function log(int $donation_id, array $data): void {
        if (LkngcCryptocurrencyForGivewpHelper::get_configs()['debug'] === 'disabled') {
            return;
        }

        // Register the logs on GiveWP Logs.
        Log::error('Cryptocurrency Payment Log', array(
            'source' => 'Cryptocurrency Payment for GiveWP plugin',
            'donation_id' => $donation_id,
            'data' => $data
        ));
    }
}
