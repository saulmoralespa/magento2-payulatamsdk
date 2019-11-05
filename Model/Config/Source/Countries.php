<?php


namespace Saulmoralespa\PayuLatamSDK\Model\Config\Source;


class Countries
{
    public function toOptionArray()
    {
        return [
            ['value' => 'AR', 'label' => __('Argentina')],
            ['value' => 'BR', 'label' => __('Brazil')],
            ['value' => 'CO', 'label' => __('Colombia')],
            ['value' => 'MX', 'label' => __('Mexico')],
            ['value' => 'PA', 'label' => __('Panamá')],
            ['value' => 'PE', 'label' => __('Perú')]
        ];
    }
}