<?php

namespace Pensopay\Gateway\Model\Ui\Method;

use Pensopay\Gateway\Model\Ui\ConfigProvider;

final class ViabillConfigProvider extends ConfigProvider
{
    const CODE = 'pensopay_gateway_viabill';
    protected string $_code = self::CODE;
}
