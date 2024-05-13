<?php

namespace PensoPay\Gateway\Gateway\Http\Client;

class TransactionRefund extends AbstractTransaction
{
    protected function process(array $data)
    {
        return $this->adapter->refund($data);
    }
}