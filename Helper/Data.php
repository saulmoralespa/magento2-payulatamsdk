<?php


namespace Saulmoralespa\PayuLatamSDK\Helper;


use Magento\Framework\View\LayoutFactory;
use PayuSDK\Api\Environment;
use PayuSDK\Api\SupportedLanguages;
use PayuSDK\PayU;

class Data extends \Magento\Payment\Helper\Data
{
    protected $_payulatamSDKLogger;

    protected $_enviroment;

    public function __construct(
        \Saulmoralespa\PayuLatamSDK\Logger\Logger $_payulatamSDKLogger,
        \Magento\Framework\App\Helper\Context $context,
        LayoutFactory $layoutFactory,
        \Magento\Payment\Model\Method\Factory $paymentMethodFactory,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Payment\Model\Config $paymentConfig,
        \Magento\Framework\App\Config\Initial $initialConfig
    )
    {
        parent::__construct(
            $context,
            $layoutFactory,
            $paymentMethodFactory,
            $appEmulation,
            $paymentConfig,
            $initialConfig
        );

        $this->_payulatamSDKLogger = $_payulatamSDKLogger;
    }

    public function getEnviroment()
    {
        return (int)$this->scopeConfig->getValue('payment/payulatamsdk/environment',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getActive()
    {
        return (int)$this->scopeConfig->getValue('payment/payulatamsdk/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getActiveCards()
    {
        return (int)$this->scopeConfig->getValue('payment/payulatamsdk_cards/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getMerchantId()
    {
        if ($this->getEnviroment()){
            return $this->scopeConfig->getValue('payment/payulatamsdk/enviroment_g/development/merchantId', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }else{
            return $this->scopeConfig->getValue('payment/payulatamsdk/enviroment_g/production/merchantId', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
    }

    public function getAccountId()
    {
        if ($this->getEnviroment()){
            return $this->scopeConfig->getValue('payment/payulatamsdk/enviroment_g/development/accountId', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }else{
            return $this->scopeConfig->getValue('payment/payulatamsdk/enviroment_g/production/accountId', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
    }

    public function getApiKey()
    {
        if ($this->getEnviroment()){
            return $this->scopeConfig->getValue('payment/payulatamsdk/enviroment_g/development/apiKey', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }else{
            return $this->scopeConfig->getValue('payment/payulatamsdk/enviroment_g/production/apiKey', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
    }

    public function getApiLogin()
    {
        if ($this->getEnviroment()){
            return $this->scopeConfig->getValue('payment/payulatamsdk/enviroment_g/development/apiLogin', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }else{
            return $this->scopeConfig->getValue('payment/payulatamsdk/enviroment_g/production/apiLogin', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
    }

    public function getCountry()
    {
        if ($this->getEnviroment()){
            return $this->scopeConfig->getValue('payment/payulatamsdk/enviroment_g/development/country', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }else{
            return $this->scopeConfig->getValue('payment/payulatamsdk/enviroment_g/production/country', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
    }

    public function getMinOrderTotal()
    {
        return $this->scopeConfig->getValue('payment/payulatamsdk/min_order_total', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getMaxOrderTotal()
    {
        return $this->scopeConfig->getValue('payment/payulatamsdk/max_order_total', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getOrderStates()
    {
        return [
            'pending' => $this->scopeConfig->getValue('payment/payulatamsdk/states/pending', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'approved' => $this->scopeConfig->getValue('payment/payulatamsdk/states/approved', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'rejected' => $this->scopeConfig->getValue('payment/payulatamsdk/states/rejected', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
        ];
    }

    public function log($message)
    {
        if (is_array($message) || is_object($message))
            $message = print_r($message, true);

        $this->_payulatamSDKLogger->debug($message);
    }

    public function getLanguagePayu()
    {
        $country = $this->getCountry();
        return $country === 'BR' ? SupportedLanguages::PT : SupportedLanguages::ES;
    }

    public function createUrl($test, $reports = false)
    {
        if ($test){
            $url = "https://sandbox.api.payulatam.com/";
        }else{
            $url = "https://api.payulatam.com/";
        }
        if ($reports){
            $url .= 'reports-api/4.0/service.cgi';
        }
        else{
            $url .= 'payments-api/4.0/service.cgi';
        }
        return $url;
    }

    public function credentialsPayu()
    {
        PayU::$apiKey = $this->getApiKey();
        PayU::$apiLogin = $this->getApiLogin();
        PayU::$merchantId = $this->getMerchantId();
        PayU::$language = $this->getLanguagePayu();
        PayU::$isTest = $this->getEnviroment();
        $urlPayment = $this->createUrl($this->getEnviroment());
        Environment::setPaymentsCustomUrl($urlPayment);
    }
}