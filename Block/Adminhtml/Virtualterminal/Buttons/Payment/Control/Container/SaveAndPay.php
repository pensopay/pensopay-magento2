<?php

namespace Pensopay\Gateway\Block\Adminhtml\Virtualterminal\Buttons\Payment\Control\Container;

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
