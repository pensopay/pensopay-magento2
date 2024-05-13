<?php

namespace PensoPay\Gateway\Gateway\Request;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Psr\Log\LoggerInterface;

class CancelRequest implements BuilderInterface
{
    private SubjectReader $subjectReader;

    protected LoggerInterface $_logger;

    /**
     * @param SubjectReader $subjectReader
     * @param LoggerInterface $logger
     */
    public function __construct(SubjectReader $subjectReader, LoggerInterface $logger)
    {
        $this->subjectReader = $subjectReader;
        $this->_logger = $logger;
    }

    /** Builds cancel request */
    public function build(array $buildSubject)
    {
        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();

        ContextHelper::assertOrderPayment($payment);

        $storeId = $paymentDO->getOrder()->getStoreId();

        return [
            'TXN_ID' => $payment->getLastTransId(),
            'STORE_ID' => $storeId
        ];
    }
}
