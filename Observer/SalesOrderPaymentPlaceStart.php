<?php

namespace PensoPay\Gateway\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use PensoPay\Gateway\Model\Ui\Method\AnydayConfigProvider;
use PensoPay\Gateway\Model\Ui\Method\PensoPayConfigProvider;
use PensoPay\Gateway\Model\Ui\Method\ViabillConfigProvider;
use PensoPay\Gateway\Model\Ui\Method\MobilePayConfigProvider;
use PensoPay\Gateway\Model\Ui\Method\ApplePayConfigProvider;
use PensoPay\Gateway\Model\Ui\Method\SwishConfigProvider;
use PensoPay\Gateway\Model\Ui\Method\KlarnaConfigProvider;

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

        if (in_array($payment->getMethod(), [
            PensoPayConfigProvider::CODE,
            ViabillConfigProvider::CODE,
            AnydayConfigProvider::CODE,
            ApplePayConfigProvider::CODE,
            MobilePayConfigProvider::CODE,
            SwishConfigProvider::CODE,
            KlarnaConfigProvider::CODE,
        ], false)) {
            /** @var Order $order */
            $order = $payment->getOrder();
            $order->setCanSendNewEmailFlag(false)
                ->setIsCustomerNotified(false)
                ->save();
        }
    }
}
