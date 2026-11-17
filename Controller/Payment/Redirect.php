<?php

namespace Pensopay\Gateway\Controller\Payment;

use Exception;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\Result\Redirect as RedirectResult;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Pensopay\Gateway\Helper\Checkout as PensopayCheckoutHelper;
use Pensopay\Gateway\V2\Response\PaymentLinkHandler;
use Psr\Log\LoggerInterface;

class Redirect implements ActionInterface
{
    protected LoggerInterface $_logger;

    protected PensopayCheckoutHelper $_pensopayCheckoutHelper;

    protected RedirectFactory $_resultRedirectFactory;

    protected ManagerInterface $_messageManager;

    /**
     * @param LoggerInterface $logger
     * @param PensopayCheckoutHelper $pensopayCheckoutHelper
     * @param RedirectFactory $resultRedirectFactory
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        LoggerInterface        $logger,
        PensopayCheckoutHelper $pensopayCheckoutHelper,
        RedirectFactory        $resultRedirectFactory,
        ManagerInterface       $messageManager
    )
    {
        $this->_logger = $logger;
        $this->_pensopayCheckoutHelper = $pensopayCheckoutHelper;
        $this->_resultRedirectFactory = $resultRedirectFactory;
        $this->_messageManager = $messageManager;
    }

    /**
     * Redirect to pensopay
     *
     * @return RedirectResult
     */
    public function execute()
    {
        try {
            $order = $this->_pensopayCheckoutHelper->getCheckoutSession()->getLastRealOrder();
            $paymentLink = $order->getPayment()->getAdditionalInformation(PaymentLinkHandler::PAYMENT_LINK);

            return $this->_resultRedirectFactory->create()->setUrl($paymentLink);
        } catch (Exception $e) {
            $this->_messageManager->addExceptionMessage($e, __('Something went wrong, please try again later'));
            $this->_logger->critical($e);
            $this->_pensopayCheckoutHelper->getCheckoutSession()->restoreQuote();
            return $this->_resultRedirectFactory->create()->setPath('checkout/cart');
        }
    }
}
