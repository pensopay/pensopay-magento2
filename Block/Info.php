<?php

namespace Pensopay\Gateway\Block;

use Magento\Payment\Block\ConfigurableInfo;
use Pensopay\Gateway\Model\PaymentFactory;

class Info extends ConfigurableInfo
{
    protected $_template = 'Pensopay_Gateway::info/default.phtml';

    protected function getLabel($field)
    {
        return __($field);
    }
}
