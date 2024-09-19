<?php

namespace Pensopay\Gateway\Controller\Payment;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;
use Pensopay\Gateway\Helper\Checkout as PensopayCheckoutHelper;
use Pensopay\Gateway\V2\Response\PaymentLinkHandler;
use Psr\Log\LoggerInterface;

class Redirect extends Action
{
    protected LoggerInterface $_logger;

    protected PensopayCheckoutHelper $_pensopayCheckoutHelper;

    protected StoreManagerInterface $_storeManager;

    /**
     * Class constructor
     * @param Context $context
     * @param LoggerInterface $logger
     * @param PensopayCheckoutHelper $pensopayCheckoutHelper
     */
    public function __construct(
        Context                $context,
        LoggerInterface        $logger,
        PensopayCheckoutHelper $pensopayCheckoutHelper
    )
    {
        $this->_logger = $logger;
        $this->_pensopayCheckoutHelper = $pensopayCheckoutHelper;
        parent::__construct($context);
    }

    /**
     * Redirect to to pensopay
     *
     * @return string
     */
    public function execute()
    {
        try {
            $order = $this->_pensopayCheckoutHelper->getCheckoutSession()->getLastRealOrder();
            $paymentLink = $order->getPayment()->getAdditionalInformation(PaymentLinkHandler::PAYMENT_LINK);

            return $this->_redirect($paymentLink);
        } catch (Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong, please try again later'));
            $this->_logger->critical($e);
            $this->_getCheckout()->restoreQuote();
            $this->_redirect('checkout/cart');
        }
    }
}
