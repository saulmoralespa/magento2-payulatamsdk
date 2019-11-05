<?php


namespace Saulmoralespa\PayuLatamSDK\Model\Config\Backend;


class Alphanumeric extends \Magento\Framework\App\Config\Value
{
    /**
     * @return \Magento\Framework\App\Config\Value|void
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function beforeSave()
    {
        if (!empty($this->getValue()) && !ctype_alnum($this->getValue()))
            throw new \Magento\Framework\Exception\ValidatorException(__('PayuLatamSDK: %1 requires alphanumeric', $this->getPath()));
        $this->setValue($this->getValue());
        parent::beforeSave();
    }
}