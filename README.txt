=== Cryptocurrency Payment for GiveWP ===

Contributors: linknacional  
Donate link: https://www.linknacional.com.br/wordpress/plugins/  
Tags: givewp, donation, cryptocurrency, bitcoin, ethereum  
Requires at least: 5.7  
Tested up to: 7.1  
Stable tag: 4.0.4  
Requires PHP: 7.4  
License: GPL-3.0+  
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Accept Bitcoin and Ethereum donations with QR Code and automatic blockchain confirmation — no third-party checkout.

== Turn Your GiveWP Donations into Cryptocurrency ==

[WordPress](https://www.linknacional.com.br/wordpress/) integration [Cryptocurrency Payment for GiveWP](https://www.linknacional.com.br/wordpress/givewp/criptomoedas/) adds Bitcoin and Ethereum as native payment gateways to [GiveWP](https://www.linknacional.com.br/wordpress/givewp/). The donor picks the cryptocurrency, gets a QR Code with the exact amount, pays from any wallet and the donation is confirmed by reading the blockchain — the money goes straight to your wallet, with no intermediary and no chargeback.

The plugin keeps the donation flow inside your [GiveWP](https://www.linknacional.com.br/wordpress/givewp/) forms (classic and legacy templates), converts the donation amount using the current exchange rate and checks every received transaction against the expected amount and destination wallet.

== Why Choose Cryptocurrency Payment for GiveWP? ==

- **No intermediary:** donations go directly to the Bitcoin or Ethereum wallet you configure.
- **Native to [GiveWP](https://www.linknacional.com.br/wordpress/givewp/):** two new gateways appear in [GiveWP](https://www.linknacional.com.br/wordpress/givewp/) → Settings → Payment Gateways, ready to be enabled per form.
- **Automatic confirmation:** the plugin queries the blockchain and completes the donation when a matching transaction arrives.
- **Exchange rate on the fly:** the donation amount is converted to the cryptocurrency at the moment of payment.
- **Error margin:** tolerate small differences between the amount sent and the amount expected (network and exchange variations).
- **Transparent for the donor:** QR Code, copy-to-clipboard wallet address and a countdown timer on the payment screen.
- **Works with [WordPress](https://www.linknacional.com.br/wordpress/) and [GiveWP](https://www.linknacional.com.br/wordpress/givewp/) blocks:** the gateway ships its own scripts for the [GiveWP](https://www.linknacional.com.br/wordpress/givewp/) 3.x (Visual Form Builder) donation form.

== Features at a Glance ==

- Bitcoin and Ethereum gateways, enabled separately.
- QR Code generated on the front end (no external image service).
- Amount converted to BTC/ETH with 8 and 18 decimals respectively.
- Transaction verification against destination wallet, amount and fees.
- Configurable error margin (percentage) for the amount comparison.
- Donation details block in the [GiveWP](https://www.linknacional.com.br/wordpress/givewp/) donation screen, with a "Consult Transaction" button to re-check the blockchain.
- Debug log integrated with the [GiveWP](https://www.linknacional.com.br/wordpress/givewp/) logs.
- Multi-currency: the exchange rate follows the currency configured in [GiveWP](https://www.linknacional.com.br/wordpress/givewp/).

** Recommended Plugins **
* [Rede Itaú for WooCommerce](https://wordpress.org/plugins/integration-rede-for-woocommerce/) - Accept PIX, credit and debit cards in your [WooCommerce](https://www.linknacional.com.br/wordpress/woocommerce/) store.
* [Shipping Calculator for Brazil](https://wordpress.org/plugins/woo-better-shipping-calculator-for-brazil/) - Provide accurate freight calculation for Brazilian addresses directly in your [WooCommerce](https://www.linknacional.com.br/wordpress/woocommerce/) checkout.
* [Link Invoice Payment for WooCommerce](https://wordpress.org/plugins/invoice-payment-for-woocommerce/) - Integrate custom payment methods and offer invoice-based payments in your [WooCommerce](https://www.linknacional.com.br/wordpress/woocommerce/) store.

== Installation ==

### 1. Using the WordPress Admin Dashboard (Recommended)
1. Navigate to Plugins → Add New.
2. Click Upload Plugin and select the `lkngc-cryptocurrency-for-givewp.zip` file.
3. Click Install Now and then Activate.
4. Go to [GiveWP](https://www.linknacional.com.br/wordpress/givewp/) plugin → Settings → Payment Gateways to configure the plugin.

### 2. Manual Installation via FTP
1. Extract the plugin ZIP file.
2. Upload the extracted folder to `wp-content/plugins/`.
3. Activate the plugin in the [WordPress](https://www.linknacional.com.br/wordpress/) admin dashboard under Plugins.

### 3. WP-CLI Installation
1. Upload the plugin folder to `wp-content/plugins/`.
2. Run:
    ```bash
    wp plugin activate lkngc-cryptocurrency-for-givewp
    ```

== Configuration ==

1. In the [WordPress](https://www.linknacional.com.br/wordpress/) sidebar, go to the [GiveWP](https://www.linknacional.com.br/wordpress/givewp/) settings menu.
2. Select Payment Gateways and open the **CriptoCurrencies** section.
3. Enter the **Bitcoin wallet** and the **Ethereum wallet** that will receive the donations.
4. Optionally enable the **Error margin** and set a percentage (for example, `2` for 2%) to accept small differences between the expected and the received amount.
5. Optionally enable the **Debug Mode** (logs in [GiveWP](https://www.linknacional.com.br/wordpress/givewp/) → Tools → Logs) and the **Advanced Debug Mode** (console messages on the front end).
6. Click on Save changes.
7. Still in Payment Gateways, open the **Gateways** tab.
8. In the **Enabled Gateways** table, enable **Crypto Bitcoin** and/or **Crypto Ethereum**.
9. In the same table, click on the **Visual Form Builder** tab and enable the gateways for the new donation form template as well.
10. Optionally reorder the gateways and set one of them as default, then click on Save changes.

== External services ==

This plugin connects to an API to obtain the cryptocurrency blockchain transactions, it's needed for transaction validation and confirmation after payment.
It sends the user cryptocurrency public wallet address every time a donation is being processed to check for all transactions received. This service is provided by **BlockCypher**: [Terms of service](https://www.blockcypher.com/terms-of-service.html), [privacy policy](https://www.blockcypher.com/privacy-policy.html).

This plugin connects to an API to obtain the cryptocurrency price for conversion, it's needed for transaction amount definition.
It sends the donation currency and the cryptocurrency used in the donation. This service is provided by **CoinGecko**: [Terms of service](https://www.coingecko.com/en/terms), [privacy policy](https://www.coingecko.com/en/privacy).

== External Libraries ==

This plugin bundles the following third-party library:

- **QR Code JS** by davidshimjs: generates the payment QR Code on the front end, so the wallet address and the amount never leave the donor's browser to a third-party image service. Source: [davidshimjs/qrcodejs](https://github.com/davidshimjs/qrcodejs) (MIT license).

== Frequently Asked Questions ==

Q: What is the license of the plugin?  
A: This plugin is released under the GPLv3 license.

Q: What do I need to use this plugin?  
A: You need [GiveWP](https://www.linknacional.com.br/wordpress/givewp/) version 2.3.0 or later installed and active, and a Bitcoin and/or Ethereum wallet to receive the donations.

Q: Which cryptocurrencies are supported?  
A: Bitcoin (BTC) and Ethereum (ETH), each one as a separate gateway that can be enabled per form.

Q: When is a donation marked as complete?  
A: When the blockchain returns a transaction that pays the configured wallet with the expected amount (converted to BTC/ETH and, optionally, within the configured error margin).

Q: What is the error margin for?  
A: Because of exchange rate and network variations, the amount that arrives can differ by a few cents. The margin defines how much difference is still accepted before the plugin considers the transaction as not matching.

Q: Do I need an account on a payment gateway?  
A: No. There is no intermediary: the donation goes directly to your wallet. The plugin only reads public blockchain data and the cryptocurrency price.

Q: The donation stayed pending — what now?  
A: Open the donation in [GiveWP](https://www.linknacional.com.br/wordpress/givewp/) and use the **Consult Transaction** button in the donation details block to verify the blockchain again, after the transaction is confirmed by the network.

Q: Does it work with the new [GiveWP](https://www.linknacional.com.br/wordpress/givewp/) donation form (Visual Form Builder)?  
A: Yes. Both gateways are registered for the [GiveWP](https://www.linknacional.com.br/wordpress/givewp/) 3.x form, and the QR Code and the amount are rendered by the plugin scripts.

Q: Does it work with the classic (legacy) form template?  
A: Yes. In forms using the classic template the gateway renders its own markup with the QR Code, the wallet address and the confirmation buttons.

Q: Is the plugin compatible with the latest [WordPress](https://www.linknacional.com.br/wordpress/) version?  
A: Yes — tested up to [WordPress](https://www.linknacional.com.br/wordpress/) 7.1.

== Changelog ==

= 4.0.4 - 18/11/2026 =
* Fix: the Ethereum gateway queried the Bitcoin chain, so ETH donations were never confirmed automatically.
* Fix: the version constant check used a name that does not exist, which served every asset with the fixed version 1.0.0 (broken cache busting after updates).
* Fix: the admin class received the version in the wrong constructor argument.
* Fix: the donation details block rendered empty because load_template() does not expose its third argument to the template.
* Fix: the form template lookup had the WordPress table prefix hardcoded and crashed on sites that do not use the "wp_" prefix.
* Fix: the REST verification route killed the JSON response (exit) when the request was missing parameters, and crashed when the donation was not found or the blockchain API was unavailable.
* Fix: uninstall now removes only the plugin options and no longer breaks if GiveWP was already deleted.
* Tweak: code standard cleanup and removal of the deprecated accesses flagged by the Plugin Check.
* Tweak: readme rewritten, with the external services, libraries and configuration documented.

= 4.0.3 - 12/03/2026 =
* Addition of new icons and banners.

= 4.0.2 - 11/02/2025 =
* WordPress guidelines update
* Fixed script errors;
* Updated slug to lkngc-cryptocurrency-for-givewp.

= 4.0.1 - 03/02/2025 =
* WordPress guidelines update

= 4.0.0 - 16/09/2024 =

* Refatoração completa do plugin com uso de boilerplate.
* Implementação de boilerplate para padronização e melhores práticas no desenvolvimento.
* Compatibilidade com o Give 3.0.
* Correção na geração do QrCode.
* Otimização e limpeza geral do código para melhor desempenho e manutenção.
* Implementação de caching de consultas para otimizar o tempo e reduzir o número de requisições à API.

= 3.0.0 - 10/10/23 =

* Complete refactoring for best practices.
* Integration for new GiveWP v3.0.0 form.
* Added configuration for margin of error, used in payments verification.
* Fixed bug that causing multiple API requests.

= 2.1.2 =

* Fixed missing translation.
* Fixed decimal handling.
* Fixed decimal handling with PagHiper.

= 2.1.1 =

* Fixed bug of unimported files.
* Improved plugin activation function.

= 2.1.0 =

* Implemented translation for English and Portuguese.
* Corrected multi-currency compatibility.
* Added compatibility with native Bitcoin donations from GiveWP.

= 2.0.0 =

* Refactored donation metadata.
* Added attribute sanitization.
* Code cleaning and optimization.
* Changed endpoint for automatic updates.

= 1.1.1 =

* Fixed license verification script bug.

= 1.1.0 =

* Added option to activate and register Waves wallet.
* Minor display adjustments.
* Updated donation metadata.

= 1.0.0 =

* Plugin release.

== Upgrade Notice ==

= 4.0.4 =

* Fix: Ethereum donations were verified against the Bitcoin chain and never confirmed. Recommended update.

= 4.0.3 =

* New icons and banners.

= 1.0.0 =

* Plugin release.
