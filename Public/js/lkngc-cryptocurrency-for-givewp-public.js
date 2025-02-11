(function ($) {
  'use strict';

  function submitForm() {
    const formGive = document.querySelector('form[id^="give-form-"]'); //Todo: Paramos de usar o prefixo de ID do Formulário
    if (formGive && formGive.checkValidity()) {
      formGive.submit();
    } else {
      formGive.reportValidity();
    }
  }

  function lkngcGerarPagamento(cryptoType) {
    const submitWrapper = document.getElementById('btnSubmitWrapper');
    const btcWrapper = document.getElementById('lkn_cryptocurrency_wrapper');
    const btnDoar = document.getElementById('btnDonateCrypto');
    const advanceBtn = document.querySelectorAll('.advance-btn');

    if (advanceBtn.length >= 1) {
      const formGive = document.querySelector('form[id^="give-form-"]'); //Todo: Paramos de usar o prefixo de ID do Formulário

      if (formGive && formGive.checkValidity()) {
        submitWrapper.style.display = ''; // Mostra o wrapper do botão de submit
        inicializarTemporizador(5, cryptoType); // Inicia o temporizador

        const btnCopiar = document.querySelector('.copy_adress');
        if (btnCopiar) btnCopiar.style.display = 'none';
        if (btcWrapper) btcWrapper.style.display = ''; // Exibe o wrapper da criptomoeda
        if (btnDoar) btnDoar.style.display = 'none'; // Esconde o botão de doação
      } else {
        if (formGive) {
          formGive.reportValidity();
        }
      }
    } else { //LEGADO!
      const formGive = document.querySelector('form[id^="give-form-"]'); //Todo: Paramos de usar o prefixo de ID do Formulário

      if (formGive.checkValidity()) {
        submitWrapper.style.display = '';

        inicializarTemporizador(5, cryptoType);

        const btnSubmitForm = document.getElementById('btnSubmitForm');
        const donationLevels = document.getElementById('give-donation-level-button-wrap');
        const selectCurrency = document.getElementById('give-mc-select');
        const giveInput = document.getElementById('give-amount');
        const givePaymentList = document.getElementById('give-gateway-radio-list');

        // Esconde o botão e deixa a página de pagamento aparecer
        btcWrapper.style.display = '';
        btnDoar.style.display = 'none';

        // Adiciona e remove eventos dos elementos
        function addEventListeners() {
          if (donationLevels) {
            const arrDonationLevels = donationLevels.querySelectorAll('li button:not([value="custom"])');
            arrDonationLevels.forEach(btn => btn.addEventListener('click', listenerNiveisDoacao));
          }

          if (selectCurrency) {
            selectCurrency.addEventListener('change', listenerMultiMoeda);
          }

          if (givePaymentList) {
            const arrPaymentList = givePaymentList.querySelectorAll('li input:not([value="lkn_cryptocurrency_bitcoin"])');
            arrPaymentList.forEach(input => {
              input.parentElement.addEventListener('click', listenerSeletorGateway);
            });
          }

          if (giveInput) {
            giveInput.addEventListener('blur', listenerInput);
          }
        }

        function removeEventListeners() {
          if (selectCurrency) {
            selectCurrency.removeEventListener('change', listenerMultiMoeda);
          }

          if (giveInput) {
            giveInput.removeEventListener('blur', listenerInput);
          }

          if (givePaymentList) {
            const arrPaymentList = givePaymentList.querySelectorAll('li input:not([value="lkn_cryptocurrency_bitcoin"])');
            arrPaymentList.forEach(input => {
              input.parentElement.removeEventListener('click', listenerSeletorGateway);
            });
          }

          if (donationLevels) {
            const arrDonationLevels = donationLevels.querySelectorAll('li button:not([value="custom"])');
            arrDonationLevels.forEach(btn => btn.removeEventListener('click', listenerNiveisDoacao));
          }
        }

        function listenerNiveisDoacao() {
          inicializarTemporizador(5, cryptoType);
        }

        function listenerMultiMoeda() {
          inicializarTemporizador(5, cryptoType);
        }

        function listenerInput() {
          inicializarTemporizador(5, cryptoType);
        }

        function listenerSeletorGateway() {
          clearInterval(intervalo);
          removeEventListeners();
        }

        addEventListeners();
      } else {
        formGive.reportValidity();
      }
    }
  }
  
  function copiarTexto() {
    const carteiraBitcoin = document.querySelector('.walletInput');
    if (!carteiraBitcoin) {
        console.error('Elemento .walletInput não encontrado.');
        return;
    }
    
    var texto = carteiraBitcoin.innerHTML;
    
    const textArea = document.createElement("textarea");
    textArea.style.position = 'fixed';
    textArea.value = texto;
    document.body.appendChild(textArea);
    textArea.select();
    
    try {
        document.execCommand('copy');
    } catch (err) {
        console.error('Erro ao copiar o texto:', err);
        window.prompt("Copiar para área de transferência: Ctrl+C e Enter", texto);
    }
    
    document.body.removeChild(textArea);
}

  /**=============== Atributos ===============*/
  const intervalo = 0;
  /**=============== FUNÇÕES ===============*/

  function gerarQrCode(cripto){

    var selectedWallet  = wallet.bitcoin;

    if (cripto != 'bitcoin'){
      cripto = 'ethereum';
      selectedWallet  = wallet.ethereum;
    }

    const inputCripto = document.getElementById('lkn_btc_value');

    const qrContainer = document.getElementById('qrcode');

    if (qrContainer) {
      qrContainer.innerHTML = '';
    }

    var qrText = cripto + ':' + selectedWallet  + '?amount=' + inputCripto.value;

    const inputGateway = document.getElementsByName('gatewayData[cryptoValue]');
    inputGateway[0].value = inputCripto.value;

    new QRCode(document.getElementById('qrcode'), {
      text: qrText,
      width: 250,
      height: 250
    });
  }

  function enviarFormulario() {
    const formGive = document.getElementById(`give-form-${idPrefix}`);
    if (formGive) {
      formGive.submit();
    } else {
      console.error('Formulário não encontrado.');
    }
  }

  function inicializarTemporizador(duracao, cripto) {
    const duracaoSegundos = 60 * duracao; // Converte para segundos
    const display = document.getElementById('timer');
    clearInterval(intervalo); // Limpa o temporizador anterior
    iniciarTemporizador(duracaoSegundos, display, cripto); // Inicia o temporizador
  }

  function atualizarCotacaoCripto(moeda, cripto) {
    const url = `https://api.coingecko.com/api/v3/simple/price?ids=${cripto}&vs_currencies=${moeda}`;

    fetch(url)
      .then(response => response.json())
      .then(response => {
        const totalGive = document.querySelector('input[name="give-amount"]').value;
        const cotacaoCripto = response[cripto][moeda.toLowerCase()];
        let totalCripto = totalGive
          .replace(give_global_vars.thousands_separator, '')
          .replace(give_global_vars.decimal_separator, '.');
        totalCripto = totalCripto / cotacaoCripto; // Conversão de moeda para criptomoeda
        totalCripto = Math.round(totalCripto * 1e8) / 1e8; // Arredonda para 8 casas decimais

        const labelCripto = document.getElementById('totalBtc');
        const inputCripto = document.getElementById('lkn_btc_value');

        if (labelCripto && inputCripto) {
          labelCripto.innerHTML = totalCripto.toString().replace('.', give_global_vars.decimal_separator);
          inputCripto.value = totalCripto;

          gerarQrCode(cripto);
        }
      })
      .catch(error => console.error('Erro ao atualizar a cotação da criptomoeda:', error));
  }

  function iniciarTemporizador(duracao, display, cripto) {
    const multiMoeda = document.getElementById('give-mc-select');
    let temporizador = duracao;
    const codigoMoeda = multiMoeda?.value ?? give_global_vars.currency;

    atualizarCotacaoCripto(codigoMoeda, cripto);

    const atualizarTemporizador = () => {
      const minutos = String(Math.floor(temporizador / 60)).padStart(2, '0');
      const segundos = String(temporizador % 60).padStart(2, '0');
      display.textContent = `${minutos}:${segundos}`;

      if (--temporizador < 0) {
        atualizarCotacaoCripto(codigoMoeda, cripto);
        temporizador = duracao;
      }
    };

    setInterval(atualizarTemporizador, 1000);
  }

  // Expondo funções para o escopo global
  window.submitForm = submitForm;
  window.lkngcGerarPagamento = lkngcGerarPagamento;
  window.copiarTexto = copiarTexto;
})();
