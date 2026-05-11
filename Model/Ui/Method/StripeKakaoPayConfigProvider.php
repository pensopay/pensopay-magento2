<?php

namespace Pensopay\Gateway\Model\Ui\Method;

use Pensopay\Gateway\Model\Ui\ConfigProvider;

final class StripeKakaoPayConfigProvider extends ConfigProvider
{
    const CODE = 'pensopay_gateway_stripekakaopay';
    protected string $_code = self::CODE;
}
