<?php

namespace Pensopay\Gateway\Model\Ui\Method;

use Pensopay\Gateway\Model\Ui\ConfigProvider;

final class AnydayConfigProvider extends ConfigProvider
{
    const CODE = 'pensopay_gateway_anyday';
    protected string $_code = self::CODE;
}
