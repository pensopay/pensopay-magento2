<?php

namespace Pensopay\Gateway\Controller\Adminhtml\Virtualterminal;

class RefundPayment extends Generic
{
    public function execute()
    {
        return $this->_genericPaymentCallback('refund');
    }
}
