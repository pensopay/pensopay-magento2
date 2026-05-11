<?php

namespace Pensopay\Gateway\Model\Ui\Method;

use Pensopay\Gateway\Model\Ui\ConfigProvider;

final class StripeBlikConfigProvider extends ConfigProvider
{
    const CODE = 'pensopay_gateway_stripeblik';
    protected string $_code = self::CODE;
}
