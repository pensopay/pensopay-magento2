<?php

namespace Pensopay\Gateway\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class PaymentMethods implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 'all',
                'label' => __('All available')
            ],
            [
                'value' => 'creditcard',
                'label' => __('All credit cards')
            ],
            [
                'value' => 'specified',
                'label' => __('As specified')
            ],
        ];
    }
}
