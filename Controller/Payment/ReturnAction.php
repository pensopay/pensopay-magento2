<?php

namespace Pensopay\Gateway\Controller\Payment;

use Magento\Checkout\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class ReturnAction implements ActionInterface
{
    protected Session $_session;

    protected EncryptorInterface $_encryptor;

    protected RequestInterface $_request;

    protected RedirectFactory $_resultRedirectFactory;

    protected SearchCriteriaBuilder $_searchCriteriaBuilder;

    protected OrderRepositoryInterface $_orderRepository;

    public function __construct(
        Session                  $session,
        EncryptorInterface       $encryptor,
        RequestInterface         $request,
        RedirectFactory          $resultRedirectFactory,
        SearchCriteriaBuilder    $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository
    )
    {
        $this->_session = $session;
        $this->_encryptor = $encryptor;
        $this->_request = $request;
        $this->_resultRedirectFactory = $resultRedirectFactory;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_orderRepository = $orderRepository;
    }

    /**
     * Redirect to checkout success.
     *
     * If the checkout session no longer holds the order (e.g. the session was lost
     * while the customer was at the gateway), restore it from the encrypted `ori` param.
     *
     * @return Redirect
     */
    public function execute()
    {
        if (!$this->_session->getLastRealOrderId()) {
            $this->restoreSessionFromOrderHash((string)$this->_request->getParam('ori'));
        }

        return $this->_resultRedirectFactory->create()->setPath('checkout/onepage/success');
    }

    /**
     * @param string $orderHash
     * @return void
     */
    private function restoreSessionFromOrderHash(string $orderHash): void
    {
        if ($orderHash === '') {
            return;
        }

        $orderIncrementId = (string)$this->_encryptor->decrypt($orderHash);
        if ($orderIncrementId === '') {
            return;
        }

        $searchCriteria = $this->_searchCriteriaBuilder
            ->addFilter('increment_id', $orderIncrementId)
            ->setPageSize(1)
            ->create();
        $searchResult = $this->_orderRepository->getList($searchCriteria);

        if (!$searchResult->getTotalCount()) {
            return;
        }

        /** @var Order $order */
        $order = $searchResult->getFirstItem();

        if ($order->getIncrementId() === $orderIncrementId) {
            $this->_session->setLastSuccessQuoteId($order->getQuoteId());
            $this->_session->setLastQuoteId($order->getQuoteId());
            $this->_session->setLastRealOrderId($order->getIncrementId());
            $this->_session->setLastOrderId((int)$order->getEntityId());
        }
    }
}
