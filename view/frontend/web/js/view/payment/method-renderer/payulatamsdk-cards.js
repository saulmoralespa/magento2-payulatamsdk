define(
    [
        'Magento_Payment/js/view/payment/cc-form',
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/model/customer',
        'Magento_Payment/js/model/credit-card-validation/validator'
    ],
    function (Component, $, quote, customer, validator) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Saulmoralespa_PayuLatamSDK/payment/payulatamsdk_cards'
            },

            getCode: function() {
                return 'payulatamsdk_cards';
            },

            isActive: function() {
                return true;
            },
            getData: function () {
                var number = this.creditCardNumber().replace(/\D/g,'');
                return {
                    'method': this.getCode(),
                    'additional_data': {
                        'card_number': number,
                        'cc_type': this.creditCardType(),
                        'cc_exp_year': this.creditCardExpYear(),
                        'cc_exp_month': this.creditCardExpMonth(),
                        'cvc': this.creditCardVerificationNumber(),
                        'card_holder_name': $("#card_holder_name").val(),
                        'document_number': $("#document_number").val(),
                        'installments_numbers': $("#installments_numbers").val()
                    }
                };
            },
            validate: function() {
                var $form = $('#' + this.getCode() + '-form');
                return $form.validation() && $form.validation('isValid');
            }
        });
    }
);