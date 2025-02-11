<?php

namespace Lkngc\CryptocurrencyForGivewp\PublicView;

use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\PaymentPending;
use Give\Framework\PaymentGateways\Commands\PaymentRefunded;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give_DB_Form_Meta;
use Lkngc\CryptocurrencyForGivewp\Includes\LkngcCryptocurrencyForGivewpHelper;

/**
 * @inheritDoc
 */
final class LkngcCryptocurrencyForGivewpEthereum extends PaymentGateway {
    /**
     * @inheritDoc
     */
    public static function id(): string {
        return 'lkngc-cryptocurrencyforgivewp-ethereum';
    }

    /**
     * @inheritDoc
     */
    public function getId(): string {
        return self::id();
    }

    /**
     * @inheritDoc
     */
    public function getName(): string {
        return 'Crypto Ethereum';
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel(): string {
        return 'Crypto Ethereum';
    }

    /**
     * @inheritDoc
     */
    public function getLegacyFormFieldMarkup(int $formId, array $args): string {
        $formTb = new Give_DB_Form_Meta();
        $formTb->table_name = "wp_give_formmeta";
        $resultForm = $formTb->get_results_by(array('form_id' => $formId, 'meta_key' => '_give_form_template'));

        if ('legacy' != $resultForm[0]->meta_value) {
            $html = '
            <div class="donation-errors">
                <div class="give-notice give-notice-error" id="give_error_warning">
                    <p class="give_notice give_warning">
                    <strong>' . esc_html__('Notice:', 'give') . '</strong>
                    ' . esc_html__('CryptoCurrency is not enabled for the classic and multistep form!', 'give') . '</p>
                </div>
            </div>';
            return $html;
        }

        $wallets = LkngcCryptocurrencyForGivewpHelper::get_wallets();
        $walletEthereum = esc_attr($wallets['ethereum']);

        $i18n = '__'; //TODO: Trocar por mensagens de tradução

        $html = '
        <fieldset id="lkngc_give_cc_fields" class="give-do-validate">

            <input name="gatewayData[cryptoValue]" type="hidden" value="0" >

            <legend>Payment Information</legend>

            <div id="give_secure_site_wrapper">
                <span class="give-icon padlock"></span>
                <span>Secure Donation via SSL Encryption.</span>
            </div>

            <input id="lkn_btc_value" name="lkn_crypto_value" type="hidden" value="0">

            <div id="lkn_cryptocurrency_wrapper" class="lkn_crypto_hidden">
               <a href="ethereum:' . $walletEthereum . '" target="_blank">
                    <div id="qrcode"></div>
                </a>

                <div id="lkn_crypto_price_wrapper">
                    <div><span>Donation amount:</span></div>
                    <div><span id="totalBtc">0.00000000</span><span> ETH</span></div>
                </div>

                <div id="lkn_cryptocurrency_input_wrapper">
                    <button type="button" class="copy_adress popup" onclick="copiarTexto()">
                        <span>Click here to copy</span>
                        <span class="dashicons dashicons-admin-page"></span>
                        <span class="popuptext" id="myPopup">Copied!</span>
                    </button>
                    <div>
                        <span class="walletInput">' . $walletEthereum . '</span>
                    </div>
                </div>

                <div id="lkn_crypto_temp">Remaining time to make the payment: <span id="timer"></span></div>
            </div>

            <div id="btnBtcWrapper">
                <button type="button" id="btnDonateCrypto" class="give-btn lkn-btn-gateway" onclick="lkngcGerarPagamento(\'ethereum\')">Generate QR Code</button>
            </div>

            <div id="btnSubmitWrapper">
                <button type="button" id="btnSubmitForm" class="give-btn lkn-btn-gateway" onclick="submitForm()">Confirm payment</button>
            </div>
        </fieldset>
    ';

        return $html;
    }

    /**
     * @inheritDoc
     */
    public function createPayment(Donation $donation, $gatewayData): GatewayCommand {
        $configs = LkngcCryptocurrencyForGivewpHelper::get_configs();
        $logEnabled = $configs['debug'];
        $wallet = LkngcCryptocurrencyForGivewpHelper::get_wallets()['ethereum'];
        $totalCryptoDec = $gatewayData['cryptoValue'];

        $statusPendente = __('Pending payment', 'lkngc-cryptocurrency-for-givewp');
        $statusComplete = __('Completed payment', 'lkngc-cryptocurrency-for-givewp');

        try {
            // Verificar e Limpar Erros Anteriores
            give_clear_errors();
            $errors = give_get_errors();

            if ('enabled' === $logEnabled && $errors) {
                LkngcCryptocurrencyForGivewpHelper::log($donation->id, array(
                    'donation' => $donation->toArray(),
                    'gateway_data' => $gatewayData,
                    'errors' => var_export($errors, true)
                ));
            }

            if ($errors) {
                throw new PaymentGatewayException("Errors found");
            }

            // Consultar API Externa
            $cabecalho = array('Content-Type: application/json');
            $query = 'https://api.blockcypher.com/v1/btc/main/addrs/' . $wallet . '/full?limit=5';
            $response = $this->lkngc_cryptocurrency_for_givewp_connect_query($cabecalho, $query);

            $result = json_decode($response);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON response from API');
            }

            $qtdTransacoes = $result->n_tx;

            // 4. Verificar Transações
            if (0 === $qtdTransacoes) {
                // Transação não reconhecida
                $this->savePaymentMeta($donation->id, $statusPendente, '0', $totalCryptoDec);
                return new PaymentPending();
            }

            $qtdTransacoes = min($qtdTransacoes, 5); // Limita a 5 transações

            for ($c = 0; $c < $qtdTransacoes; ++$c) {
                $totalTransaction = $result->txs[$c]->total + $result->txs[$c]->fees;
                $arrOutputs = $result->txs[$c]->outputs;

                foreach ($arrOutputs as $output) {
                    $txsWallet = $output->addresses[0];
                    if ($wallet === $txsWallet && $totalCryptoDec == $totalTransaction) {
                        $this->savePaymentMeta($donation->id, $statusComplete, $result->txs[$c]->hash, $totalCryptoDec);
                        $donation->status = DonationStatus::COMPLETE();
                        $donation->save();
                        return new PaymentComplete();
                    }
                }
            }

            // Nenhuma transação corresponde, mantemos o status como pendente
            $this->savePaymentMeta($donation->id, $statusPendente, '0', $totalCryptoDec);
            $donation->status = DonationStatus::PENDING();
            $donation->save();
            return new PaymentPending();
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();

            $donation->status = DonationStatus::FAILED();
            $donation->save();

            DonationNote::create(array(
                'donationId' => $donation->id,
                'content' => esc_html('Falha na doação. Razão: ' . $errorMessage) // Translators: %s é um espaço reservado para a mensagem de erro
            ));

            throw new PaymentGatewayException(esc_html($errorMessage));
        }
    }

    private function savePaymentMeta($payment_id, $status, $transactionId, $totalCrypto): void {
        give_update_payment_meta($payment_id, 'lkngc_cryptocurrency_for_givewp_response', wp_json_encode(array(
            'wallet' => LkngcCryptocurrencyForGivewpHelper::get_wallets()['ethereum'],
            'status' => $status,
            'transactionId' => $transactionId,
            'totalCrypto' => $totalCrypto,
            'crypto' => 'eth'
        )));
    }

    public function lkngc_cryptocurrency_for_givewp_connect_query($headers, $query) {
        $configs = LkngcCryptocurrencyForGivewpHelper::get_configs();
        $url = $configs['urlQuery'] . $query;

        // Configura os argumentos para a requisição HTTP
        $args = array(
            'headers' => $headers,
            'timeout' => 45, // Tempo limite para a requisição em segundos
        );

        // Faz a requisição HTTP usando wp_remote_get
        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            // Log de erro, se necessário
            // lkn_give_cryptocurrency_reg_log(
            //     '[Query result]: ' . var_export($response->get_error_message(), true),
            //     $configs
            // );

            // Retorna false ou uma mensagem de erro apropriada
            return false;
        }

        // Obtém o corpo da resposta
        $body = wp_remote_retrieve_body($response);

        // Se necessário, registre a resposta
        // lkn_give_cryptocurrency_reg_log(
        //     '[Query result]: ' . var_export($body, true),
        //     $configs
        // );

        return $body;
    }

    /**
     * @inerhitDoc
     */
    public function refundDonation(Donation $donation): PaymentRefunded {
        // Step 1: refund the donation with your gateway.
        // Step 2: return a command to complete the refund.
        return new PaymentRefunded();
    }

    /**
     * // TO DO needs this function to appear in v3 forms
     * @since 3.0.0
     */
    public function enqueueScript(int $formId): void {
        $configs = LkngcCryptocurrencyForGivewpHelper::get_configs();
        $wallets = LkngcCryptocurrencyForGivewpHelper::get_wallets();
        $advDebug = $configs['advdebug'];

        wp_enqueue_script(
            'lkngc-ethereum-script-js',
            plugin_dir_url(__FILE__) . 'js/ethereum-script.js',
            array('jquery'),
            LKNGC_GIVE_CRYPTOCURRENCY_FOR_GIVEWP_VERSION,
            true
        );

        $decimal_separator = give_get_option('decimal_separator', );
        $thousand_separator = give_get_option('thousands_separator', );
        $currency = give_get_option('currency', );

        $walletEthereum = ! empty($wallets['ethereum']) && strlen($wallets['ethereum']) > 10 ? $wallets['ethereum'] : '';
        $MenssageErrorEthereum = __('Wallet Ethereum Invalid or Empty.', 'lkngc-cryptocurrency-for-givewp');

        wp_localize_script('lkngc-ethereum-script-js', 'lkngcGiveCryptoGlobalsETH', array(
            'decimal_separator' => $decimal_separator,
            'thousand_separator' => $thousand_separator,
            'currency' => $currency,
            'walletEthereum' => $walletEthereum,
            'advDebug' => $advDebug,
            'MenssageErrorEthereum' => $MenssageErrorEthereum,
        ));

        wp_enqueue_script(
            'lkngc-ethereum-give-cryptocurrency-qrcode-js',
            plugin_dir_url(__FILE__) . 'js/qrcode.min.js',
            array('jquery'),
            LKNGC_GIVE_CRYPTOCURRENCY_FOR_GIVEWP_VERSION,
            true
        );

        wp_enqueue_style(
            'lkngc-ethereum-give-cryptocurrency-public-css',
            plugin_dir_url(__FILE__) . 'css/lkngc-cryptocurrency-for-givewp-public.css',
            array(),
            LKNGC_GIVE_CRYPTOCURRENCY_FOR_GIVEWP_VERSION
        );
    }
}
