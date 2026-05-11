<?php

namespace Pensopay\Gateway\Model\Ui\Method;

use Pensopay\Gateway\Model\Ui\ConfigProvider;

final class StripeMbwayConfigProvider extends ConfigProvider
{
    const CODE = 'pensopay_gateway_stripembway';
    protected string $_code = self::CODE;
}
