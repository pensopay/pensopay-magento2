<?php

namespace Pensopay\Gateway\V2\Http\Client;

class TransactionRefund extends AbstractTransaction
{
    protected function process(array $data)
    {
        return $this->adapter->refund($data);
    }
}
