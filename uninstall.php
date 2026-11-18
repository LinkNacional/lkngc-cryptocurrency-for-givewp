<?php

/**
 * Uninstall Cryptocurrency Payment for GiveWP.
 *
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 *
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// O Give pode já ter sido removido: sem a API de settings não há o que limpar.
if ( ! function_exists('give_get_settings') || ! function_exists('give_delete_option')) {
    return;
}

$lkngc_cryptocurrency_options = give_get_settings();

if ( ! is_array($lkngc_cryptocurrency_options)) {
    return;
}

/*
 * Mantém apenas as chaves do plugin. O prefixo é `lkn_give_cryptocurrency`
 * (nome herdado das versões anteriores ao slug atual) — mudar aqui deixaria
 * as configurações órfãs no banco.
 */
$lkngc_cryptocurrency_settings = array_filter($lkngc_cryptocurrency_options, function ($lkngc_key) {
    return 0 === strpos($lkngc_key, 'lkn_give_cryptocurrency');
}, ARRAY_FILTER_USE_KEY);

foreach (array_keys($lkngc_cryptocurrency_settings) as $lkngc_setting) {
    give_delete_option($lkngc_setting);
}
