<?php

namespace PensoPay\Gateway\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order as OrderAlias;
use PensoPay\Gateway\Helper\Data;
use PensoPay\Gateway\Model\Ui\Method\AnydayConfigProvider;
use PensoPay\Gateway\Model\Ui\Method\ExpressBankConfigProvider;
use PensoPay\Gateway\Model\Ui\Method\PensoPayConfigProvider;
use PensoPay\Gateway\Model\Ui\Method\ViabillConfigProvider;
use PensoPay\Gateway\Model\Ui\Method\PayPalConfigProvider;

class SalesOrderPlaceAfter implements ObserverInterface
{
    protected Data $_pensoPayHelper;

    public function __construct(
        Data $pensoPayHelper
    ) {
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

        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $order->getPayment();

        if (in_array($payment->getMethod(), [
            PensoPayConfigProvider::CODE,
            ViabillConfigProvider::CODE,
            AnydayConfigProvider::CODE,
            ExpressBankConfigProvider::CODE,
            PayPalConfigProvider::CODE,
        ], false)) {
            /** @var OrderAlias $order */
            $this->_pensoPayHelper->setNewOrderStatus($order, true);
            $order->setCanSendNewEmailFlag(false)
                ->setIsCustomerNotified(false)
                ->save();
        }
    }
}
