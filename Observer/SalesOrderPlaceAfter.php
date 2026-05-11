<?php

namespace Pensopay\Gateway\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order as OrderAlias;
use Magento\Sales\Model\Order\Payment;
use Pensopay\Gateway\Helper\Data;

class SalesOrderPlaceAfter implements ObserverInterface
{
    protected Data $_pensoPayHelper;

    public function __construct(
        Data $pensoPayHelper
    )
    {
        $this->_pensoPayHelper = $pensoPayHelper;
    }

    /**
     * Prevent order emails from being sent prematurely
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var OrderAlias $order */
        $order = $observer->getOrder();

        /** @var Payment $payment */
        $payment = $order->getPayment();

        if (in_array($payment->getMethod(), Data::PENSOPAY_METHOD_CODES, false)) {
            /** @var OrderAlias $order */
            $this->_pensoPayHelper->setNewOrderStatus($order, true);
            $order->setCanSendNewEmailFlag(false)
                ->setIsCustomerNotified(false)
                ->save();
        }
    }
}
