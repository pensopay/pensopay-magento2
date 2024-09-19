<?php

namespace Pensopay\Gateway\Block\Adminhtml\Virtualterminal\Fieldset;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Context;
use Pensopay\Gateway\Helper\Data;
use Pensopay\Gateway\Model\Payment;
use Pensopay\Gateway\Model\PaymentFactory;

class Transactionlog extends AbstractBlock
{
    protected PaymentFactory $_paymentFactory;

    protected Data $_pensoPayHelper;

    public function __construct(
        Context                                $context,
        PaymentFactory $paymentFactory,
        Data          $pensoPayHelper,
        array                                  $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_paymentFactory = $paymentFactory;
        $this->_pensoPayHelper = $pensoPayHelper;
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        $paymentId = $this->_request->getParam('id');
        if ($paymentId) {
            /** @var Payment $payment */
            $payment = $this->_paymentFactory->create();
            $payment->load($paymentId);
            if ($payment->getId()) {
                $value = $payment->getOperations();
                $operationsArray = json_decode($value, true);

                if (!empty($operationsArray)) {
                    $html = '<table class="operations"><thead>';
                    $html .= sprintf(
                        '<tr><th>%s</th><th>%s</th><th>%s</th></tr>',
                        __('Type'),
                        __('Result'),
                        __('Time')
                    );
                    $html .= '</thead><tbody>';
                    foreach ($operationsArray as $operation) {
                        $html .= sprintf(
                            '<tr class="%s"><td>%s</td><td>%s: %s</td><td>%s</td></tr>',
                            $this->_pensoPayHelper->getStatusColorCode($operation['qp_status_code']),
                            $operation['type'],
                            $operation['qp_status_code'],
                            $operation['qp_status_msg'],
                            strftime('%d-%m-%Y %H:%M:%S', strtotime($operation['created_at']))
                        );
                    }
                    $html .= '</tbody></table>';
                    return $html;
                }
            }
        }
        return parent::_toHtml();
    }
}
