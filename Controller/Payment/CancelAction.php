<?php

namespace Pensopay\Gateway\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Pensopay\Gateway\Helper\Checkout;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\Result\RedirectFactory;

class CancelAction implements ActionInterface
{
    private Checkout $_checkoutHelper;

    protected RedirectFactory $_resultRedirectFactory;

    /**
     * @param Context $context
     * @param Checkout $checkoutHelper
     */
    public function __construct(
        Checkout                      $checkoutHelper,
        RedirectFactory               $resultRedirectFactory
    )
    {
        $this->_checkoutHelper = $checkoutHelper;
        $this->_resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * Customer canceled payment on gateway side.
     *
     * @return void
     */
    public function execute()
    {
        $this->_checkoutHelper->cancelCurrentOrder('');
        $this->_checkoutHelper->restoreQuote();

        return $this->_resultRedirectFactory->create()->setPath('checkout/cart', ['_fragment' => 'payment']);
    }
}
