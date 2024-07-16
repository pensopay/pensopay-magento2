<?php

namespace PensoPay\Gateway\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Sales\Model\Order;

class Callback extends Action
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    protected $order;

    /**
     * @var Order\Email\Sender\OrderSender
     */
    protected $orderSender;

    /** @var \PensoPay\Gateway\Helper\Data $_pensoPayHelper */
    protected $_pensoPayHelper;

    protected $_pensoPaymentFactory;

    /**
     * Class constructor
     * @param \Magento\Framework\App\Action\Context              $context
     * @param \Psr\Log\LoggerInterface                           $logger
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \PensoPay\Gateway\Helper\Data $pensoPayHelper,
        \PensoPay\Gateway\Model\PaymentFactory $paymentFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->order = $order;
        $this->orderSender = $orderSender;
        $this->_pensoPayHelper = $pensoPayHelper;
        $this->_pensoPaymentFactory = $paymentFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Checkout\Model\Session
     */
    protected function _getCheckout()
    {
        return $this->_objectManager->get('Magento\Checkout\Model\Session');
    }

    /**
     * Handle callback from PensoPay
     *
     * @return string
     */
    public function execute()
    {
        $body = $this->getRequest()->getContent();

        try {
            //Fetch private key from config and validate checksum
            $key = $this->_pensoPayHelper->getPrivateKey();
            $checksum = hash_hmac('sha256', $body, $key);
            $submittedChecksum = $this->getRequest()->getHeader('pensopay-signature');

            if ($checksum === $submittedChecksum) {
                $response = json_decode($body);

                //Make sure that payment is accepted
                if ($response->type === 'payment' && $response->event === 'payment.authorized') {
                    /**
                     * Load order by incrementId
                     * @var Order $order
                     */
                    $order = $this->order->loadByIncrementId($response->resource->order_id);

                    if (!$order->getId()) {
                        $this->logger->debug('Failed to load order with id: ' . $response->resource->order_id);
                        return;
                    }

                    //Cancel order if testmode is disabled and this is a test payment
                    $testMode = $this->_pensoPayHelper->getIsTestmode();

                    if (!$testMode && $response->resource->testmode === true) {
                        $this->logger->debug('Order attempted paid with a test card but testmode is disabled.');
                        if (!$order->isCanceled()) {
                            $order->registerCancellation("Order attempted paid with test card")->save();
                        }
                        return;
                    }

//                    Add card metadata
                    $payment = $order->getPayment();
                    if (isset($response->resource->payment_details) && !empty($response->resource->payment_details->last4)) {
                        $payment->setCcType($response->resource->payment_details->brand);
                        $payment->setCcLast4('xxxx-' . $response->resource->payment_details->last4);
                        $payment->setCcExpMonth($response->resource->payment_details->exp_month);
                        $payment->setCcExpYear($response->resource->payment_details->exp_year);

                        $payment->setAdditionalInformation('cc_number', 'xxxx-' . $response->resource->payment_details->last4);
                        $payment->setAdditionalInformation('exp_month', $response->resource->payment_details->exp_month);
                        $payment->setAdditionalInformation('exp_year', $response->resource->payment_details->exp_year);
                        $payment->setAdditionalInformation('cc_type', $response->resource->payment_details->brand);
                        $payment->setAdditionalInformation('bin', $response->resource->payment_details->bin);
                        $payment->setAdditionalInformation('acquirer', $response->resource->payment_details->acquirer);
                        $payment->setAdditionalInformation('is_3d_secure', $response->resource->payment_details->is_3d_secure);
                        $payment->setAdditionalInformation('currency', $response->resource->currency);
                    } else {
                        if (isset($response->resource->payment_details->method)) {
                            $payment->setCcType($response->resource->payment_details->method);
                            $payment->setAdditionalInformation('cc_type', $response->resource->payment_details->method);
                        }
                    }

                    $pensoPayment = $this->_pensoPaymentFactory->create();
                    $pensoPayment->load($order->getIncrementId(), 'order_id');
                    $pensoPayment->importFromRemotePayment(json_decode($body, true));
                    $pensoPayment->save();

                    //Set order to processing
                    $stateProcessing = Order::STATE_PROCESSING;
                    if ($order->getState() !== $stateProcessing) {
                        $order->setState($stateProcessing)
                            ->setStatus($order->getConfig()->getStateDefaultStatus($stateProcessing))
                            ->save();
                    }

                    $this->_pensoPayHelper->setNewOrderStatus($order);
                    $order->save();

                    //Send order email
                    if (!$order->getEmailSent()) {
                        $this->sendOrderConfirmation($order);
                    }
                }
            } else {
                $this->logger->debug('Checksum mismatch');
                return;
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * Add transaction fee as virtual product
     *
     * @param Order $order
     * @param $fee
     */
    private function addTransactionFee(Order $order, $fee)
    {
        try {
            foreach ($order->getAllItems() as $orderItem) {
                if ($orderItem->getSku() === \PensoPay\Gateway\Helper\Data::TRANSACTION_FEE_SKU) {
                    return;
                }
            }

            /** @var \Magento\Sales\Model\Order\Item $item */
            $item = $this->_objectManager->create(\Magento\Sales\Model\Order\Item::class);
            $item->setSku(\PensoPay\Gateway\Helper\Data::TRANSACTION_FEE_SKU);

            //Calculate fee price
            $feeBase = (float)$fee / 100;
            $feeTotal = $order->getStore()->getBaseCurrency()->convert($feeBase, $order->getOrderCurrencyCode());

            $name = $this->_pensoPayHelper->getTransactionFeeLabel();
            $item->setName($name);
            $item->setBaseCost($feeBase);
            $item->setBasePrice($feeBase);
            $item->setBasePriceInclTax($feeBase);
            $item->setBaseOriginalPrice($feeBase);
            $item->setBaseRowTotal($feeBase);
            $item->setBaseRowTotalInclTax($feeBase);
            $item->setCost($feeTotal);
            $item->setPrice($feeTotal);
            $item->setPriceInclTax($feeTotal);
            $item->setOriginalPrice($feeTotal);
            $item->setRowTotal($feeTotal);
            $item->setRowTotalInclTax($feeTotal);
            $item->setProductType(\Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL);
            $item->setIsVirtual(1);
            $item->setQtyOrdered(1);
            $item->setStoreId($order->getStoreId());
            $item->setOrderId($order->getId());

            $order->addItem($item);

            $order = $this->updateTotals($order, $feeBase, $feeTotal);
            $order->save();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * Update order totals after adding transaction fee
     *
     * @param Order $order
     * @param $feeBase
     * @param $feeTotal
     */
    private function updateTotals($order, $feeBase, $feeTotal)
    {
        $order->setBaseGrandTotal($order->getBaseGrandTotal() + $feeBase);
        $order->setBaseSubtotal($order->getBaseSubtotal() + $feeBase);
        $order->setGrandTotal($order->getGrandTotal() + $feeTotal);
        $order->setSubtotal($order->getSubtotal() + $feeTotal);

        return $order;
    }

    /**
     * Send order confirmation email
     *
     * @param Order $order
     */
    private function sendOrderConfirmation($order)
    {
        try {
            $this->orderSender->send($order);
            $order->addStatusHistoryComment(__('Order confirmation email sent to customer'))
                ->setIsCustomerNotified(true)
                ->save();
        } catch (\Exception $e) {
            $order->addStatusHistoryComment(__('Failed to send order confirmation email: %s', $e->getMessage()))
                ->setIsCustomerNotified(false)
                ->save();
        }
    }
}
