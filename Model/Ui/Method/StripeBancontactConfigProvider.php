<?php

namespace Pensopay\Gateway\Model\Ui\Method;

use Pensopay\Gateway\Model\Ui\ConfigProvider;

final class StripeBancontactConfigProvider extends ConfigProvider
{
    const CODE = 'pensopay_gateway_stripebancontact';
    protected string $_code = self::CODE;
}
