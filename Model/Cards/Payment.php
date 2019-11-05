<?php


namespace Saulmoralespa\PayuLatamSDK\Model\Cards;

use Magento\Sales\Model\Order\Payment\Transaction;
use PayuSDK\Api\PayUCountries;
use PayuSDK\Exceptions\PayUException;
use PayuSDK\PayUPayments;
use PayuSDK\Util\PayUParameters;

class Payment extends \Magento\Payment\Model\Method\Cc
{
    const CODE = 'payulatamsdk_cards';

    protected $_code = self::CODE;

    protected $_isGateway = true;

    protected $_canOrder = true;

    protected $_canAuthorize = true;

    protected $_canCapture = true;

    protected $_canCapturePartial = true;

    protected $_canRefund = true;

    protected $_canRefundInvoicePartial = true;

    protected $_canFetchTransactionInfo = true;

    protected $_canReviewPayment = true;

    protected $_supportedCurrencyCodes = ['ARS','BRL','COP','MXN','USD','PEN'];

    protected $_debugReplacePrivateDataKeys = ['number', 'exp_month', 'exp_year', 'cvc'];

    protected $_helperData;

    protected $_url;

    protected $_transactionBuilder;

    public function __construct(
        \Saulmoralespa\PayuLatamSDK\Helper\Data $helperData,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\App\Action\Context $actionContext,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder,
        array $data = []
    )
    {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $moduleList,
            $localeDate,
            null,
            null,
            $data
        );

        $this->_helperData = $helperData;
        $this->_url = $actionContext->getUrl();
        $this->_transactionBuilder = $transactionBuilder;
    }


    public function isActive($storeId = null)
    {
        if ($this->_helperData->getActiveCards()) return true;
        return false;
    }

    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if ($quote && (
                $quote->getBaseGrandTotal() < $this->_helperData->getMinOrderTotal()
                || ($this->_helperData->getMaxOrderTotal() && $quote->getBaseGrandTotal() > $this->_helperData->getMaxOrderTotal()))
        ) {
            return false;
        }
        if (!$this->_helperData->getMerchantId() ||
            !$this->_helperData->getAccountId() ||
            !$this->_helperData->getApiKey() ||
            !$this->_helperData->getApiLogin()){
            return false;
        }

        return true;
    }

    public function canUseForCurrency($currencyCode)
    {
        if (!in_array($currencyCode, $this->_supportedCurrencyCodes))
            return false;
        return true;
    }

    public function assignData(\Magento\Framework\DataObject $data)
    {
        parent::assignData($data);
        $content = (array)$data->getData();
        $info = $this->getData('info_instance');


        if (key_exists('additional_data', $content)) {
            if (key_exists('card_holder_name', $content['additional_data']) &&
                key_exists('document_number', $content['additional_data']) &&
                key_exists('installments_numbers', $content['additional_data'])) {
                $additionalData = $content['additional_data'];
                $info->setAdditionalInformation(
                    'card_holder_name', $additionalData['card_holder_name']
                );
                $info->setAdditionalInformation(
                    'document_number', $additionalData['document_number']
                );
                $info->setAdditionalInformation(
                    'installments_numbers', $additionalData['installments_numbers']
                );
                $info->setCcType($additionalData['cc_type'])
                    ->setCcExpYear($additionalData['cc_exp_year'])
                    ->setCcExpMonth($additionalData['cc_exp_month'])
                    ->setCcNumber($additionalData['card_number'])
                    ->setCcCid($additionalData['cvc']);
            }else {
                //$this->_logger->error(__('[Neogateway]: Card holder name not found.'));
                //$this->_neoLogger->debug(__('[Neogateway]: Card holder name not found.'));
                throw new \Magento\Framework\Validator\Exception(
                    __("Payment capturing error.")
                );
            }
            return $this;
        }
        throw new \Magento\Framework\Validator\Exception(
            __("Payment capturing error.")
        );
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this|\Magento\Payment\Model\Method\Cc
     * @throws \Magento\Framework\Validator\Exception
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $order = $payment->getOrder();

        $address = $this->getAddress($order);

        $info = $this->getData('info_instance');

        $card_holder_name = $info->getAdditionalInformation(
            'card_holder_name'
        );

        $document_number = $info->getAdditionalInformation(
            'document_number'
        );

        $installments_numbers = $info->getAdditionalInformation(
            'installments_numbers'
        );

        $month = sprintf('%02d',$payment->getCcExpMonth());
        $year =  $payment->getCcExpYear();
        $expireDate = "$year/$month";
        $reference = time();

        $this->_helperData->credentialsPayu();

        $parameters = array(
            //Ingrese aquí el identificador de la cuenta.
            PayUParameters::ACCOUNT_ID => $this->_helperData->getAccountId(),
            //Ingrese aquí el código de referencia.
            PayUParameters::REFERENCE_CODE => $reference,
            //Ingrese aquí la descripción.
            PayUParameters::DESCRIPTION => __('Order # %1',  $order->getIncrementId()),
            // -- Valores --
            //Ingrese aquí el valor de la transacción.
            PayUParameters::VALUE => $order->getGrandTotal(),
            //Ingrese aquí el valor del IVA (Impuesto al Valor Agregado solo valido para Colombia) de la transacción,
            //si se envía el IVA nulo el sistema aplicará el 19% automáticamente. Puede contener dos dígitos decimales.
            //Ej: 19000.00. En caso de no tener IVA debe enviarse en 0.
            PayUParameters::TAX_VALUE => "0",
            //Ingrese aquí el valor base sobre el cual se calcula el IVA (solo valido para Colombia).
            //En caso de que no tenga IVA debe enviarse en 0.
            PayUParameters::TAX_RETURN_BASE => "0",
            //Ingrese aquí la moneda.
            PayUParameters::CURRENCY => $order->getBaseCurrencyCode(),
            // -- Comprador
            //Ingrese aquí el nombre del comprador.
            PayUParameters::BUYER_NAME => $card_holder_name,
            //Ingrese aquí el email del comprador.
            PayUParameters::BUYER_EMAIL => $order->getCustomerEmail(),
            //Ingrese aquí el teléfono de contacto del comprador.
            PayUParameters::BUYER_CONTACT_PHONE => $address->getTelephone(),
            //Ingrese aquí el documento de contacto del comprador.
            PayUParameters::BUYER_DNI => $document_number,
            //Ingrese aquí la dirección del comprador.
            PayUParameters::BUYER_STREET => $address->getData("street"),
            PayUParameters::BUYER_STREET_2 => $address->getData("street"),
            PayUParameters::BUYER_CITY => $address->getCity(),
            PayUParameters::BUYER_STATE => $address->getRegionCode(),
            PayUParameters::BUYER_COUNTRY => $address->getCountryId(),
            PayUParameters::BUYER_POSTAL_CODE => $address->getPostcode(),
            PayUParameters::BUYER_PHONE => $address->getTelephone(),

            // -- pagador --
            //Ingrese aquí el nombre del pagador.
            PayUParameters::PAYER_NAME => $card_holder_name,
            //Ingrese aquí el email del pagador.
            PayUParameters::PAYER_EMAIL => $order->getCustomerEmail(),
            //Ingrese aquí el teléfono de contacto del pagador.
            PayUParameters::PAYER_CONTACT_PHONE => $address->getTelephone(),
            //Ingrese aquí el documento de contacto del pagador.
            PayUParameters::PAYER_DNI => $document_number,
            //Ingrese aquí la dirección del pagador.
            PayUParameters::PAYER_STREET => $address->getData("street"),
            PayUParameters::PAYER_STREET_2 => $address->getData("street"),
            PayUParameters::PAYER_CITY => $address->getCity(),
            PayUParameters::PAYER_STATE => $address->getRegionCode(),
            PayUParameters::PAYER_COUNTRY => $address->getCountryId(),
            PayUParameters::PAYER_POSTAL_CODE => $address->getTelephone(),
            PayUParameters::PAYER_PHONE => $address->getPostcode(),

            // -- Datos de la tarjeta de crédito --
            //Ingrese aquí el número de la tarjeta de crédito
            PayUParameters::CREDIT_CARD_NUMBER => $payment->getCcNumber(),
            //Ingrese aquí la fecha de vencimiento de la tarjeta de crédito
            PayUParameters::CREDIT_CARD_EXPIRATION_DATE => $expireDate,
            //Ingrese aquí el código de seguridad de la tarjeta de crédito
            PayUParameters::CREDIT_CARD_SECURITY_CODE=> $payment->getCcCid(),
            //Ingrese aquí el nombre de la tarjeta de crédito
            //VISA||MASTERCARD||AMEX||DINERS
            PayUParameters::PAYMENT_METHOD => $this->getTypeCard($payment->getCcType()),

            //Ingrese aquí el número de cuotas.
            PayUParameters::INSTALLMENTS_NUMBER => $installments_numbers,
            //Ingrese aquí el nombre del pais.
            PayUParameters::COUNTRY => $this->getCountryPayu(),

            //Session id del device.
            PayUParameters::DEVICE_SESSION_ID => md5(session_id().microtime()),
            //IP del pagadador
            PayUParameters::IP_ADDRESS => $this->getIP(),
            //Cookie de la sesión actual.
            PayUParameters::PAYER_COOKIE => md5(session_id().microtime()),
            //Cookie de la sesión actual.
            PayUParameters::USER_AGENT => $_SERVER['HTTP_USER_AGENT'],

            PayUParameters::NOTIFY_URL => $this->_url->getUrl('payulatamsdk/payment/notify', ['order_id' => $order->getIncrementId()] )
        );

        try{
            $response = PayUPayments::doAuthorizationAndCapture($parameters);

            if ($response->transactionResponse->state == "PENDING") {
                $payment->setTransactionId($reference)
                    ->setIsTransactionClosed(0);
                $payment->setParentTransactionId($order->getIncrementId());
                $payment->setIsTransactionPending(true);

                $transaction = $this->_transactionBuilder->setPayment($payment)
                    ->setOrder($order)
                    ->setTransactionId($payment->getTransactionId())
                    ->build(Transaction::TYPE_ORDER);
                $payment->addTransactionCommentsToOrder($transaction, __('Pending payment'));
                $statuses = $this->_helperData->getOrderStates();
                $status = $statuses["pending"];
                $state = \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT;
                $order->setState($state)->setStatus($status);
                $payment->setSkipOrderProcessing(true);
                $order->save();
            }elseif ($response->transactionResponse->state === "DECLINED"){
                throw new \Magento\Framework\Validator\Exception(__('Declined transaction, try again'));
            }

        }catch (PayUException $exception){
            throw new \Magento\Framework\Validator\Exception(__($exception->getMessage()));
        }

        return $this;
    }

    public function getTypeCard($type)
    {
        $types = [
            'VI' => 'VISA',
            'MC' => 'MASTERCARD',
            'AE' => 'AME',
            'DN' => 'DINERS',
            'EL' => 'ELO',
            'HI' => 'HIPERCARD'
        ];

        return $types[$type];
    }

    public function getIP()
    {
        return ($_SERVER['REMOTE_ADDR'] == '::1' || $_SERVER['REMOTE_ADDR'] == '::' ||
            !preg_match('/^((?:25[0-5]|2[0-4][0-9]|[01]?[0-9]?[0-9]).){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9]?[0-9])$/m',
                $_SERVER['REMOTE_ADDR'])) ? '127.0.0.1' : $_SERVER['REMOTE_ADDR'];
    }

    public function getCountryPayu()
    {
        $countryShop = $this->_helperData->getCountry();
        $countryName = PayUCountries::CO;
        if ($countryShop === 'AR')
            $countryName = PayUCountries::AR;
        if ($countryShop === 'BR')
            $countryName = PayUCountries::BR;
        if ($countryShop === 'MX')
            $countryName = PayUCountries::MX;
        if ($countryShop === 'PA')
            $countryName = PayUCountries::PA;
        if ($countryShop === 'PE')
            $countryName = PayUCountries::PE;
        return $countryName;
    }

    public function getAddress($order)
    {
        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();
        if ($billingAddress){
            return $billingAddress;
        }
        return $shippingAddress;
    }

    public function formattedAmount($amount, $decimals = 2)
    {
        $amount = number_format($amount, $decimals,'.','');
        return $amount;
    }

    public function getSignCreate(array $data = [])
    {
        return md5(
            $this->_helperData->getApiKey() . "~" .
            $this->_helperData->getMerchantId() . "~" .
            $data['referenceCode'] ."~".
            $data['amount']."~".
            $data['currency']
        );
    }
    public function getSignValidate(array $data = [])
    {
        return md5(
            $this->_helperData->getApiKey() . "~" .
            $this->_helperData->getMerchantId() . "~" .
            $data['referenceCode'] . "~" .
            $data['amount'] . "~" .
            $data['currency'] . "~" .
            $data['state_pol']
        );
    }

}