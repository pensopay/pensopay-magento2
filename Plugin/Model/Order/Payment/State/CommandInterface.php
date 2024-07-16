<?php

namespace PensoPay\Gateway\Plugin\Model\Order\Payment\State;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\State\CommandInterface as BaseCommandInterface;
use PensoPay\Gateway\Helper\Data;
use PensoPay\Gateway\Model\Ui\Method\AnydayConfigProvider;
use PensoPay\Gateway\Model\Ui\Method\PensoPayConfigProvider;
use PensoPay\Gateway\Model\Ui\Method\ViabillConfigProvider;
use PensoPay\Gateway\Model\Ui\Method\MobilePayConfigProvider;
use PensoPay\Gateway\Model\Ui\Method\ApplePayConfigProvider;
use PensoPay\Gateway\Model\Ui\Method\SwishConfigProvider;
use PensoPay\Gateway\Model\Ui\Method\KlarnaConfigProvider;

class CommandInterface
{
    /**
     * Set pending order status on order place
     * see https://github.com/magento/magento2/issues/5860
     *
     * @todo Refactor this when another option becomes available
     *
     * @param BaseCommandInterface $subject
     * @param \Closure $proceed
     * @param OrderPaymentInterface $payment
     * @param $amount
     * @param OrderInterface $order
     * @return mixed
     */
    public function aroundExecute(BaseCommandInterface $subject, \Closure $proceed, OrderPaymentInterface $payment, $amount, OrderInterface $order)
    {
        $result = $proceed($payment, $amount, $order);

        if (in_array($payment->getMethod(), [
            PensoPayConfigProvider::CODE,
            ViabillConfigProvider::CODE,
            AnydayConfigProvider::CODE,
            ApplePayConfigProvider::CODE,
            MobilePayConfigProvider::CODE,
            SwishConfigProvider::CODE,
            KlarnaConfigProvider::CODE,
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