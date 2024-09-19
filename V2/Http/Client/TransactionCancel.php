<?php

namespace Pensopay\Gateway\V2\Http\Client;

class TransactionCancel extends AbstractTransaction
{
    protected function process(array $data)
    {
        return $this->adapter->cancel($data);
    }
}
