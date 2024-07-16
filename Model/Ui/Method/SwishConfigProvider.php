<?php
namespace PensoPay\Gateway\Model\Ui\Method;

use PensoPay\Gateway\Model\Ui\ConfigProvider;

final class SwishConfigProvider extends ConfigProvider
{
    const CODE = 'pensopay_swish';
    protected string $_code = self::CODE;
}
