<?php

namespace Pensopay\Gateway\Block\Adminhtml\Virtualterminal\Buttons\Payment\Control\Container;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Pensopay\Gateway\Model\Payment;
use Pensopay\Gateway\Model\PaymentFactory;

class Generic implements ButtonProviderInterface
{
    protected Context $context;

    protected Registry $registry;

    protected PaymentFactory $_paymentFactory;

    protected Payment $_payment;

    public function __construct(
        Context                                $context,
        PaymentFactory $_paymentFactory
    )
    {
        $this->context = $context;
        $this->_paymentFactory = $_paymentFactory;
    }

    /**
     * Generate url by route and parameters
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrl($route, $params);
    }

    public function hasPayment()
    {
        return $this->getPayment() !== null && $this->getPayment()->getId();
    }

    public function getPayment()
    {
        if (!isset($this->_payment) || !$this->_payment->getId()) {
            $this->_payment = $this->_paymentFactory->create();
            $id = $this->context->getRequestParam('id');
            if ($id) {
                $this->_payment = $this->_paymentFactory->create();
                $this->_payment->load($id);
            }
        }
        return $this->_payment;
    }

    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        return [];
    }
}
