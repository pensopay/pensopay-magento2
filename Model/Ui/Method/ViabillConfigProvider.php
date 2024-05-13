<?php
namespace PensoPay\Gateway\Model\Ui\Method;

use PensoPay\Gateway\Model\Ui\ConfigProvider;

final class ViabillConfigProvider extends ConfigProvider
{
    const CODE = 'pensopay_viabill';
    protected string $_code = self::CODE;
}
