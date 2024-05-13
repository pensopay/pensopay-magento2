<?php

namespace PensoPay\Gateway\Controller\Adminhtml\Virtualterminal;

class MassRefund extends Generic
{
    public function execute()
    {
        return $this->_genericMassPaymentAction('refund');
    }
}
