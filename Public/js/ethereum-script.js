let lkngcEthereumObserver = null
let lkngcEthereumObserverMethod = null
const lkngcCachedCryptoPriceEthereum = {}
const lkngcCacheDurationEthereum = 5 * 60 * 1000
let lkngcTimerIntervalIdEthereum = null
let lkngcActivatorEthereum = false

function lkngcSubmitFormEthereum () {
  const btnSubmitForm = document.querySelector('button[type="submit"]')
  if (btnSubmitForm) {
    btnSubmitForm.disabled = false
    btnSubmitForm.click()
  }
}

function lkngcIsValidEmail (email) {
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailPattern.test(email)
}

function lkngcNewPaymentEthereum (cryptoType) {
  if (!lkngcActivatorEthereum) { lkngcActivatorEthereum = true }

  const btnDoar = document.getElementById('btnDonateCrypto')
  const nomeInput = document.querySelector('input[name="firstName"]')
  const emailInput = document.querySelector('input[name="email"]')
  const errorMessageDiv = document.getElementById('lkngc-error-message-ethereum')

  let isValid = true
  let errorMessage = ''

  if (nomeInput && emailInput && errorMessageDiv) {
    if (!nomeInput.value.trim()) {
      isValid = false
      errorMessage = 'Nome não pode ser vazio'
    } else if (nomeInput.value.trim().length < 3) {
      isValid = false
      errorMessage = 'Nome deve ter no mínimo 3 caracteres'
    } else if (!emailInput.value.trim()) {
      isValid = false
      errorMessage = 'Email não pode ser vazio'
    } else if (!lkngcIsValidEmail(emailInput.value)) {
      isValid = false
      errorMessage = 'Email inválido'
    }

    if (!isValid) {
      errorMessageDiv.style.display = 'block'
      errorMessageDiv.textContent = errorMessage
    } else {
      errorMessageDiv.style.display = 'none'
      document.querySelector('button[type="submit"]').disabled = true
      btnDoar.style.display = 'none'
      const btnEthWrapper = document.getElementById('lkn_cryptocurrency_wrapper')
      const btnSubmitWrapper = document.getElementById('btnSubmitForm')
      btnEthWrapper.style.display = 'flex'
      btnSubmitWrapper.style.display = 'block'

      lkngcInitTempEthereum(5, cryptoType)
    }
  }
}

function lkngcCopyTextEthereum () {
  const carteiraEthereum = document.querySelector('.lkngcWalletEthereumInput')
  if (!carteiraEthereum) {
    if (lkngcGiveCryptoGlobalsETH.advDebug) {
      console.error('Elemento .lkngcWalletEthereumInput não encontrado.')
    }
    return
  }

  const texto = carteiraEthereum.innerHTML

  navigator.clipboard.writeText(texto).then(() => {
    const button = document.querySelector('.copy_adress')
    button.firstChild.textContent = 'Copied!'
  }).catch(err => {
    console.error('Erro ao copiar o texto:', err)
    window.prompt('Copiar para área de transferência: Ctrl+C e Enter', texto)
  })
}

/** =============== FUNÇÕES =============== */

function lkngcGenerateQrCodeEthereum (cripto) {
  const selectedWallet = lkngcGiveCryptoGlobalsETH.walletEthereum

  const inputCripto = document.getElementById('lkngc_eth_value')

  const qrContainer = document.getElementById('qrcode')

  const aDiv = document.getElementById('lkn_crypto_wallet_ethereum')

  if (qrContainer) {
    qrContainer.innerHTML = ''
  }

  if (aDiv) {
    aDiv.href = cripto + ':' + selectedWallet + '?amount=' + inputCripto.value
  } else {
    console.error("Elemento <a> com a classe 'lkn_crypto_wallet_ethereum' não encontrado.")
  }

  const qrText = cripto + ':' + selectedWallet + '?amount=' + inputCripto.value

  new QRCode(document.getElementById('qrcode'), {
    text: qrText,
    width: 250,
    height: 250
  })

  // Adicionando margens via CSS
  document.getElementById('qrcode').style.marginTop = '15px'
}

function lkngcInitTempEthereum (duracao, cripto) {
  const duracaoSegundos = 60 * duracao
  const display = document.getElementById('timer')
  if (lkngcTimerIntervalIdEthereum) {
    clearInterval(lkngcTimerIntervalIdEthereum)
  }
  lkngcInitTempEthereumAfter(duracaoSegundos, display, cripto) // Inicia o temporizador
}

function lkngcUpdateCryptoExRateEthereum (moeda, cripto) {
  const url = `https://api.coingecko.com/api/v3/simple/price?ids=${cripto}&vs_currencies=${moeda}`
  const now = Date.now()

  // Verifica se há uma cotação em cache válida
  if (lkngcCachedCryptoPriceEthereum[cripto] && (now - lkngcCachedCryptoPriceEthereum[cripto].timestamp < lkngcCacheDurationEthereum)) {
    lkngcProcessCryptoPriceEthereum(moeda, cripto, lkngcCachedCryptoPriceEthereum[cripto].data)
    return
  }

  fetch(url)
    .then(response => response.json())
    .then(data => {
      lkngcCachedCryptoPriceEthereum[cripto] = {
        data,
        timestamp: now
      }
      lkngcProcessCryptoPriceEthereum(moeda, cripto, data)
    })
    .catch(error => console.error('Erro ao atualizar a cotação da criptomoeda:', error))
}

function lkngcProcessCryptoPriceEthereum (moeda, cripto, response) {
  const valorText = document.querySelector('.givewp-elements-donationSummary__list__item__value').textContent
  const valorLimpo = valorText.replace(/[^\d.,]/g, '')

  const cotacaoCripto = response[cripto][moeda.toLowerCase()]
  let totalCripto = valorLimpo
    .replace(lkngcGiveCryptoGlobalsETH.thousand_separator, '')
    .replace(lkngcGiveCryptoGlobalsETH.decimal_separator, '.')
  totalCripto = totalCripto / cotacaoCripto // Conversão de moeda para criptomoeda
  totalCripto = Math.round(totalCripto * 1e8) / 1e8 // Arredonda para 8 casas decimais

  const labelCripto = document.getElementById('lkngcTotalEth')
  const inputCripto = document.getElementById('lkngc_eth_value')

  if (labelCripto && inputCripto) {
    labelCripto.innerHTML = totalCripto.toString().replace('.', lkngcGiveCryptoGlobalsETH.decimal_separator)
    inputCripto.value = totalCripto
    lkngcGenerateQrCodeEthereum(cripto)
  }
}

function lkngcInitTempEthereumAfter (duracao, display, cripto) {
  if (lkngcTimerIntervalIdEthereum) {
    clearInterval(lkngcTimerIntervalIdEthereum)
  }

  const multiMoeda = document.getElementById('give-mc-select')
  let temporizador = duracao
  // const codigoMoeda = multiMoeda?.value ?? give_global_vars.currency;
  const codigoMoeda = multiMoeda ?? lkngcGiveCryptoGlobalsETH.currency

  if (lkngcGiveCryptoGlobalsETH.advDebug) {
    console.log('codigoMoeda:', codigoMoeda)
  }

  lkngcUpdateCryptoExRateEthereum(codigoMoeda, cripto)

  const lkngcUpdateTemporizerEthereum = () => {
    const minutos = String(Math.floor(temporizador / 60)).padStart(2, '0')
    const segundos = String(temporizador % 60).padStart(2, '0')
    display.textContent = `${minutos}:${segundos}`

    if (--temporizador < 0) {
      lkngcUpdateCryptoExRateEthereum(codigoMoeda, cripto)
      temporizador = duracao
    }
  }

  lkngcTimerIntervalIdEthereum = setInterval(lkngcUpdateTemporizerEthereum, 1000)
}

function lkngcDebounceETH (func, delay) {
  let timer
  return function () {
    const context = this
    const args = arguments
    clearTimeout(timer)
    timer = setTimeout(() => {
      func.apply(context, args)
    }, delay)
  }
}

function lkngcObserveDonationChangesEthereum () {
  if (lkngcGiveCryptoGlobalsETH.advDebug) { console.log('lkngcObserveDonationChangesEthereum') }

  if (typeof lkngcEthereumObserver !== 'undefined' && lkngcEthereumObserver) { lkngcEthereumObserver.disconnect() }

  if (lkngcEthereumObserver) { lkngcEthereumObserver.disconnect() }

  const targetNode = document.querySelector('.givewp-elements-donationSummary__list__item__value')
  if (!targetNode) return

  lkngcEthereumObserver = new MutationObserver(lkngcDebounceETH(function (mutationsList, observer) {
    for (const mutation of mutationsList) {
      if (mutation.type === 'childList' || mutation.type === 'characterData') {
        const fieldset = document.getElementById('lkngc_cryptocurrency_fields-ethereum')
        if (!fieldset) return

        if (lkngcActivatorEthereum) { lkngcNewPaymentEthereum('ethereum') }
      }
    }
  }, 500))

  lkngcEthereumObserver.observe(targetNode, {
    attributes: true,
    childList: true,
    subtree: true,
    characterData: true
  })
}

function lkngcObserveMetodoChangesEthereum () {
  const checkGateway = () => {
    const gatewayItem = document.querySelector('.givewp-fields-gateways__list .givewp-fields-gateways__gateway.givewp-fields-gateways__gateway--lkngc-cryptocurrencyforgivewp-ethereum')
    const submitButton = document.querySelector('button[type="submit"]')

    if (!gatewayItem || !submitButton) { // Aguarda o elemento ser carregado
      setTimeout(checkGateway, 100)
      return
    }

    const isActive = gatewayItem.classList.contains('givewp-fields-gateways__gateway--active')

    if (isActive) {
      submitButton.disabled = true
    } else {
      submitButton.removeAttribute('disabled')
      lkngcRemovelkngcEthereumObserver()
    }
  }

  const lkngcSetupObserverEthereum = () => {
    console.log('observer chamado')
    const targetNode = document.querySelector('.givewp-fields-gateways__list')

    if (targetNode) {
      const config = { childList: true, subtree: true, attributes: true }

      // Remove o observer anterior se existir
      lkngcRemovelkngcEthereumObserver()

      lkngcEthereumObserverMethod = new MutationObserver((mutationsList) => {
        mutationsList.forEach(mutation => {
          if (mutation.type === 'childList' || (mutation.type === 'attributes' && mutation.attributeName === 'class')) {
            checkGateway()
          }
        })
      })

      lkngcEthereumObserverMethod.observe(targetNode, config)

      // Executa a verificação inicial após configurar o observer
      checkGateway()
    } else {
      setTimeout(lkngcSetupObserverEthereum, 500) // Tenta novamente após 500ms
    }
  }

  // Inicia a configuração do observer com uma pequena espera para garantir que o DOM esteja pronto
  setTimeout(lkngcSetupObserverEthereum, 500)
}

function lkngcRemovelkngcEthereumObserver () {
  console.log('remove observer')
  if (lkngcEthereumObserverMethod) {
    lkngcEthereumObserverMethod.disconnect()
    lkngcEthereumObserverMethod = null
    if (lkngcGiveCryptoGlobalsETH.advDebug) {
      console.log('ObserverETH desconectado')
    }
  }
}

const LkngcCriptocurrencyForGivewpEthereum = {
  id: 'lkngc-cryptocurrencyforgivewp-ethereum',
  async initialize () {
    // Aqui vai todas as funções necessárias ao carregar a página de pagamento
  },
  async beforeCreatePayment (values) {
    // Aqui vai tudo que precisa rodar depois de submeter o formulário e antes do pagamento ser completado
    // Ponha validações e adicione atributos que você vai precisar no back-end aqui

    // Caso detecte algum erro de validação você pode adicionar uma exceção
    // A mensagem de erro aparecerá para o cliente já formatada

    // Retorna os atributos usados pelo back-end
    // Atributos do objeto value já são passados por padrão

    if (values.firstname === 'error') {
      console.log('gateway error')
      throw new Error('Gateway failed')
    }

    const inputCripto = document.getElementById('lkngcTotalEth')

    if (lkngcGiveCryptoGlobalsETH.advDebug) {
      console.log('inputCripto: ', inputCripto)
    }

    return {
      cryptoValue: inputCripto.innerText
    }
  },
  async afterCreatePayment (response) {
    // Depois da criação do pagamento
  },

  Fields () {
    lkngcObserveDonationChangesEthereum()
    lkngcObserveMetodoChangesEthereum()

    // Função para exibir um aviso de erro no frontend
    function lkngcGiveCryptoPrintFrontendNotice (title, message) {
      return /* #__PURE__ */ React.createElement(
        'div',
        { className: 'error-notice' },
        /* #__PURE__ */ React.createElement('strong', null, title),
        ' ',
        message
      )
    }

    // Verifica se a carteira Ethereum está disponível, se não, retorna um aviso
    if (!lkngcGiveCryptoGlobalsETH.walletEthereum) {
      return lkngcGiveCryptoPrintFrontendNotice('Erro:', lkngcGiveCryptoGlobalsETH.MenssageErrorEthereum)
    } else {
      return /* #__PURE__ */ React.createElement(
        React.Fragment,
        null,
        /* #__PURE__ */ React.createElement(
          'fieldset',
          { id: 'lkngc_cryptocurrency_fields-ethereum', className: 'give-do-validate' },
          /* #__PURE__ */ React.createElement('legend', null, 'Payment Information'),

          // Informação de segurança do site
          /* #__PURE__ */ React.createElement(
            'div',
            { id: 'give_secure_site_wrapper' },
            /* #__PURE__ */ React.createElement('span', { className: 'give-icon padlock' }),
            /* #__PURE__ */ React.createElement('span', null, 'Secure Donation via SSL Encryption.')
          ),

          // Mensagem de erro para preenchimento incorreto dos campos
          /* #__PURE__ */ React.createElement(
            'div',
            {
              id: 'lkngc-error-message-ethereum',
              style: {
                color: 'white',
                marginBottom: '10px',
                display: 'none',
                fontSize: '20px',
                fontWeight: 'bold',
                textAlign: 'center',
                width: '100%',
                padding: '10px',
                backgroundColor: '#cb6a47',
                border: '2px solid #f5c6cb',
                borderRadius: '2px'
              }
            },
            'Por favor, preencha todos os campos corretamente.'
          ),

          // Campo oculto para o valor em Ethereum
          /* #__PURE__ */ React.createElement('input', {
            id: 'lkngc_eth_value',
            name: 'lkn_crypto_value',
            type: 'hidden',
            value: '0'
          }),

          // Seção do QR code e informações de criptomoeda
          /* #__PURE__ */ React.createElement(
            'div',
            { id: 'lkn_cryptocurrency_wrapper', className: 'lkn_crypto_hidden', style: { display: 'none' } },
            /* #__PURE__ */ React.createElement(
              'a',
              {
                id: 'lkn_crypto_wallet_ethereum',
                href: `ethereum:${lkngcGiveCryptoGlobalsETH.walletEthereum}?amount=`,
                target: '_blank'
              },
              /* #__PURE__ */ React.createElement('div', { id: 'qrcode' })
            ),

            // Exibe o valor da doação em Ethereum
            /* #__PURE__ */ React.createElement(
              'div',
              { id: 'lkn_crypto_price_wrapper' },
              /* #__PURE__ */ React.createElement(
                'div',
                null,
                /* #__PURE__ */ React.createElement('span', null, 'Donation amount:')
              ),
              /* #__PURE__ */ React.createElement(
                'div',
                null,
                /* #__PURE__ */ React.createElement('span', { id: 'lkngcTotalEth' }, '0.00000000'),
                /* #__PURE__ */ React.createElement('span', null, ' ETH')
              )
            ),

            // Input de carteira para copiar o endereço
            /* #__PURE__ */ React.createElement(
              'div',
              { id: 'lkn_cryptocurrency_input_wrapper' },
              /* #__PURE__ */ React.createElement(
                'button',
                {
                  type: 'button',
                  className: 'copy_adress popup',
                  onClick: lkngcCopyTextEthereum
                },
                /* #__PURE__ */ React.createElement('span', null, 'Click here to copy'),
                /* #__PURE__ */ React.createElement('span', { className: 'dashicons dashicons-admin-page' }),
                /* #__PURE__ */ React.createElement('span', { className: 'popuptext', id: 'myPopup' }, 'Copied!')
              ),
              /* #__PURE__ */ React.createElement(
                'div',
                null,
                /* #__PURE__ */ React.createElement('span', { className: 'lkngcWalletEthereumInput' }, lkngcGiveCryptoGlobalsETH.walletEthereum)
              )
            ),

            // Temporizador para o tempo restante do pagamento
            /* #__PURE__ */ React.createElement(
              'div',
              { id: 'lkn_crypto_temp' },
              'Remaining time to make the payment: ',
              /* #__PURE__ */ React.createElement('span', { id: 'timer' })
            )
          ),

          // Botão para gerar o QR Code
          /* #__PURE__ */ React.createElement(
            'div',
            { id: 'btnEthWrapper' },
            /* #__PURE__ */ React.createElement(
              'button',
              {
                type: 'button',
                id: 'btnDonateCrypto',
                className: 'give-btn lkn-btn-gateway',
                onClick: () => lkngcNewPaymentEthereum('ethereum')
              },
              'Generate QR Code'
            )
          ),

          // Botão para confirmar o pagamento
          /* #__PURE__ */ React.createElement(
            'div',
            { id: 'btnSubmitWrapper' },
            /* #__PURE__ */ React.createElement(
              'button',
              {
                type: 'button',
                id: 'btnSubmitForm',
                className: 'give-btn lkn-btn-gateway',
                style: { display: 'none' },
                onClick: lkngcSubmitFormEthereum
              },
              'Confirm payment'
            )
          )
        )
      )
    }
  }
}

// Registrar o gateway de criptomoedas Ethereum
window.givewp.gateways.register(LkngcCriptocurrencyForGivewpEthereum)
