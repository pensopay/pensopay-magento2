<?php

namespace PensoPay\Gateway\Gateway\Http\Client;

class TransactionCapture extends AbstractTransaction
{
    protected function process(array $data)
    {
        return $this->adapter->capture($data);
    }
}