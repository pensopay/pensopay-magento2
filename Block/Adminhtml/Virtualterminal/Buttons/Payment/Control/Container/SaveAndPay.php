<?php

namespace PensoPay\Gateway\Block\Adminhtml\Virtualterminal\Buttons\Payment\Control\Container;

use PensoPay\Gateway\Model\Payment;

class SaveAndPay extends Generic
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        if ($this->hasPayment()) {
            return [];
        }

        return [
            'label' => __('Pay'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'saveAndPay']],
                'form-role' => 'saveAndPay',
            ]
        ];
    }
}
