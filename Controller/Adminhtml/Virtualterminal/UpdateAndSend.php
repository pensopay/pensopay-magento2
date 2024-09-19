<?php

namespace Pensopay\Gateway\Controller\Adminhtml\Virtualterminal;

class UpdateAndSend extends Generic
{
    public function execute()
    {
        $this->_updatePaymentLink(true);
        return $this->_redirectToTerminal();
    }
}
