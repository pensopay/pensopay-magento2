<?php

namespace PensoPay\Gateway\Block\Adminhtml\Virtualterminal\Fieldset;

use Magento\Framework\View\Element\Context;

class Status extends \Magento\Framework\View\Element\AbstractBlock
{
    /** @var \PensoPay\Gateway\Model\PaymentFactory $_paymentFactory */
    protected $_paymentFactory;

    /** @var \PensoPay\Gateway\Helper\Data $_pensoPayHelper\ */
    protected $_pensoPayHelper;

    public function __construct(
        Context $context,
        \PensoPay\Gateway\Model\PaymentFactory $paymentFactory,
        \PensoPay\Gateway\Helper\Data $pensoPayHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_paymentFactory = $paymentFactory;
        $this->_pensoPayHelper = $pensoPayHelper;
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        $paymentId = $this->_request->getParam('id');
        if ($paymentId) {
            /** @var \PensoPay\Gateway\Model\Payment $payment */
            $payment = $this->_paymentFactory->create();
            $payment->load($paymentId);
            if ($payment->getId()) {
                $extraClass = $this->_pensoPayHelper->getStatusColorCode($payment->getLastCode());
                return "
                <div class='payment-status {$extraClass}'>
                    {$payment->getDisplayStatus()}
                </div>";
            }
        }
        return parent::_toHtml();
    }
}
