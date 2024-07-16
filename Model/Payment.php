<?php

namespace PensoPay\Gateway\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;
use PensoPay\Gateway\Helper\Checkout as PensoPayCheckoutHelper;
use PensoPay\Gateway\Helper\Data as PensoPayDataHelper;
use PensoPay\Gateway\Model\Adapter\PensoPayAdapter;

class Payment extends AbstractModel
{
    const PAYMENT_TABLE = 'pensopay_payment';

    protected PensoPayCheckoutHelper $_helper;
    protected PensoPayDataHelper $_pensoPayHelper;
    protected OrderRepository $_orderRepository;
    protected SearchCriteriaBuilder $_searchCriteriaBuilder;
    protected PensoPayAdapter $_paymentAdapter;

    const STATE_PENDING = 'pending';
    const STATE_AUTHORIZED = 'authorized';
    const STATE_CAPTURED = 'captured';
    const STATE_REFUNDED = 'refunded';
    const STATE_CANCELED = 'canceled';
    const STATE_REJECTED = 'rejected';

    /**
     * States in which the payment can't be updated anymore
     * Used for cron.
     */
    const FINALIZED_STATES = [
        self::STATE_REJECTED,
        self::STATE_CAPTURED,
        self::STATE_CANCELED
    ];

    public function __construct(
        Context                $context,
        Registry               $registry,
        PensoPayCheckoutHelper $checkoutHelper,
        PensoPayDataHelper     $pensoPayDataHelper,
        OrderRepository        $orderRepository,
        SearchCriteriaBuilder  $searchCriteriaBuilder,
        PensoPayAdapter        $paymentAdapter,
        AbstractResource       $resource = null,
        AbstractDb             $resourceCollection = null,
        array                  $data = []
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_init(ResourceModel\Payment::class);
        $this->_helper = $checkoutHelper;
        $this->_pensoPayHelper = $pensoPayDataHelper;
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_paymentAdapter = $paymentAdapter;
    }

    public function getDisplayStatus()
    {
//        $lastCode = $this->getLastCode();
//
//        $status = '';
//        if ($lastCode == self::STATUS_APPROVED && $this->getLastType() == self::OPERATION_CAPTURE) {
//            $status = __('Captured');
//        } elseif ($lastCode == self::STATUS_APPROVED && $this->getLastType() == self::OPERATION_CANCEL) {
//            $status = __('Cancelled');
//        } elseif ($lastCode == self::STATUS_APPROVED && $this->getLastType() == self::OPERATION_REFUND) {
//            $status = __('Refunded');
//        } elseif (!empty(self::STATUS_CODES[$lastCode])) {
//            $status = self::STATUS_CODES[$lastCode];
//        }
//        return sprintf('%s (%s)', $status, $this->getState());
    }

    /**
     * @param array $payment
     */
    public function importFromRemotePayment($payment)
    {
        if (!$this->_pensoPayHelper->getIsTestmode() && $payment['resource']['testmode']) {
            $this->setState(self::STATE_REJECTED);
            return;
        }

        $this->setReferenceId($payment['id']);
        unset($payment['id']); //We don't want to override the object id with the remote id
        $this->addData($payment);
        if (isset($payment['link']) && !empty($payment['link'])) {
            if (is_array($payment['link'])) {
                $this->setLink($payment['link']['url']);
            } else {
                $this->setLink($payment['link']);
            }
        }

        $this->setAmount($payment['amount'] / 100);
        $this->setCurrencyCode($payment['currency']);
        //penso actions?
//        if (!empty($payment['metadata']) && is_array($payment['metadata'])) {
//            $this->setFraudProbability($payment['metadata']['fraud_suspected'] || $payment['metadata']['fraud_reported'] ? self::FRAUD_PROBABILITY_HIGH : self::FRAUD_PROBABILITY_NONE);
//        }
//        $this->setOperations(json_encode($payment['operations']));
//        $this->setMetadata(json_encode($payment['metadata']));
        $this->setHash(md5($this->getReferenceId() . $this->getLink() . $this->getAmount()));
        $this->setAmountCaptured($payment['captured'] / 100);
        $this->setAmountRefunded($payment['refunded'] / 100);
    }

    /**
     * Updates payment data from remote gateway.
     *
     * @throws \Exception
     */
    public function updatePaymentRemote()
    {
        if (!$this->getId()) {
            throw new \Exception(__('Payment not loaded.'));
        }

        if (!$this->getReferenceId()) {
            throw new \Exception(__('Reference id not found.'));
        }

        $orderIncrement = $this->getOrderId();
        $storeId = null;
        if (!empty($orderIncrement)) {
            $storeId = $this->_pensoPayHelper->getStoreIdForOrderIncrement($orderIncrement);
            if (is_numeric($storeId)) {
                $this->_paymentAdapter->setTransactionStore($storeId);
            }
        }
        $paymentInfo = $this->_paymentAdapter->getPayment($this->getReferenceId());
        $this->importFromRemotePayment($paymentInfo);
        $this->save();
    }

    public function canCapture()
    {
        return $this->getState() === self::STATE_AUTHORIZED && $this->getAmount() > $this->getAmountCaptured();
    }

    public function canCancel()
    {
        return $this->getState() === self::STATE_AUTHORIZED;
    }

    public function canRefund()
    {
        return ($this->getState() === self::STATE_CAPTURED && ($this->getAmount() > $this->getAmountRefunded()));
    }
}
