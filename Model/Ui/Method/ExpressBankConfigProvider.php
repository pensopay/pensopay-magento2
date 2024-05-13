<?php
namespace PensoPay\Gateway\Model\Ui\Method;

use PensoPay\Gateway\Model\Ui\ConfigProvider;

final class ExpressBankConfigProvider extends ConfigProvider
{
    const CODE = 'pensopay_expressbank';
    protected string $_code = self::CODE;
}
