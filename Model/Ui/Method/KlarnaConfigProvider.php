<?php

namespace Pensopay\Gateway\Model\Ui\Method;

use Pensopay\Gateway\Model\Ui\ConfigProvider;

final class KlarnaConfigProvider extends ConfigProvider
{
    const CODE = 'pensopay_gateway_klarna';
    protected string $_code = self::CODE;
}
