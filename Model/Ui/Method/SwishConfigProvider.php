<?php

namespace Pensopay\Gateway\Model\Ui\Method;

use Pensopay\Gateway\Model\Ui\ConfigProvider;

final class SwishConfigProvider extends ConfigProvider
{
    const CODE = 'pensopay_gateway_swish';
    protected string $_code = self::CODE;
}
