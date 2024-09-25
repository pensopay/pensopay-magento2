<?php

namespace Pensopay\Gateway\Controller\Payment;

use Exception;
use Magento\Catalog\Model\Product\Type;
use Magento\Checkout\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\OrderRepository;
use Pensopay\Gateway\Helper\Data;
use Pensopay\Gateway\Model\Payment;
use Pensopay\Gateway\Model\PaymentFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Raw;

class Callback implements ActionInterface
{
    protected LoggerInterface $_logger;

    protected ScopeConfigInterface $_scopeConfig;

    protected OrderRepository $_orderRepository;

    protected OrderSender $_orderSender;

    protected Data $_pensoPayHelper;

    protected PaymentFactory $_pensoPaymentFactory;

    protected Session $_checkoutSession;

    protected SearchCriteriaBuilder $_searchCriteriaBuilder;

    protected RequestInterface $_request;

    protected Raw $_resultRaw;

    public function __construct(
        LoggerInterface       $logger,
        ScopeConfigInterface  $scopeConfig,
        OrderSender           $orderSender,
        Data                  $pensoPayHelper,
        PaymentFactory        $paymentFactory,
        Session               $checkoutSession,
        OrderRepository       $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface      $request,
        Raw                   $resultRaw
    )
    {
        $this->_scopeConfig = $scopeConfig;
        $this->_logger = $logger;
        $this->_orderSender = $orderSender;
        $this->_pensoPayHelper = $pensoPayHelper;
        $this->_pensoPaymentFactory = $paymentFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_request = $request;
        $this->_resultRaw = $resultRaw;
    }

    /**
     * @return Session
     */
    protected function _getCheckout()
    {
        return $this->_checkoutSession;
    }

    /**
     * Handle callback from pensopay
     *
     * @return string
     */
    public function execute()
    {
        $body = $this->_request->getContent();

        try {
            //Fetch private key from config and validate checksum
            $key = $this->_pensoPayHelper->getPrivateKey();
            $checksum = hash_hmac('sha256', $body, $key);
            $submittedChecksum = $this->_request->getHeader('pensopay-signature');

            if ($checksum === $submittedChecksum) {
                $response = json_decode($body);

                //Make sure that payment is accepted - right now we're ignoring other events
                if ($response->type === 'payment' && $response->event === 'payment.authorized') {
                    $searchCriteria = $this->_searchCriteriaBuilder->addFilter('increment_id', $response->resource->order_id)->create();
                    $searchResult = $this->_orderRepository->getList($searchCriteria);

                    if (!$searchResult->getTotalCount()) {
                        $this->_logger->debug('Failed to load order with id: ' . $response->resource->order_id);
                        return $this->_resultRaw->setHttpResponseCode(400);
                    }

                    $order = $searchResult->getFirstItem();

                    //Cancel order if testmode is disabled and this is a test payment
                    $testMode = $this->_pensoPayHelper->getIsTestmode();

                    if (!$testMode && $response->resource->testmode === true) {
                        $this->_logger->debug('Order attempted paid with a test card but testmode is disabled.');
                        if (!$order->isCanceled()) {
                            $order->registerCancellation("Order attempted paid with test card")->save();
                        }
                        return $this->_resultRaw->setHttpResponseCode(400);
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

                    //Add transaction fee if set
//                    if (false && $response->fee > 0) {
//                        $fee = $response->fee / 100;
//                        $currentFee = $order->getData('card_surcharge');
//                        $calculatedFee = $fee;
//                        if ($currentFee > 0) {
//                            $order->setData('card_surcharge', $fee);
//                            $order->setData('base_card_surcharge', $fee);
//                            $calculatedFee = -$currentFee + $fee;
//                        } else {
//                            $order->setData('card_surcharge', $fee);
//                            $order->setData('base_card_surcharge', $fee);
//                        }
//
//                        $order->setGrandTotal($order->getGrandTotal() + $calculatedFee);
//                        $order->setBaseGrandTotal($order->getBaseGrandTotal() + $calculatedFee);
//
//                        $quoteId = $order->getQuoteId();
//                        if ($quoteId) {
//                            /**
//                             * Not business critical, don't want to stop
//                             * Basically adds support for most order editors.
//                             */
//                            try {
//                                $quote = $this->_quoteRepository->get($quoteId);
//                                if ($quote->getId()) {
//                                    $quote->setData('card_surcharge', $fee);
//                                    $quote->setData('base_card_surcharge', $fee);
//                                    $quote->setGrandTotal($quote->getGrandTotal() + $calculatedFee);
//                                    $quote->setBaseGrandTotal($quote->getBaseGrandTotal() + $calculatedFee);
//                                    $this->_quoteRepository->save($quote);
//                                }
//                            } catch (\Exception $e) {}
//                        }
//                    }

                    /** @var Payment $pensoPayment */
                    $pensoPayment = $this->_pensoPaymentFactory->create();
                    $pensoPayment->load($order->getIncrementId(), 'order_id');
                    $pensoPayment->importFromRemotePayment(json_decode($body, true));
                    $pensoPayment->save();

                    $this->_pensoPayHelper->setNewOrderStatus($order);

                    $this->_orderRepository->save($order);

                    //Send order email
                    if (!$order->getEmailSent()) {
                        $this->sendOrderConfirmation($order);
                    }
                }
            } else {
                $this->_logger->debug('Checksum mismatch');
                return $this->_resultRaw->setHttpResponseCode(400);
            }
        } catch (Exception $e) {
            $this->_logger->critical($e->getMessage());
        }

        return $this->_resultRaw;
    }

    /**
     * Send order confirmation email
     *
     * @param Order $order
     */
    private function sendOrderConfirmation($order)
    {
        try {
            $this->_orderSender->send($order);
            $order->addStatusHistoryComment(__('Order confirmation email sent to customer'))
                ->setIsCustomerNotified(true);
        } catch (Exception $e) {
            $order->addStatusHistoryComment(__('Failed to send order confirmation email: %s', $e->getMessage()))
                ->setIsCustomerNotified(false);
        }
        $this->_orderRepository->save($order);
    }
}
