<?php

namespace Pensopay\Gateway\Plugin\Model\Order\Payment\State;

use Closure;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\State\CommandInterface as BaseCommandInterface;
use Pensopay\Gateway\Model\Ui\Method\AnydayConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\ApplePayConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\GooglePayConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\KlarnaConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\MobilePayConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\PensopayConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\StripeIdealConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\StripeKlarnaConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\SwishConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\ViabillConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\VippsPspConfigProvider;

class CommandInterface
{
    /**
     * Set pending order status on order place
     * see https://github.com/magento/magento2/issues/5860
     *
     * @param BaseCommandInterface $subject
     * @param Closure $proceed
     * @param OrderPaymentInterface $payment
     * @param $amount
     * @param OrderInterface $order
     * @return mixed
     * @todo Refactor this when another option becomes available
     *
     */
    public function aroundExecute(BaseCommandInterface $subject, Closure $proceed, OrderPaymentInterface $payment, $amount, OrderInterface $order)
    {
        $result = $proceed($payment, $amount, $order);

        if (in_array($payment->getMethod(), [
            PensopayConfigProvider::CODE,
            ViabillConfigProvider::CODE,
            AnydayConfigProvider::CODE,
            ApplePayConfigProvider::CODE,
            GooglePayConfigProvider::CODE,
            MobilePayConfigProvider::CODE,
            SwishConfigProvider::CODE,
            KlarnaConfigProvider::CODE,
            StripeKlarnaConfigProvider::CODE,
            StripeIdealConfigProvider::CODE,
            VippsPspConfigProvider::CODE
        ], false)) {
            $orderStatus = Order::STATE_NEW;
            if ($orderStatus && $order->getState() === Order::STATE_PROCESSING) {
                $order->setState($orderStatus)
                    ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_NEW));
            }
        }

        return $result;
    }
}
