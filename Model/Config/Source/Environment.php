<?php


namespace Saulmoralespa\PayuLatamSDK\Model\Config\Source;


class Environment
{
    public function toOptionArray()
    {
        return [
            ['value' => '1', 'label' => __('Development')],
            ['value' => '0', 'label' => __('Production')]
        ];
    }
}