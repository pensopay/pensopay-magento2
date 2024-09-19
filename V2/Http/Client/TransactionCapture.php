<?php

namespace Pensopay\Gateway\V2\Http\Client;

class TransactionCapture extends AbstractTransaction
{
    protected function process(array $data)
    {
        return $this->adapter->capture($data);
    }
}
