<?php

namespace Pensopay\Gateway\Helper;

use Magento\Backend\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Pensopay\Gateway\Model\Payment as PensopayPayment;

class Data extends AbstractHelper
{
    const TESTMODE_XML_PATH = 'payment/pensopay_gateway/testmode';
    const TRANSACTION_FEE_LABEL_XML_PATH = 'payment/pensopay_gateway/transaction_fee_label';
    const API_KEY_XML_PATH = 'payment/pensopay_gateway/api_key';
    const PRIVATE_KEY_XML_PATH = 'payment/pensopay_gateway/private_key';
    const AUTOCAPTURE_XML_PATH = 'payment/pensopay_gateway/autocapture';
    const NEW_ORDER_STATUS_XML_PATH = 'payment/pensopay_gateway/new_order_status';
    const PAYMENT_METHODS_XML_PATH = 'payment/pensopay_gateway/payment_methods';
    const PAYMENT_METHODS_SPECIFIED_XML_PATH = 'payment/pensopay_gateway/payment_methods_specified';

    protected Session $_backendSession;
    protected TransportBuilder $_transportBuilder;
    protected OrderRepository $_orderRepository;
    protected SearchCriteriaBuilder $_searchCriteriaBuilder;

    public function __construct(
        Context               $context,
        Session               $backendSession,
        TransportBuilder      $transportBuilder,
        OrderRepository       $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    )
    {
        parent::__construct($context);
        $this->_backendSession = $backendSession;
        $this->_transportBuilder = $transportBuilder;
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function getStoreIdForOrderIncrement($orderIncrement): ?int
    {
        $searchCriteria = $this->_searchCriteriaBuilder->addFilter('increment_id', $orderIncrement)->create();
        $orderList = $this->_orderRepository->getList($searchCriteria)->getItems();
        if (count($orderList)) {
            /** @var Order $order */
            foreach ($orderList as $order) {
                return $order->getStoreId();
            }
        }
        return null;
    }

    public function getTransactionFeeLabel()
    {
        return $this->scopeConfig->getValue(self::TRANSACTION_FEE_LABEL_XML_PATH, ScopeInterface::SCOPE_STORE);
    }

    public function getBackendSession(): Session
    {
        return $this->_backendSession;
    }

    public function getStatusColorCode($value): string
    {
        switch ($value) {
            case PensopayPayment::STATE_PENDING:
                $colorClass = 'grid-severity-minor';
                break;
            case PensopayPayment::STATE_REJECTED:
            case PensopayPayment::STATE_CANCELED:
                $colorClass = 'grid-severity-major';
                break;
            case PensopayPayment::STATE_AUTHORIZED:
            case PensopayPayment::STATE_CAPTURED:
            case PensopayPayment::STATE_REFUNDED:
            default:
                $colorClass = 'grid-severity-notice';
        }
        return $colorClass;
    }

    public function sendEmail($email, $name, $amount, $currency, $link): bool
    {
        $vars = [
            'currency' => $currency,
            'amount' => $amount,
            'link' => $link
        ];

        $senderName = $this->scopeConfig->getValue('trans_email/ident_sales/name');
        $senderEmail = $this->scopeConfig->getValue('trans_email/ident_sales/email');

        $postObject = new DataObject();
        $postObject->setData($vars);

        $sender = [
            'name' => $senderName,
            'email' => $senderEmail,
        ];

        $transport = $this->_transportBuilder->setTemplateIdentifier('pensopay_gateway_paymentlink_email')
            ->setTemplateOptions([
                'area' => Area::AREA_FRONTEND,
                'store' => Store::DEFAULT_STORE_ID
            ])
            ->setTemplateVars($vars)
            ->setFrom($sender)
            ->addTo($email, $name)
            ->setReplyTo($senderEmail)
            ->getTransport();
        $transport->sendMessage();
        return true;
    }

    public function setNewOrderStatus(OrderInterface $order, $beforePayment = false): self
    {
        $status = $beforePayment ? $order->getPayment()->getMethodInstance()->getConfigData('order_status') : $this->getNewOrderStatus();

        $states = [
            Order::STATE_NEW,
            Order::STATE_PROCESSING,
            Order::STATE_COMPLETE,
            Order::STATE_CLOSED,
            Order::STATE_CANCELED,
            Order::STATE_HOLDED
        ];

        $state = false;
        foreach ($states as $_state) {
            $stateStatuses = $order->getConfig()->getStateStatuses($_state);
            if (array_key_exists($status, $stateStatuses)) {
                $state = $_state;
                break;
            }
        }

        if ($state) {
            $order->setState($state)
                ->setStatus($status);
        }
        return $this;
    }

    public function getIsTestmode(): bool
    {
        return $this->scopeConfig->isSetFlag(self::TESTMODE_XML_PATH, ScopeInterface::SCOPE_STORE);
    }

    public function getApiKey($storeId = null): string
    {
        return $this->scopeConfig->getValue(self::API_KEY_XML_PATH, ScopeInterface::SCOPE_STORE, $storeId) ?? '';
    }

    public function getPrivateKey($storeId = null): string
    {
        return $this->scopeConfig->getValue(self::PRIVATE_KEY_XML_PATH, ScopeInterface::SCOPE_STORE, $storeId) ?? '';
    }

    public function getIsAutocapture(): bool
    {
        return $this->scopeConfig->isSetFlag(self::AUTOCAPTURE_XML_PATH, ScopeInterface::SCOPE_STORE);
    }

    public function getNewOrderStatus(): string
    {
        return $this->scopeConfig->getValue(self::NEW_ORDER_STATUS_XML_PATH, ScopeInterface::SCOPE_STORE);
    }

    public function getPaymentMethods(): array
    {
        $paymentMethods = $this->scopeConfig->getValue(self::PAYMENT_METHODS_XML_PATH, ScopeInterface::SCOPE_STORE);

        switch ($paymentMethods) {
            case 'specified':
                return explode(',', $this->scopeConfig->getValue(self::PAYMENT_METHODS_SPECIFIED_XML_PATH, ScopeInterface::SCOPE_STORE) ?? '');
            case 'creditcard':
                return ['card'];
            default:
                return [];
        }
    }
}
