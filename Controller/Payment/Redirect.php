<?php

namespace Pensopay\Gateway\Controller\Payment;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;
use Pensopay\Gateway\Helper\Checkout as PensopayCheckoutHelper;
use Pensopay\Gateway\V2\Response\PaymentLinkHandler;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;

class Redirect implements ActionInterface
{
    protected LoggerInterface $_logger;

    protected PensopayCheckoutHelper $_pensopayCheckoutHelper;

    protected StoreManagerInterface $_storeManager;

    protected RedirectFactory $_resultRedirectFactory;

    /**
     * Class constructor
     * @param Context $context
     * @param LoggerInterface $logger
     * @param PensopayCheckoutHelper $pensopayCheckoutHelper
     */
    public function __construct(
        LoggerInterface        $logger,
        PensopayCheckoutHelper $pensopayCheckoutHelper,
        RedirectFactory        $resultRedirectFactory
    )
    {
        $this->_logger = $logger;
        $this->_pensopayCheckoutHelper = $pensopayCheckoutHelper;
        $this->_resultRedirectFactory = $resultRedirectFactory;
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

            return $this->_resultRedirectFactory->create()->setPath($paymentLink);
        } catch (Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong, please try again later'));
            $this->_logger->critical($e);
            $this->_pensopayCheckoutHelper->getCheckoutSession()->restoreQuote();
            return $this->_resultRedirectFactory->create()->setPath('checkout/cart');
        }
    }
}
