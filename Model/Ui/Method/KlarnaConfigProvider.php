<?php
namespace PensoPay\Gateway\Model\Ui\Method;

use PensoPay\Gateway\Model\Ui\ConfigProvider;

final class KlarnaConfigProvider extends ConfigProvider
{
    const CODE = 'pensopay_klarna';
    protected string $_code = self::CODE;
}
