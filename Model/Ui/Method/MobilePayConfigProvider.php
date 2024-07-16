<?php
namespace PensoPay\Gateway\Model\Ui\Method;

use PensoPay\Gateway\Model\Ui\ConfigProvider;

final class MobilePayConfigProvider extends ConfigProvider
{
    const CODE = 'pensopay_mobilepay';
    protected string $_code = self::CODE;
}
