<?php

namespace Pensopay\Gateway\Model\Ui\Method;

use Pensopay\Gateway\Model\Ui\ConfigProvider;

final class StripeTwintConfigProvider extends ConfigProvider
{
    const CODE = 'pensopay_gateway_stripetwint';
    protected string $_code = self::CODE;
}
