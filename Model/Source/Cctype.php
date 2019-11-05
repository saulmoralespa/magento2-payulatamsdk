<?php


namespace Saulmoralespa\PayuLatamSDK\Model\Source;

use Magento\Payment\Model\Source\Cctype as PaymentCctype;
use Saulmoralespa\PayuLatamSDK\Helper\Data;

class Cctype extends PaymentCctype
{
    protected $_helperData;

    public function __construct(
        \Magento\Payment\Model\Config $paymentConfig,
        Data $helperData
    )
    {
        parent::__construct($paymentConfig);
        $this->_helperData = $helperData;
    }

    public function getAllowedTypes()
    {
        $cards = ['VI', 'MC', 'AE', 'DN', 'EL'];

        if ($this->_helperData->getCountry() === 'AR')
            $cards = ['VI', 'MC', 'AE'];
        if ($this->_helperData->getCountry() === 'BR')
            $cards = ['VI', 'MC', 'AE', 'DN', 'EL'];
        if ($this->_helperData->getCountry() === 'CO')
            $cards = ['VI', 'MC', 'AE', 'DN'];
        if ($this->_helperData->getCountry() === 'MX')
            $cards = ['VI', 'MC', 'AE'];
        if ($this->_helperData->getCountry() === 'PA')
            $cards = ['MC'];

        return $cards;
    }
}