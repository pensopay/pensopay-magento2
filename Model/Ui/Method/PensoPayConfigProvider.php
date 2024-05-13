<?php
namespace PensoPay\Gateway\Model\Ui\Method;

use PensoPay\Gateway\Model\Ui\ConfigProvider;

final class PensoPayConfigProvider extends ConfigProvider
{
    const CODE = 'pensopay';
    protected string $_code = self::CODE;
}
