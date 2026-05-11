<?php

namespace Pensopay\Gateway\Plugin\Model\Order\Payment\State;

use Closure;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\State\CommandInterface as BaseCommandInterface;
use Pensopay\Gateway\Helper\Data;

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

        if (in_array($payment->getMethod(), Data::PENSOPAY_METHOD_CODES, false)) {
            $orderStatus = Order::STATE_NEW;
            if ($orderStatus && $order->getState() === Order::STATE_PROCESSING) {
                $order->setState($orderStatus)
                    ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_NEW));
            }
        }

        return $result;
    }
}
