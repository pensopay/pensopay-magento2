<?php

namespace Pensopay\Gateway\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order as OrderAlias;
use Magento\Sales\Model\Order\Payment;
use Pensopay\Gateway\Helper\Data;
use Pensopay\Gateway\Model\Ui\Method\AnydayConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\ApplePayConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\GooglePayConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\MobilePayConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\PensopayConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\StripeIdealConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\StripeKlarnaConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\SwishConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\ViabillConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\VippsPspConfigProvider;

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

        if (in_array($payment->getMethod(), [
            PensopayConfigProvider::CODE,
            ViabillConfigProvider::CODE,
            AnydayConfigProvider::CODE,
            MobilePayConfigProvider::CODE,
            ApplePayConfigProvider::CODE,
            GooglePayConfigProvider::CODE,
            SwishConfigProvider::CODE,
            StripeKlarnaConfigProvider::CODE,
            StripeIdealConfigProvider::CODE,
            VippsPspConfigProvider::CODE
        ], false)) {
            /** @var OrderAlias $order */
            $this->_pensoPayHelper->setNewOrderStatus($order, true);
            $order->setCanSendNewEmailFlag(false)
                ->setIsCustomerNotified(false)
                ->save();
        }
    }
}
