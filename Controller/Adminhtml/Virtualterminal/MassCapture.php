<?php

namespace PensoPay\Gateway\Controller\Adminhtml\Virtualterminal;

class MassCapture extends Generic
{
    public function execute()
    {
        return $this->_genericMassPaymentAction('capture');
    }
}
