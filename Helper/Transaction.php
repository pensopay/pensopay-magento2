<?php

namespace Pensopay\Gateway\Helper;

use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Pensopay\Gateway\Helper\Data as PensopayHelperData;
use Pensopay\Gateway\Model\Adapter\PensopayAdapter;
use Pensopay\Gateway\Model\PaymentFactory;

class Transaction extends AbstractHelper
{

    protected PaymentFactory $_paymentFactory;
    protected PensopayAdapter $_pensoPayAdapter;
    protected PensopayHelperData $_pensoPayHelper;

    public function __construct(
        Context            $context,
        PaymentFactory     $paymentFactory,
        PensopayAdapter    $pensoPayAdapter,
        PensopayHelperData $pensoPayHelper
    )
    {
        parent::__construct($context);
        $this->_paymentFactory = $paymentFactory;
        $this->_pensoPayAdapter = $pensoPayAdapter;
        $this->_pensoPayHelper = $pensoPayHelper;
    }


    /**
     * @throws Exception
     */
    public function getPaymentForOrderId($orderId, $transactionId = false)
    {
        $pensoPayment = $this->_paymentFactory->create();
        $pensoPayment->load($orderId, 'order_id');
        if ($transactionId) { //wants to refresh it
            $storeId = $this->_pensoPayHelper->getStoreIdForOrderIncrement($orderId);
            if (is_numeric($storeId)) {
                $this->_pensoPayAdapter->setTransactionStore($storeId);
            }
            $paymentInfo = $this->_pensoPayAdapter->getPayment($transactionId);
            $pensoPayment->importFromRemotePayment($paymentInfo);
            $pensoPayment->save();
        }
        return $pensoPayment;
    }
}
