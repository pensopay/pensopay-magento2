<?php

namespace Pensopay\Gateway\V2\Request;

use Magento\Payment\Gateway\Data\Order\OrderAdapter;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

class AuthorizationRequest implements BuilderInterface
{
    private SubjectReader $subjectReader;

    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**  Builds authorization request */
    public function build(array $buildSubject): array
    {
        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $amount = $this->subjectReader->readAmount($buildSubject) * 100;

        /** @var OrderAdapter $order */
        $order = $paymentDO->getOrder();
        $address = $order->getBillingAddress();

        return [
            'INCREMENT_ID' => $order->getOrderIncrementId(),
            'AMOUNT' => $amount, //Get order total in cents
            'ORDER_ID' => $order->getId(),
            'CURRENCY' => $order->getCurrencyCode(),
            'EMAIL' => $address->getEmail(),
            'BILLING_ADDRESS' => $address,
            'SHIPPING_ADDRESS' => $order->getShippingAddress() ?: $address,
            'ITEMS' => $order->getItems()
        ];
    }
}
