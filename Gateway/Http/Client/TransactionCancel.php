<?php

namespace PensoPay\Gateway\Gateway\Http\Client;

class TransactionCancel extends AbstractTransaction
{
    protected function process(array $data)
    {
        return $this->adapter->cancel($data);
    }
}