<?php

namespace PensoPay\Gateway\Model\ResourceModel\Payment;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use PensoPay\Gateway\Model\Payment;
use PensoPay\Gateway\Model\ResourceModel\Payment as PaymentResource;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Payment::class, PaymentResource::class);
    }
}
