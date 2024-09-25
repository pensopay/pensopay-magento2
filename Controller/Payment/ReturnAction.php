<?php

namespace Pensopay\Gateway\Controller\Payment;

use Magento\Checkout\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Sales\Model\Order;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;

class ReturnAction implements ActionInterface
{
    protected Session $_session;

    protected EncryptorInterface $_encryptor;

    protected RequestInterface $_request;

    protected RedirectFactory $_resultRedirectFactory;

    protected SearchCriteriaBuilder $_searchCriteriaBuilder;

    public function __construct(
        Session            $session,
        EncryptorInterface $encryptor,
        RequestInterface   $request,
        RedirectFactory    $resultRedirectFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder
    )
    {
        $this->_session = $session;
        $this->_encryptor = $encryptor;
        $this->_request = $request;
        $this->_resultRedirectFactory = $resultRedirectFactory;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Redirect to to checkout success
     *
     * @return void
     */
    public function execute()
    {
        $lastRealOrderId = $this->_session->getLastRealOrderId();

        if (!$lastRealOrderId) {
            $orderHash = $this->_request->getParam('ori');
            if (!empty($orderHash)) {
                $orderIncrementId = $this->_encryptor->decrypt($orderHash);

                $searchCriteria = $this->_searchCriteriaBuilder->addFilter('increment_id', $orderIncrementId)->create();
                $searchResult = $this->_orderRepository->getList($searchCriteria);

                if ($searchResult->getTotalCount()) {
                    /** @var Order $order */
                    $order = $searchResult->getFirstItem();

                    if ($order->getIncrementId() === $orderIncrementId) {
                        $this->_session->setLastSuccessQuoteId($order->getQuoteId());
                        $this->_session->setLastQuoteId($order->getQuoteId());
                        $this->_session->setLastRealOrderId($order->getIncrementId());
                        $this->_session->setLastOrderId($order->getIncrementId());
                    }
                }
            }
        }
        return $this->_resultRedirectFactory->create()->setPath('checkout/onepage/success');
    }
}
