<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 *
 * This file is used to markup the Donation Details in admin area of the plugin.
 *
 * @link       https://www.linknacional.com.br/wordpress/plugins/
 * @since      1.0.0
 *
 */
?>

<div
    id="give-criptocurrency-donation-details"
    class="postbox"
>
    <h3 class="hndle">
        <?php esc_html($args['box_title']) ?>
    </h3>

    <div
        id="lkn-give-criptocurrency-meta-wrap"
        class="give-order-gateway give-admin-box-inside lkn-hidden"
    >
        <div>
            <p id="lkn-give-criptocurrency-meta-msg">
                <strong><?php echo esc_html($args['return_msg_label']) ?></strong>
                <?php echo esc_html($args['return_msg']) ?>
            </p>
            <p id="lkn-give-criptocurrency-meta-return">
                <strong><?php echo esc_html($args['return_id_label']) ?></strong>
                <?php echo esc_html($args['return_id']) ?>
            </p>
            <p id="lkn-give-criptocurrency-meta-cryptvalue">
                <strong><?php echo esc_html($args['crypto_value_label']) ?></strong>
                <?php echo esc_html($args['crypto_value']) ?>
            </p>
            <p id="lkn-give-criptocurrency-meta-wallet">
                <strong><?php echo esc_html($args['wallet_label']) ?></strong>
                <?php echo esc_html($args['wallet']) ?>
            </p><br>
            <p id="lkn-give-criptocurrency-crypto">
                <strong><?php echo esc_html($args['cripto_label']) ?></strong>
                <?php echo esc_html($args['crypto_type']) ?>
            </p><br>
        </div>

        <button
            type="button"
            id="lkn-give-criptocurrency-consult-transaction"
            class="button"
        ><?php echo esc_html($args['button_label']) ?></button><br><br>
    </div>
</div>