<?php

namespace Pensopay\Gateway\Model\Ui\Method;

use Pensopay\Gateway\Model\Ui\ConfigProvider;

final class VippsPspConfigProvider extends ConfigProvider
{
    const CODE = 'pensopay_gateway_vippspsp';
    protected string $_code = self::CODE;
}
