<?php

namespace Pensopay\Gateway\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Payment extends AbstractDb
{
    protected function _construct()
    {
        $this->_setMainTable(\Pensopay\Gateway\Model\Payment::PAYMENT_TABLE, 'id');
    }
}
