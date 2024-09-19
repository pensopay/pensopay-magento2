<?php

namespace Pensopay\Gateway\Model\Ui\Method;

use Pensopay\Gateway\Model\Ui\ConfigProvider;

final class ApplePayConfigProvider extends ConfigProvider
{
    const CODE = 'pensopay_gateway_applepay';
    protected string $_code = self::CODE;
}
