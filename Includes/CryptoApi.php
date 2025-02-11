<?php

namespace Lkngc\CryptocurrencyForGivewp\Includes;

use Throwable;
use WP_REST_Request;
use Give\Donations\Models\Donation; // Add this line
use Give\Donations\ValueObjects\DonationStatus;

final class CryptoApi {
    /**
     * @since 3.0.0
     *
     * @param string $wallet
     * @param string $cryptoType
     * @param int    $transactionsQtd
     *
     * @return
     */
    public static function blockcypher_wallet_query(string $wallet, string $cryptoType, int $transactionsQtd) {
        $url = "https://api.blockcypher.com/v1/{$cryptoType}/main/addrs/{$wallet}/full?limit={$transactionsQtd}";

        $headers = array(
            'Content-Type: application/json',
        );

        // Make the request args.
        $args = array(
            'headers' => $headers,
            'timeout' => '10',
            'redirection' => '5',
            'httpversion' => '1.1'
        );

        // Make the request.
        $response = wp_remote_get($url, $args);

        return json_decode(wp_remote_retrieve_body($response), true);
    }

    /**
     * @since 3.0.0
     *
     * @param int    $donationId
     * @param string $gatewayId
     * @param string $totalCryptoDec
     * @param string $wallet
     * @param string $cryptoType
     *
     * @return
     */
    public static function transaction_verification(int $donationId, string $gatewayId, string $totalCryptoDec, string $wallet, string $cryptoType) {
        $configs = LkngcCryptocurrencyForGivewpHelper::get_configs();
        $logEnabled = $configs['debug'];

        $CryptoDecimals = ('btc' === $cryptoType) ? 8 : 18;

        $totalCrypto = (int) number_format($totalCryptoDec, $CryptoDecimals, '', '');

        $request = CryptoApi::blockcypher_wallet_query($wallet, $cryptoType, 5);

        $transactionsQntd = $request['n_tx'];

        // Limit because the request is definided to search the last five transactions.
        $transactionsAmount = ($transactionsQntd >= 5) ? 5 : $transactionsQntd;

        if (0 === $transactionsAmount || null === $transactionsAmount) {
            if ('enabled' === $logEnabled) {
                LkngcCryptocurrencyForGivewpHelper::log($donationId, array(__('[Query result]: Unrecognized transaction on blockchain', 'lkngc-cryptocurrency-for-givewp')));
            }

            // Update metadata pending.
            give_update_payment_meta($donationId, 'lkn_give_cryptocurrency_response', wp_json_encode(array('gatewayId' => $gatewayId, 'status' => __('Pending payment', 'lkngc-cryptocurrency-for-givewp'), 'transactionId' => '0', 'totalCrypto' => $totalCryptoDec, 'wallet' => $wallet, 'cripto' => $cryptoType)));

            return give_send_to_success_page();
        } else {
            $errorMarginEnabled = $configs['enable_error_margin'];

            // Initialize error margin.
            $errorMargin = 0;
            if ('enabled' === $errorMarginEnabled) {
                $errorMargin = $configs['error_margin'];
                $errorMargin = $errorMargin / 100; // Convert to percentual.
            }

            // Filter the transactions.
            for ($c = 0; $c < $transactionsAmount; ++$c) {
                // Catch the transaction value + fee value.
                $totalTransaction = $request['txs'][$c]['total'] + $request['txs'][$c]['fees'];

                // Transaction value added to the margin of error.
                $totTransMarginTop = $totalTransaction * (1 + $errorMargin);

                // Transaction value subtracted to the margin of error.
                $totTransMarginBottom = $totalTransaction * (1 - $errorMargin);

                if ('eth' === $cryptoType) {
                    $totTransMarginTop = number_format($totTransMarginTop, 0, '', '');

                    $totTransMarginBottom = number_format($totTransMarginBottom, 0, '', '');
                }

                $arrOutputs = $request['txs'][$c]['outputs'];

                // Filter the transaction wallets.
                for ($i = 0; $i < count($arrOutputs); ++$i) {
                    // Catch the destinatary wallet.
                    $txsWallet = $arrOutputs[$i]['addresses'][0];

                    // Compare the wallets, if equal, verify the values.
                    if ($wallet === $txsWallet) {
                        // Verify the value, if ok, finalize the donation and show receipt of completed.
                        if ($totalCrypto >= $totTransMarginBottom && $totalCrypto <= $totTransMarginTop) {
                            // Register log with informations.
                            if ('enabled' === $logEnabled) {
                                LkngcCryptocurrencyForGivewpHelper::log($donationId, array(
                                    '[Total plugin Crypto]' => var_export($totalCrypto, true),
                                    '[Total transaction Crypto]' => var_export($totalTransaction, true),
                                    '[Total transaction Error Margin]' => array('Min' => $totTransMarginBottom, 'Max' => $totTransMarginTop),
                                    '[Carteira Crypto]' => var_export($wallet, true),
                                    '[Carteira Crypto txs]' => var_export($txsWallet, true)
                                ));
                            }

                            // Update metadata completed.
                            give_update_payment_meta($donationId, 'lkn_give_cryptocurrency_response', wp_json_encode(array('gatewayId' => $gatewayId, 'status' => __('Completed', 'lkngc-cryptocurrency-for-givewp'), 'transactionId' => $request['txs'][$c]['hash'], 'totalCrypto' => $totalCryptoDec, 'wallet' => $wallet, 'cripto' => $cryptoType)));

                            // Update Give donation status.
                            give_update_payment_status($donationId, 'complete');

                            return give_send_to_success_page();
                        }
                    }
                }
            }

            // Register log with informations.
            if ('enabled' === $logEnabled) {
                LkngcCryptocurrencyForGivewpHelper::log($donationId, array(
                    '[Total plugin Crypto]' => var_export($totalCrypto, true),
                    '[Carteira Crypto]' => var_export($wallet, true)
                ));
            }

            // Not registered, update metadata pending.
            give_update_payment_meta($donationId, 'lkn_give_cryptocurrency_response', wp_json_encode(array('gatewayId' => $gatewayId, 'status' => __('Pending payment', 'lkngc-cryptocurrency-for-givewp'), 'transactionId' => '0', 'totalCrypto' => $totalCryptoDec, 'wallet' => $wallet, 'cripto' => $cryptoType)));

            return give_send_to_success_page();
        }
    }

    /**
     * @since 3.0.0
     *
     * @param WP_REST_Request $request
     * @return array    json with data for .js POST request.
     */
    public static function btn_transaction_verification(WP_REST_Request $request) {
        try {
            $configs = LkngcCryptocurrencyForGivewpHelper::get_configs();
            $logEnabled = $configs['debug'];

            $params = $request->get_params();

            $verify_token = wp_hash(gmdate('dmYH') . 'notification');

            $auth_token = sanitize_text_field($params['token']);

            $donationId = sanitize_text_field($params['donation_id']);
            $totalCryptoDec = sanitize_text_field($params['crypt_value']);
            $wallet = sanitize_text_field($params['wallet_regist']);
            $cryptoType = sanitize_text_field($params['crypto_type']);

            if ($verify_token === $auth_token) {
                if (isset($donationId) && isset($totalCryptoDec) && isset($wallet)) {
                    $meta = give_get_payment_meta($donationId, '_give_payment_gateway');

                    // Get the gateway identifier: ethereum or bitcoin.
                    $gatewayId = $meta;
                    $gatewayId = explode('-', $gatewayId);
                    $gatewayId = end($gatewayId);

                    $CryptoDecimals = ('btc' === $cryptoType) ? 8 : 18;

                    $cryptoValueFormatted = str_replace(',', '.', $totalCryptoDec);
                    $totalCryptoFloat = (float) $cryptoValueFormatted;
                    $totalCrypto = (int) number_format($totalCryptoFloat, $CryptoDecimals, '', '');

                    $request = CryptoApi::blockcypher_wallet_query($wallet, $cryptoType, 50);

                    $transactionsQntd = $request['n_tx'];

                    // Limit because the request is definided to search the last fifty transactions.
                    $transactionsAmount = ($transactionsQntd >= 50) ? 50 : $transactionsQntd;

                    if (empty($transactionsAmount)) {
                        // if ('enabled' === $logEnabled) {
                        //     LkngcCryptocurrencyForGivewpHelper::log($donationId, array(__('[Query result]: Unrecognized transaction on blockchain', 'lkngc-cryptocurrency-for-givewp')));
                        // }

                        // Update metadata pending.

                        // $donation = Donation::find($donationId);
                        // $donation->status = DonationStatus::COMPLETE();
                        // $donation->save();

                        // give_update_payment_meta($donationId, 'lkngc_cryptocurrency_for_givewp_response', wp_json_encode(array('gatewayId' => $gatewayId, 'status' => __('Pending payment', 'lkngc-cryptocurrency-for-givewp'), 'transactionId' => '0', 'totalCrypto' => $totalCryptoDec, 'wallet' => $wallet, 'crypto' => $cryptoType)));

                        return array('return_response' => __('[Query result]: Unrecognized transaction on blockchain', 'lkngc-cryptocurrency-for-givewp'));
                    } else {
                        $errorMarginEnabled = $configs['enable_error_margin'];

                        // Initialize error margin.
                        $errorMargin = 0;
                        if ('enabled' === $errorMarginEnabled) {
                            $errorMargin = $configs['error_margin'];
                            $errorMargin = $errorMargin / 100; // Convert to percentual.
                        }

                        // Filter the transactions.
                        for ($c = 0; $c < $transactionsAmount; ++$c) {
                            // Catch the transaction value + fee value.
                            $totalTransaction = $request['txs'][$c]['total'] + $request['txs'][$c]['fees'];

                            // Transaction value added to the margin of error.
                            $totTransMarginTop = $totalTransaction * (1 + $errorMargin);

                            // Transaction value subtracted to the margin of error.
                            $totTransMarginBottom = $totalTransaction * (1 - $errorMargin);

                            if ('eth' === $cryptoType) {
                                $totTransMarginTop = number_format($totTransMarginTop, 0, '', '');

                                $totTransMarginBottom = number_format($totTransMarginBottom, 0, '', '');
                            }

                            $arrOutputs = $request['txs'][$c]['outputs'];

                            // Filter the transaction wallets.
                            for ($i = 0; $i < count($arrOutputs); ++$i) {
                                // Catch the destinatary wallet.
                                $txsWallet = $arrOutputs[$i]['addresses'][0];

                                // Compare the wallets, if equal, verify the values.
                                if ($wallet === $txsWallet) {
                                    // Verify the value, if ok, finalize the donation and show receipt of completed.
                                    if ($totalCrypto >= $totTransMarginBottom && $totalCrypto <= $totTransMarginTop) {
                                        // Register log with informations.
                                        // if ('enabled' === $logEnabled) {
                                        //     LkngcCryptocurrencyForGivewpHelper::log($donationId, array(
                                        //         '[Total plugin Crypto]' => var_export($totalCrypto, true),
                                        //         '[Total transaction Crypto]' => var_export($totalTransaction, true),
                                        //         '[Total transaction Error Margin]' => array('Min' => $totTransMarginBottom, 'Max' => $totTransMarginTop),
                                        //         '[Carteira Crypto]' => var_export($wallet, true),
                                        //         '[Carteira Crypto txs]' => var_export($txsWallet, true)
                                        //     ));
                                        // }

                                        // Update metadata completed.
                                        $donation = Donation::find($donationId);
                                        $donation->status = DonationStatus::COMPLETE();
                                        $donation->save();
                                        return array('return_response' => __('[Query result]: Payment confirmed', 'lkngc-cryptocurrency-for-givewp'));
                                    }
                                }
                            }
                        }

                        return array('return_response' => __('[Query result]: Unrecognized transaction on blockchain', 'lkngc-cryptocurrency-for-givewp'));
                    }
                }
                exit;
            } else {
                return array('return_response' => __('Unauthenticated session', 'lkngc-cryptocurrency-for-givewp'));
            }
        } catch (Throwable $e) {
            if ('enabled' === $logEnabled) {
                LkngcCryptocurrencyForGivewpHelper::log($donationId, array('donationID' => $donationId, 'error' => var_export($e, true)));
            }
            return $e;
        }
    }
}