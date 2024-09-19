<?php

namespace Pensopay\Gateway\Cron;

use Exception;
use Pensopay\Gateway\Model\Payment;
use Pensopay\Gateway\Model\ResourceModel\Payment\Collection;
use Psr\Log\LoggerInterface;

class UpdatePayments
{
    protected Collection $_paymentCollection;
    protected LoggerInterface $_logger;

    public function __construct(
        Collection      $paymentCollection,
        LoggerInterface $logger
    )
    {
        $this->_paymentCollection = $paymentCollection;
        $this->_logger = $logger;
    }

    /**
     * @return $this
     */
    public function execute(): self
    {
        $this->_paymentCollection->addFieldToFilter('state', ['nin' => Payment::FINALIZED_STATES]);
        $this->_paymentCollection->addFieldToFilter('reference_id', ['notnull' => true]);
        $this->_paymentCollection->addFieldToFilter('is_virtualterminal', 1);

        /** @var Payment $payment */
        foreach ($this->_paymentCollection as $payment) {
            try {
                $payment->updatePaymentRemote();
            } catch (Exception $e) {
                $this->_logger->error('CRON: Could not update payment remotely. Exception: ' . $e->getMessage());
            }
        }
        return $this;
    }
}
