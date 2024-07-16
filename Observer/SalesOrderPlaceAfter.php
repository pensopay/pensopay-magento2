<?php

namespace PensoPay\Gateway\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order as OrderAlias;
use PensoPay\Gateway\Helper\Data;
use PensoPay\Gateway\Model\Ui\Method\AnydayConfigProvider;
use PensoPay\Gateway\Model\Ui\Method\PensoPayConfigProvider;
use PensoPay\Gateway\Model\Ui\Method\ViabillConfigProvider;
use PensoPay\Gateway\Model\Ui\Method\MobilePayConfigProvider;
use PensoPay\Gateway\Model\Ui\Method\ApplePayConfigProvider;
use PensoPay\Gateway\Model\Ui\Method\SwishConfigProvider;
use PensoPay\Gateway\Model\Ui\Method\KlarnaConfigProvider;

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
            MobilePayConfigProvider::CODE,
            ApplePayConfigProvider::CODE,
            SwishConfigProvider::CODE,
            KlarnaConfigProvider::CODE,
        ], false)) {
            /** @var OrderAlias $order */
            $this->_pensoPayHelper->setNewOrderStatus($order, true);
            $order->setCanSendNewEmailFlag(false)
                ->setIsCustomerNotified(false)
                ->save();
        }
    }
}
