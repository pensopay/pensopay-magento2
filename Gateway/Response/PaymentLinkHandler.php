<?php

namespace PensoPay\Gateway\Gateway\Response;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Psr\Log\LoggerInterface;

class PaymentLinkHandler implements HandlerInterface
{
    const PAYMENT_LINK = 'paymentLink';

    protected LoggerInterface $_logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->_logger = $logger;
    }

    /** Handles payment link */
    public function handle(array $handlingSubject, array $response)
    {
        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = SubjectReader::readPayment($handlingSubject);

        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);

        $responseObject = $response['object'];

        //Save payment link
        /** @var $payment \Magento\Sales\Model\Order\Payment */
        $payment->setAdditionalInformation(self::PAYMENT_LINK, $responseObject['link']);
    }
}
