<?php

namespace Pensopay\Gateway\Model\Ui\Method;

use Pensopay\Gateway\Model\Ui\ConfigProvider;

final class StripeBillieConfigProvider extends ConfigProvider
{
    const CODE = 'pensopay_gateway_stripebillie';
    protected string $_code = self::CODE;
}
