<?php

namespace PensoPay\Gateway\Block\Adminhtml\Virtualterminal\Buttons\Payment\Control\Container;

use PensoPay\Gateway\Model\Payment;

class UpdateAndPay extends Generic
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        if ($this->hasPayment() && $this->getPayment()->getState() === Payment::STATE_INITIAL) {
            return [
                'label' => __('Update and Pay'),
                'class' => 'save primary',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'updateAndPay']],
                    'form-role' => 'updateAndPay',
                ]
            ];
        }
        return [];
    }
}
