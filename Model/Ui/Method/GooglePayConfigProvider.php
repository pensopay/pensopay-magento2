<?php

namespace Pensopay\Gateway\Model\Ui\Method;

use Pensopay\Gateway\Model\Ui\ConfigProvider;

final class GooglePayConfigProvider extends ConfigProvider
{
    const CODE = 'pensopay_gateway_googlepay';
    protected string $_code = self::CODE;
}
