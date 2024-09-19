<?php

namespace Pensopay\Gateway\V2\Response;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;
use Psr\Log\LoggerInterface;

class TransactionIdHandler implements HandlerInterface
{
    protected LoggerInterface $_logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->_logger = $logger;
    }

    /** Handles transaction id */
    public function handle(array $handlingSubject, array $response)
    {
        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = SubjectReader::readPayment($handlingSubject);

        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);

        $responseObject = $response['object'];

        /** @var $payment Payment */
        $payment->setTransactionId($responseObject['id']);
        $payment->setIsTransactionClosed(false);
    }
}
