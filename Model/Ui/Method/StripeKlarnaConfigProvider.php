<?php

namespace Pensopay\Gateway\Model\Ui\Method;

use Pensopay\Gateway\Model\Ui\ConfigProvider;

final class StripeKlarnaConfigProvider extends ConfigProvider
{
    const CODE = 'pensopay_gateway_stripeklarna';
    protected string $_code = self::CODE;
}
