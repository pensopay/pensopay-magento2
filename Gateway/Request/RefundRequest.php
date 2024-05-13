<?php

namespace PensoPay\Gateway\Gateway\Request;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Psr\Log\LoggerInterface;

class RefundRequest implements BuilderInterface
{
    private SubjectReader $subjectReader;
    protected LoggerInterface $_logger;
    public function __construct(SubjectReader $subjectReader, LoggerInterface $logger)
    {
        $this->subjectReader = $subjectReader;
        $this->_logger = $logger;
    }

    /** Builds refund request */
    public function build(array $buildSubject)
    {
        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        $payment = $paymentDO->getPayment();
        $amount = $this->subjectReader->readAmount($buildSubject) * 100;

        ContextHelper::assertOrderPayment($payment);

        $storeId = $paymentDO->getOrder()->getStoreId();

        return [
            'TXN_ID' => $payment->getLastTransId(),
            'AMOUNT' => $amount,
            'STORE_ID' => $storeId
        ];
    }
}
