<?php


namespace Saulmoralespa\PayuLatamSDK\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use PayuSDK\PayUReports;

class ConfigObserver implements ObserverInterface
{

    protected $_helperData;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Saulmoralespa\PayuLatamSDK\Helper\Data $helperData
    )
    {
        $this->_scopeConfig = $scopeConfig;
        $this->_helperData = $helperData;
    }

    /**
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        if ($this->_helperData->getActive() &&
            !$this->_helperData->getEnviroment() &&
            $this->_helperData->getMerchantId() &&
            $this->_helperData->getApiKey() &&
            $this->_helperData->getApiLogin()
        ){
            try{
                $this->_helperData->credentialsPayu();
                $response = PayUReports::doPing();
            }catch (\Exception $exception){
                throw new LocalizedException(__("PayuLatamSDK: " . $exception->getMessage()));
            }
        }
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
}