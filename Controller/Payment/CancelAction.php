<?php

namespace Pensopay\Gateway\Controller\Payment;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Pensopay\Gateway\Helper\Checkout;

class CancelAction implements ActionInterface
{
    private Checkout $_checkoutHelper;

    protected RedirectFactory $_resultRedirectFactory;

    /**
     * @param Checkout $checkoutHelper
     * @param RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        Checkout        $checkoutHelper,
        RedirectFactory $resultRedirectFactory
    )
    {
        $this->_checkoutHelper = $checkoutHelper;
        $this->_resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * Customer canceled payment on gateway side.
     *
     * @return Redirect
     */
    public function execute()
    {
        $this->_checkoutHelper->cancelCurrentOrder('');
        $this->_checkoutHelper->restoreQuote();

        return $this->_resultRedirectFactory->create()->setPath('checkout/cart', ['_fragment' => 'payment']);
    }
}
