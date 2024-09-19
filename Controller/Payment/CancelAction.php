<?php

namespace Pensopay\Gateway\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Pensopay\Gateway\Helper\Checkout;

class CancelAction extends Action
{
    private Checkout $_checkoutHelper;

    /**
     * @param Context $context
     * @param Checkout $checkoutHelper
     */
    public function __construct(
        Context  $context,
        Checkout $checkoutHelper
    )
    {
        parent::__construct($context);
        $this->_checkoutHelper = $checkoutHelper;
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

        $this->_redirect('checkout', ['_fragment' => 'payment']);
    }
}
