<?php

namespace Pensopay\Gateway\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Pensopay\Gateway\Model\Ui\Method\AnydayConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\ApplePayConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\GooglePayConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\KlarnaConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\MobilePayConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\PensopayConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\SwishConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\ViabillConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\VippsPspConfigProvider;

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
            PensopayConfigProvider::CODE,
            ViabillConfigProvider::CODE,
            AnydayConfigProvider::CODE,
            ApplePayConfigProvider::CODE,
            GooglePayConfigProvider::CODE,
            MobilePayConfigProvider::CODE,
            SwishConfigProvider::CODE,
            KlarnaConfigProvider::CODE,
            VippsPspConfigProvider::CODE,
        ], false)) {
            /** @var Order $order */
            $order = $payment->getOrder();
            $order->setCanSendNewEmailFlag(false)
                ->setIsCustomerNotified(false)
                ->save();
        }
    }
}
