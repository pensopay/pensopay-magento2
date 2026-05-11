<?php

namespace Pensopay\Gateway\Model\Ui\Method;

use Pensopay\Gateway\Model\Ui\ConfigProvider;

final class StripePaycoConfigProvider extends ConfigProvider
{
    const CODE = 'pensopay_gateway_stripepayco';
    protected string $_code = self::CODE;
}
