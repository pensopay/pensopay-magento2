<?php

namespace PensoPay\Gateway\Gateway\Http\Client;

class TransactionAuthorize extends AbstractTransaction
{
    /**
     * @inheritdoc
     */
    protected function process(array $data)
    {
        return $this->adapter->authorizeAndCreatePaymentLink($data);
    }
}