<?php

namespace Pensopay\Gateway\Model\Ui\Method;

use Pensopay\Gateway\Model\Ui\ConfigProvider;

final class PaypalConfigProvider extends ConfigProvider
{
    const CODE = 'pensopay_gateway_paypal';
    protected string $_code = self::CODE;
}
