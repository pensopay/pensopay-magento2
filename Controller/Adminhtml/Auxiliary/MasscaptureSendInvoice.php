<?php

namespace Pensopay\Gateway\Controller\Adminhtml\Auxiliary;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Controller\Adminhtml\Order as OrderController;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\OrderRepository;

class MasscaptureSendInvoice extends Action
{
    protected OrderRepository $_orderRepository;

    protected Transaction $_transaction;

    protected InvoiceSender $_invoiceSender;

    public function __construct(
        Context         $context,
        OrderRepository $orderRepository,
        Transaction     $transaction,
        InvoiceSender   $invoiceSender
    )
    {
        parent::__construct($context);
        $this->_orderRepository = $orderRepository;
        $this->_transaction = $transaction;
        $this->_invoiceSender = $invoiceSender;
    }

    public function execute()
    {
        $orderIds = $this->getRequest()->getParam('selected', array());
        $success = 0;
        foreach ($orderIds as $orderId) {
            try {
                /** @var Order $order */
                $order = $this->_orderRepository->get($orderId);

                $method = $order->getPayment()->getMethod();
                if (!str_contains($method, 'pensopay_gateway')) {
                    $this->messageManager->addErrorMessage(__('%1 Order was not placed using pensopay', $order->getIncrementId()));
                    continue;
                }

                if (!$order->canInvoice()) {
                    $this->messageManager->addErrorMessage(__('Could not create invoice for %1', $order->getIncrementId()));
                    continue;
                }

                /** @var Invoice $invoice */
                $invoice = $order->prepareInvoice();

                if (!$invoice->getTotalQty()) {
                    $this->messageManager->addErrorMessage(__('Cannot create an invoice without products for %1.', $order->getIncrementId()));
                    continue;
                }

                $invoice->register();
                $invoice->capture();
                $order->setState(Order::STATE_PROCESSING)->setStatus(Order::STATE_PROCESSING);
                $this->_transaction->addObject($invoice)->addObject($order)->save();
                $success++;

                // The invoice is persisted; an email failure must not roll back the capture.
                try {
                    $this->_invoiceSender->send($invoice);
                } catch (Exception $e) {
                    $this->messageManager->addWarningMessage(__('Order %1 was captured but the invoice email could not be sent: %2', $order->getIncrementId(), $e->getMessage()));
                }
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        if ($success) {
            $this->messageManager->addSuccessMessage(__('%1 orders invoiced, captured and invoice email sent', $success));
        }
        return $this->_redirect('sales/order/index');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(OrderController::ADMIN_RESOURCE);
    }
}
