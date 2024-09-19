<?php

namespace Pensopay\Gateway\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Pensopay\Gateway\Model\Adapter\PensopayAdapter;
use Pensopay\Gateway\Model\PaymentFactory;

class Transaction extends AbstractHelper
{

    protected PaymentFactory $_paymentFactory;
    protected PensopayAdapter $_pensoPayAdapter;

    public function __construct(
        Context         $context,
        PaymentFactory  $paymentFactory,
        PensopayAdapter $pensoPayAdapter
    )
    {
        parent::__construct($context);
        $this->_paymentFactory = $paymentFactory;
        $this->_pensoPayAdapter = $pensoPayAdapter;
    }


    public function getPaymentForOrderId($orderId, $transactionId = false)
    {
        $pensoPayment = $this->_paymentFactory->create();
        $pensoPayment->load($orderId, 'order_id');
        if ($transactionId) { //wants to refresh it
            $paymentInfo = $this->_pensoPayAdapter->getPayment($transactionId);
            $pensoPayment->importFromRemotePayment($paymentInfo);
            $pensoPayment->save();
        }
        return $pensoPayment;
    }
}
