<?php

namespace PensoPay\Gateway\Controller\Adminhtml\Virtualterminal;

class MassCancel extends Generic
{
    public function execute()
    {
        return $this->_genericMassPaymentAction('cancel');
    }
}
