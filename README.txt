=== Cryptocurrency Payment for GiveWP ===
Contributors: linknacional
Donate link: https://www.linknacional.com.br/wordpress/plugins/
Tags: donation, givewp, crypto, bitcoin, ethereum
Requires at least: 5.7
Requires PHP: 7.4
Tested up to: 6.7
Stable tag: 4.0.2
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Payment via Cryptocurrencies: Bitcoin and Ethereum.

== Description ==

**Dependencies**

Cryptocurrency Payment for GiveWP plugin is dependent of the [plugin GiveWP](https://wordpress.org/plugins/give/) activation, please make sure it is installed and properly configured before beginning the activation of Cryptocurrency Payment for GiveWP.

JS Libraries used:
[QR Code JS by davidshimjs](https://github.com/davidshimjs/qrcodejs)

**User instructions**

- In the Wordpress sidebar, go to the GiveWP settings menu.
- Select the 'Payment Gateways' option, and search for 'Cryptocurrencies'.
- Enter all required payment gateways credentials.
- Click on 'Save changes'.
- Still in 'Payment Gateways' look for the 'Gateways' tab.
- In the 'Enabled Gateways' table below, look for the gateways of the installed module, in this case 'Bitcoin Cryptocurrency' and 'Ethereum Cryptocurrency', and activate them by checking the 'Enabled' dialog box on the side.
- Still in the table, click on the 'Visual Form Builder' side tab, and also activate the gateways for the new donation form template.
- If desired, rearrange the order of gateways, and select a default gateway.
- Click on 'Save changes'.

== External services ==

This plugin connects to an API to obtain the cryptocurrency blockchain transactions, it's needed for transaction validation and confirmation after payment.
It sends the user cryptocurrency public wallet address every time a donation is beign processed to check for all transactions received. This service is provided by "Blockcypher": [Terms of service](https://www.blockcypher.com/terms-of-service.html), [privacy policy](https://www.blockcypher.com/privacy-policy.html).

This plugin connects to an API to optain the cryptocurrency price for conversion, it's needed for transaction amount definition.
It sends the donation currency and the cryptocurrency used in the donation. This service is provided by "CoinGecko": [Terms of service](https://www.coingecko.com/en/terms), [privacy policy](https://www.coingecko.com/en/privacy).

== Installation ==

- Upload the files to the /urldoseusite.com/wp-content/plugins/give-cryptocurrency/ directory. If the give-cryptocurrency directory does not exist, you need to create it.
- After uploading, go to your WordPress admin area and select the 'Plugins' option.
- Look for the plugin named 'Cryptocurrency Payment for GiveWP.'
- Click on 'Activate.'

== Frequently Asked Questions ==

= What is the plugin license? =

* This plugin is released under a GPL license.

= What is needed to use this plugin? =

* GiveWP version 2.3.0 or latter installed and active.

== Screenshots ==

1. None

== Changelog ==
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

= 1.0.0 =

* Plugin release.
