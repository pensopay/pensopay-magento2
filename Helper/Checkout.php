<?php

namespace Pensopay\Gateway\Helper;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader as ModuleDirReader;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\Config;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use Pensopay\Gateway\Model\Config\Source\CheckoutMethods;

/**
 * Checkout workflow helper
 */
class Checkout extends AbstractHelper
{
    const XML_PATH_CHECKOUT_METHOD = 'payment/pensopay_gateway/checkout_method';
    const XML_PATH_CHECKOUT_CARDLOGOS = 'payment/pensopay_gateway/cardlogos';
    const XML_PATH_CHECKOUT_CARDLOGOS_SIZE = 'payment/pensopay_gateway/cardlogos_size';
    const IS_VIRTUAL_TERMINAL = 'is_virtualterminal';

    protected CheckoutSession $_checkoutSession;
    protected PaymentHelper $_paymentHelper;
    protected Config $_paymentConfig;
    protected ModuleDirReader $_moduleDirReader;
    protected ?string $_logoBasePath = null;

    public function __construct(
        Context         $context,
        CheckoutSession $session,
        PaymentHelper   $paymentHelper,
        Config          $paymentConfig,
        ModuleDirReader $moduleDirReader
    )
    {
        parent::__construct($context);

        $this->_checkoutSession = $session;
        $this->_paymentHelper = $paymentHelper;
        $this->_paymentConfig = $paymentConfig;
        $this->_moduleDirReader = $moduleDirReader;
    }

    /**
     * Cancel last placed order with specified comment message
     */
    public function cancelCurrentOrder(string $comment): bool
    {
        /** @var Order $order */
        $order = $this->_checkoutSession->getLastRealOrder();
        if ($order->getId() && !$order->isCanceled()) {
            $order->registerCancellation($comment)->save();
            return true;
        }
        return false;
    }

    public function getCheckoutSession(): CheckoutSession
    {
        return $this->_checkoutSession;
    }

    public function restoreQuote(): bool
    {
        return $this->_checkoutSession->restoreQuote();
    }

    public function getPensopayLogos(): array
    {
        $logos = $this->scopeConfig->getValue(self::XML_PATH_CHECKOUT_CARDLOGOS, ScopeInterface::SCOPE_STORE);
        if (!empty($logos)) {
            return explode(',', $logos);
        }
        return [];
    }

    public function getLogoSize(): string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_CHECKOUT_CARDLOGOS_SIZE, ScopeInterface::SCOPE_STORE) ?? '';
    }

    public function getLogoExtension(string $suffix): string
    {
        if ($this->_logoBasePath === null) {
            $this->_logoBasePath = $this->_moduleDirReader->getModuleDir(
                Dir::MODULE_VIEW_DIR,
                'Pensopay_Gateway'
            ) . '/base/web/images/pensopaypayment/';
        }
        return file_exists($this->_logoBasePath . $suffix . '.svg') ? 'svg' : 'png';
    }

    public function getSecondaryPaymentLogos(): array
    {
        $methods = [];
        foreach ($this->scopeConfig->getValue('payment', ScopeInterface::SCOPE_STORE, null) as $code => $data) {
            if (isset($data['active'], $data['model'], $data['cardlogo_enable']) && (bool)$data['active'] && (bool)$data['cardlogo_enable']) {
                $size = $this->scopeConfig->getValue("payment/{$code}/cardlogo_size", ScopeInterface::SCOPE_STORE);
                $arr = explode('pensopay_gateway_', $code);
                $methods[array_pop($arr)] = $size;
            }
        }
        return $methods;
    }
}
