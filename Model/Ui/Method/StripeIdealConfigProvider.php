<?php

namespace Pensopay\Gateway\Model\Ui\Method;

use Pensopay\Gateway\Model\Ui\ConfigProvider;

final class StripeIdealConfigProvider extends ConfigProvider
{
    const CODE = 'pensopay_gateway_stripeideal';
    protected string $_code = self::CODE;
}
