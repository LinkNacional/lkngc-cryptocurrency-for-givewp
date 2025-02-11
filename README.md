# Documentação em Português: Pagamento por Criptomoedas para GiveWP

O [Pagamento por Criptomoedas para GiveWP](https://www.linknacional.com.br/wordpress/givewp/criptomoedas/) é um plugin que estende as funcionalidades presentes no plugin GiveWP, adicionando o módulo de pagamento por criptomoedas, que inclui pagamentos via Bitcoin e Ethereum.

## Dependencias

O plugin [Pagamento por Criptomoedas para GiveWP](https://www.linknacional.com.br/wordpress/givewp/criptomoedas/) é dependente da ativação do [plugin GiveWP](https://wordpress.org/plugins/give/), por favor, tenha certeza de que ele esteja instalado e apropriadamente configurado antes de iniciar a ativação do Pagamento por Criptomoedas para GiveWP.

## Instalação

1. Na sidebar do Wordpress, procure pela opção "Plugins" e selecione-a;
2. Aperte o botão "Adicionar novo" ao lado do título "Plugins" no topo da página;
3. Aperte o botão "Enviar Plugin" ao lado do título "Instalar Plugins" no topo da página. Irá aparecer novas opções no centro da tela, selecione "Escolher arquivo", busque pelo arquivo do plugin (lkngc-cryptocurrency-for-givewp.zip) e o envie;
4. Aperte o botão "Instalar agora", em seguida ative o plugin instalado;

Ao terminar esses passos, o plugin estará ativado e pronto para ser configurado.

## Configuração

1. Agora, na sidebar do Wordpress, vá até o menu de configurações do GiveWP;
2. Selecione a opção "Gateways de Pagamento" e procure por "Criptomoedas";
3. Insira todas as credenciais necessárias dos gateways de pagamento;
4. Clique em "Salvar alterações";
5. Ainda em "Gateways de Pagamento" procure pela aba "Gateways";
6. Na tabela "Meios de pagamento habilitados" logo abaixo, procure pelos gateways do módulo instalado, nesse caso "Criptomoeda Bitcoin" e "Criptomoeda Ethereum", e os ative marcando a caixa de diálogo "Ativado" ao lado;
7. Ainda na tabela, clique na aba lateral "Visual Form Builder", e também ative os gateways para o novo modelo de formulário de doação;
8. Caso queira, mude a ordem dos gateways, e selecione um gateway padrão;
9. Clique em "Salvar alterações";

Agora o plugin estará ativado e funcionando.

## Modo de uso

1. Acesse um formulário do Give WP;
2. Selecione uma das criptomoedas que você ativou anteriormente;
3. O formulário da criptomoeda irá aparecer e será automaticamente atualizado em caso de mudança no valor da doação;
4. Realize um pagamento;
5. Clique em "Confirmar pagamento", ou no botão equivalente;

## Notas de desenvolvimento (Apenas em português)

### Testar confirmação de pagamento para Bitcoin e Ethereum:

Esse plugin utiliza a API do BlockCypher para analisar as transferências de Bitcoin e Ethereum.

Para conseguir testar o plugin, é necessário:
- Acessar a blockchain [para Bitcoin](https://www.blockchain.com/pt/explorer/assets/btc) ou [para Ethereum](https://www.blockchain.com/explorer/assets/eth);
- Pegar qualquer uma das transações na região onde aparecem as 'Transações mais recentes' da criptomoeda que deseja testar;
- Copiar a Wallet do destinatário (na coluna 'Para') e salvar no campo 'Carteira' do gateway que será testado;
- Copiar o valor de transferência do remetente (na coluna 'De') e usar como valor de doação no formulário;
- A doação será reconhecida como paga.

### Testar botão de confirmação de pagamento para Bitcoin e Ethereum:

O botão de confirmação de pagamento encontrado nos 'Detalhes da doação' é usado para verificar pagamentos que não foram realizados no momento da doação. Portanto, ele também possui uma margem de busca de transações na API, bem maior.

De forma objetiva, na verificação em tempo de doação, a API verifica as cinco ultimas transações da determinada Carteira, já no caso do botão de verificação, ele verifica as ultimas cinquenta transações.

Para conseguir testar é necessário:
- Seguir os mesmos passos do teste de confirmação de pagamento acima, mas pegando uma transação que não esteja entre as cinco ultimas da determinada Wallet;
- Com isso a transação não será reconhecida como paga, e poderá ser testada pelo botão;
- Outra forma é comentar a função `transaction_verification()` em `give-cryptocurrency/Includes/CryptoApi.php/`, mantendo apenas a função `btn_transaction_verification()`, presente no mesmo arquivo;
- Ao apertar o botão, irá exibir um Alert com a mensagem de Pagamento completo, e os dados da doação serão atualizados.

### Testar cotação entre moedas:

Acesse a [Coinbase](https://www.coinbase.com/converter/btc/brl).


<br>
<!-- DIVISORIA PARA DOCUMENTAÇÃO EM INGLÊS -->

# Documentation in English: Cryptocurrency Payment for GiveWP

The [Cryptocurrency Payment for GiveWP](https://www.linknacional.com.br/wordpress/givewp/criptomoedas/) is a plugin that extends the functionalities present in the GiveWP plugin, adding the cryptocurrency payment module, which includes payment via Bitcoin and Ethereum.

## Dependencies

The [Cryptocurrency Payment for GiveWP](https://www.linknacional.com.br/wordpress/givewp/criptomoedas/) plugin, is dependent of the [plugin GiveWP](https://wordpress.org/plugins/give/) activation, please make sure it is installed and properly configured before beginning the activation of Cryptocurrency Payment for GiveWP.

## Installation

1. In the Wordpress sidebar, look for the "Plugins" option and select it;
2. Press the "Add New Plugin" button next to the "Plugins" heading at the top of page;
3. Press the "Upload Plugin" button next to the "Add Plugins" heading at the top of page. New options will appear in the center of the screen, select "Submit plugin", search for the plugin file (lkngc-cryptocurrency-for-givewp.zip) and send it;
4. Press the "Install Now" button and then activate the installed plugin;

When finish these steps, the plugin will be activated and ready to be configured.

## Configuration

1. Now, in the Wordpress sidebar, go to the GiveWP settings menu;
2. Select the "Payment Gateways" option, and search for "Cryptocurrencies";
3. Enter all required payment gateways credentials;
4. Click on "Save changes";
5. Still in "Payment Gateways" look for the "Gateways" tab;
6. In the "Enabled Gateways" table below, look for the gateways of the installed module, in this case "Bitcoin Cryptocurrency" and "Ethereum Cryptocurrency", and activate them by checking the "Enabled" dialog box on the side;
7. Still in the table, click on the "Visual Form Builder" side tab, and also activate the gateways for the new donation form template;
8. If desired, rearrange the order of gateways, and select a default gateway;
9. Click on "Save changes";

Now the plugin will be activated and working.

## How to use

1. Access an GiveWP form;
2. Select one of the cryptocurrencies you previously activated;
3. The cryptocurrency form will appear and will be automatically updated if the donation amount changes;
4. Make a payment;
5. Click on "Confirm payment", or the equivalent button;