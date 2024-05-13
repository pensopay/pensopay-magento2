<?php
namespace PensoPay\Gateway\Model\Ui\Method;

use PensoPay\Gateway\Model\Ui\ConfigProvider;

final class AnydayConfigProvider extends ConfigProvider
{
    const CODE = 'pensopay_anyday';
    protected string $_code = self::CODE;
}
