<?php

namespace Pensopay\Gateway\Model\Ui\Method;

use Pensopay\Gateway\Model\Ui\ConfigProvider;

final class StripeMultibancoConfigProvider extends ConfigProvider
{
    const CODE = 'pensopay_gateway_stripemultibanco';
    protected string $_code = self::CODE;
}
