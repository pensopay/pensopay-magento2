<?php

namespace Pensopay\Gateway\Model\Adminhtml\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Payment\Model\Method\AbstractMethod;

/**
 * Class PaymentAction
 */
class PaymentAction implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => AbstractMethod::ACTION_AUTHORIZE,
                'label' => __('Authorize')
            ]
        ];
    }
}
