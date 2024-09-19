<?php

namespace Pensopay\Gateway\Controller\Adminhtml\Virtualterminal;

class CapturePayment extends Generic
{
    public function execute()
    {
        return $this->_genericPaymentCallback('capture');
    }
}
