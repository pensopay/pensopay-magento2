<?php
namespace PensoPay\Gateway\Model\Ui\Method;

use PensoPay\Gateway\Model\Ui\ConfigProvider;

final class ApplePayConfigProvider extends ConfigProvider
{
    const CODE = 'pensopay_applepay';
    protected string $_code = self::CODE;
}
