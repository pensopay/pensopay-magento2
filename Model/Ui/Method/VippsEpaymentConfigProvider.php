<?php

namespace Pensopay\Gateway\Model\Ui\Method;

use Pensopay\Gateway\Model\Ui\ConfigProvider;

final class VippsEpaymentConfigProvider extends ConfigProvider
{
    const CODE = 'pensopay_gateway_vippsepayment';
    protected string $_code = self::CODE;
}
