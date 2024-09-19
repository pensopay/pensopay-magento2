<?php

namespace Pensopay\Gateway\Model\Ui\Method;

use Pensopay\Gateway\Model\Ui\ConfigProvider;

final class MobilePayConfigProvider extends ConfigProvider
{
    const CODE = 'pensopay_gateway_mobilepay';
    protected string $_code = self::CODE;
}
