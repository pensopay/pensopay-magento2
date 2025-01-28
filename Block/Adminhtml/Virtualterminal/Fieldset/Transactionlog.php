<?php

namespace Pensopay\Gateway\Block\Adminhtml\Virtualterminal\Fieldset;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Context;
use Pensopay\Gateway\Helper\Data;
use Pensopay\Gateway\Model\Payment;
use Pensopay\Gateway\Model\PaymentFactory;

class Transactionlog extends AbstractBlock
{
    protected PaymentFactory $_paymentFactory;

    protected Data $_pensoPayHelper;

    public function __construct(
        Context                                $context,
        PaymentFactory $paymentFactory,
        Data          $pensoPayHelper,
        array                                  $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_paymentFactory = $paymentFactory;
        $this->_pensoPayHelper = $pensoPayHelper;
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        return parent::_toHtml();
//
//        $paymentId = $this->_request->getParam('id');
//        if ($paymentId) {
//            /** @var Payment $payment */
//            $payment = $this->_paymentFactory->create();
//            $payment->load($paymentId);
//            if ($payment->getId()) {
//
//            }
//        }
//        return parent::_toHtml();
    }
}
