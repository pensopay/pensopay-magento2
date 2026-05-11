<?php

namespace Pensopay\Gateway\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Pensopay\Gateway\Helper\Data;

class SalesOrderPaymentPlaceStart implements ObserverInterface
{
    /**
     * Prevent order emails from being sent prematurely
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var Payment $payment */
        $payment = $observer->getPayment();

        if (in_array($payment->getMethod(), Data::PENSOPAY_METHOD_CODES, false)) {
            /** @var Order $order */
            $order = $payment->getOrder();
            $order->setCanSendNewEmailFlag(false)
                ->setIsCustomerNotified(false)
                ->save();
        }
    }
}
