<?php

namespace PensoPay\Gateway\Ui\Component\Virtualterminal\Grid\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class State extends Column
{
    /** @var \PensoPay\Gateway\Model\PaymentFactory $_paymentFactory */
    protected $_paymentFactory;

    /** @var \PensoPay\Gateway\Helper\Data $_pensoPayHelper */
    protected $_pensoPayHelper;

    /**
     * Batch constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \PensoPay\Gateway\Model\PaymentFactory $paymentFactory,
        \PensoPay\Gateway\Helper\Data $pensoPayHelper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->_paymentFactory = $paymentFactory;
        $this->_pensoPayHelper = $pensoPayHelper;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                /** @var \PensoPay\Gateway\Model\Payment $payment */
                $payment = $this->_paymentFactory->create();
                $payment->addData($item);
                $item[$this->getData('name')] = "<span class=\"{$this->_pensoPayHelper->getStatusColorCode($payment->getLastCode())}\"><span>{$payment->getDisplayStatus()}</span></span>";
            }
        }

        return $dataSource;
    }
}
