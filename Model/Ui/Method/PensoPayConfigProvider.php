<?php

namespace Pensopay\Gateway\Model\Ui\Method;

use Pensopay\Gateway\Model\Ui\ConfigProvider;

final class PensopayConfigProvider extends ConfigProvider
{
    const CODE = 'pensopay_gateway';
    protected string $_code = self::CODE;
}
