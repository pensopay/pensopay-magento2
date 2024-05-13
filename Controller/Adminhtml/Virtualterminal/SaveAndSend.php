<?php

namespace PensoPay\Gateway\Controller\Adminhtml\Virtualterminal;

class SaveAndSend extends Generic
{
    public function execute()
    {
        $this->_createPaymentLink(true);
        return $this->_redirectToTerminal();
    }
}
