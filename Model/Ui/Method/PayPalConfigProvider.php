<?php
namespace PensoPay\Gateway\Model\Ui\Method;

use PensoPay\Gateway\Model\Ui\ConfigProvider;

final class PayPalConfigProvider extends ConfigProvider
{
    const CODE = 'pensopay_paypal';
    protected string $_code = self::CODE;
}
