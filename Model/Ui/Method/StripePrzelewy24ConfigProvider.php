<?php

namespace Pensopay\Gateway\Model\Ui\Method;

use Pensopay\Gateway\Model\Ui\ConfigProvider;

final class StripePrzelewy24ConfigProvider extends ConfigProvider
{
    const CODE = 'pensopay_gateway_stripeprzelewy24';
    protected string $_code = self::CODE;
}
