<?php
namespace PensoPay\Gateway\Block;

use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Block\ConfigurableInfo;
use Magento\Payment\Gateway\ConfigInterface;
use PensoPay\Gateway\Model\Payment;
use PensoPay\Gateway\Model\PaymentFactory;

class Info extends ConfigurableInfo
{
    /**
     * @var string
     */
    protected $_template = 'PensoPay_Gateway::info/default.phtml';

    /**
     * Returns label
     *
     * @param string $field
     * @return Phrase
     */
    protected function getLabel($field)
    {
        return __($field);
    }
}
