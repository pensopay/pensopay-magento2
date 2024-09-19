<?php

namespace Pensopay\Gateway\Model\ResourceModel\Payment;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Pensopay\Gateway\Model\Payment;
use Pensopay\Gateway\Model\ResourceModel\Payment as PaymentResource;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Payment::class, PaymentResource::class);
    }
}
