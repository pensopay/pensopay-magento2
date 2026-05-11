<?php

namespace Pensopay\Gateway\Model\Ui\Method;

use Pensopay\Gateway\Model\Ui\ConfigProvider;

final class StripeSepaConfigProvider extends ConfigProvider
{
    const CODE = 'pensopay_gateway_stripesepa';
    protected string $_code = self::CODE;
}
