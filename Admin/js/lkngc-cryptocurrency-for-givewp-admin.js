(function ($) {
  'use strict'

  $(document).ready(function () {
    // Params for page verification.
    const siteUrl = new URLSearchParams(window.location.search)
    const page = siteUrl.get('page')
    const view = siteUrl.get('view')
    const tab = siteUrl.get('tab')
    const section = siteUrl.get('section')

    // Page verification (Give Donation View Payment Details):
    if (page === 'give-payment-history' && view === 'view-payment-details') {
      // The site name for POST request url.
      const siteName = window.location.hostname

      // The ID of donation for request body.
      const donationId = siteUrl.get('id')

      // The crypto value of donation for request body.
      const crypValue = $('#lkn-give-criptocurrency-meta-cryptvalue').contents().last().text().trim()
      const crypto = $('#lkn-give-criptocurrency-crypto').contents().last().text().trim()

      // The wallet of donation for request body.
      const wallet = $('#lkn-give-criptocurrency-meta-wallet').contents().last().text().trim()

      // The token via wp_localize.
      const token = window.lknCryptoGlobal.token

      // The error message via wp_localize.
      const msgError = window.lknCryptoGlobal.msg_error

      // Make json body.
      const body = {
        crypto_type: crypto,
        crypt_value: crypValue,
        wallet_regist: wallet,
        donation_id: donationId,
        token
      }

      // Runs when button is clicked.
      $('#lkn-give-criptocurrency-consult-transaction').click(function () {
      // Disable the button for not span the request.
        $('#lkn-give-criptocurrency-consult-transaction').attr({ disabled: 'disabled' })

        // Make the POST request.
        $.ajax({
          url: `https://${siteName}/wp-json/lkngc-cryptocurrency-for-givewp-verification/v1/notification/`,
          type: 'POST',
          dataType: 'json',
          data: body,

          // If success, show the alert with returned data.
          success: function (data) {
            alert(data.return_response + '.')
            console.log(data)
            // After finished the request, enable the button.
            $('#lkn-give-criptocurrency-consult-transaction').removeAttr('disabled')
            location.reload() // Reload the div for show new metadata details about transaction.
          },

          // If error, show the alert with error message.
          error: function () {
            console.log('Error')
            // After finished the request, enable the button.
            $('#lkn-give-criptocurrency-consult-transaction').removeAttr('disabled')
          }
        })
      })
    }

    // Page verification (Gateway Settings Page):
    if (page === 'give-settings' && tab === 'gateways' && section === 'lkn-cryptocurrency-settings') {
      // The Error Margin radio inputs.
      const radioErrorMargin = $('input[name="lkn_give_cryptocurrency_enable_error_margin"]')
      // Error margin 'Enabled' radio input.
      const radioEnabled = radioErrorMargin.first()
      // Error margin 'Disabled' radio input.
      const radioDisabled = radioErrorMargin.last()

      // Actual error margin radio input checked value.
      const valErrorMargin = $('input[name="lkn_give_cryptocurrency_enable_error_margin"]:checked').val()
      // Error margin text field.
      const fieldErrorMargin = $('#lkn_give_cryptocurrency_error_margin')

      // Enable the error margin field when error margin 'Enabled' is clicked.
      radioEnabled.click(function () {
        fieldErrorMargin.removeAttr('disabled')
      })

      // Disable the error margin field when error margin 'Disabled' is clicked.
      radioDisabled.click(function () {
        fieldErrorMargin.attr({ disabled: 'disabled' })
      })

      // Set attributes min-value = 0, step-value = 1, max-value = 100 for Error margin text field.
      fieldErrorMargin.attr({
        min: 0,
        step: 1,
        max: 100
      })

      // Verify the actual value of Error margin radio input and set the disable or enable for text field.
      if (valErrorMargin === 'enabled') {
        fieldErrorMargin.removeAttr('disabled')
      } else {
        fieldErrorMargin.attr({ disabled: 'disabled' })
      }
    }
  })

// eslint-disable-next-line no-undef
})(jQuery)
