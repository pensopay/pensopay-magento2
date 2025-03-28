<?php

namespace Pensopay\Gateway\Model;

use Exception;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Sales\Model\OrderRepository;
use Pensopay\Gateway\Helper\Checkout as PensopayCheckoutHelper;
use Pensopay\Gateway\Helper\Data as PensopayDataHelper;
use Pensopay\Gateway\Model\Adapter\PensopayAdapter;

class Payment extends AbstractModel
{
    const PAYMENT_TABLE = 'pensopay_gateway';

    protected PensopayCheckoutHelper $_helper;
    protected PensopayDataHelper $_pensoPayHelper;
    protected OrderRepository $_orderRepository;
    protected SearchCriteriaBuilder $_searchCriteriaBuilder;
    protected PensopayAdapter $_paymentAdapter;

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
        PensopayCheckoutHelper $checkoutHelper,
        PensopayDataHelper     $pensoPayDataHelper,
        OrderRepository        $orderRepository,
        SearchCriteriaBuilder  $searchCriteriaBuilder,
        PensopayAdapter        $paymentAdapter,
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
        return __(ucfirst($this->getState()));
    }

    /**
     * @param array $payment
     */
    public function importFromRemotePayment($payment)
    {
        if (isset($payment['resource'])) { //is callback
            $payment = $payment['resource'];
        }


        if (!$this->_pensoPayHelper->getIsTestmode() && isset($payment['testmode']) && $payment['testmode']) {
            $this->setState(self::STATE_REJECTED);
            return;
        }

        $this->setReferenceId($payment['id']);
        unset($payment['id']); //We don't want to override the object id with the remote id
        $this->addData($payment);
        if (!empty($payment['link'])) {
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
        $this->setPaymentDetails(json_encode($payment['payment_details'] ?? []));
        $this->setHash(md5($this->getReferenceId() . $this->getLink() . $this->getAmount()));
        if (isset($payment['captured'])) {
            $this->setAmountCaptured($payment['captured'] / 100);
        }
        if (isset($payment['refunded'])) {
            $this->setAmountRefunded($payment['refunded'] / 100);
        }
    }

    /**
     * Updates payment data from remote gateway.
     *
     * @throws Exception
     */
    public function updatePaymentRemote()
    {
        if (!$this->getId()) {
            throw new Exception(__('Payment not loaded.'));
        }

        if (!$this->getReferenceId()) {
            throw new Exception(__('Reference id not found.'));
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
