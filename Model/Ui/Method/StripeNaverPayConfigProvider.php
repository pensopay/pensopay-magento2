<?php

namespace Pensopay\Gateway\Model\Ui\Method;

use Pensopay\Gateway\Model\Ui\ConfigProvider;

final class StripeNaverPayConfigProvider extends ConfigProvider
{
    const CODE = 'pensopay_gateway_stripenaverpay';
    protected string $_code = self::CODE;
}
