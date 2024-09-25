<?php

namespace Pensopay\Gateway\Ui\Component\Virtualterminal\Grid\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Pensopay\Gateway\Helper\Data;
use Pensopay\Gateway\Model\Payment;
use Pensopay\Gateway\Model\PaymentFactory;

class State extends Column
{
    protected PaymentFactory $_paymentFactory;

    protected Data $_pensoPayHelper;

    /**
     * Batch constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface   $context,
        UiComponentFactory $uiComponentFactory,
        PaymentFactory     $paymentFactory,
        Data               $pensoPayHelper,
        array              $components = [],
        array              $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->_paymentFactory = $paymentFactory;
        $this->_pensoPayHelper = $pensoPayHelper;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                /** @var Payment $payment */
                $payment = $this->_paymentFactory->create();
                $payment->addData($item);
                $item[$this->getData('name')] = "<span class=\"{$this->_pensoPayHelper->getStatusColorCode($payment->getState())}\"><span>{$payment->getDisplayStatus()}</span></span>";
            }
        }

        return $dataSource;
    }
}
