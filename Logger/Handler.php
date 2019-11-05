<?php


namespace Saulmoralespa\PayuLatamSDK\Logger;


class Handler extends  \Magento\Framework\Logger\Handler\Base
{
    protected $fileName = '/var/log/payulatamsdk/info.log';
    protected $loggerType = \Monolog\Logger::INFO;
}