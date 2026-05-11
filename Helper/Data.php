<?php

namespace Pensopay\Gateway\Helper;

use Magento\Backend\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\Storage\WriterInterface as ConfigWriter;
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
use Pensopay\Gateway\Model\Ui\Method\AnydayConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\ApplePayConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\GooglePayConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\KlarnaConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\MobilePayConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\PaypalConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\PensopayConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\StripeBancontactConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\StripeBillieConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\StripeBlikConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\StripeIdealConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\StripeKakaoPayConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\StripeKlarnaConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\StripeMbwayConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\StripeNaverPayConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\StripePaycoConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\StripePrzelewy24ConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\StripeSepaConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\StripeTwintConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\SwishConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\ViabillConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\VippsPspConfigProvider;

class Data extends AbstractHelper
{
    const PENSOPAY_METHOD_CODES = [
        PensopayConfigProvider::CODE,
        ViabillConfigProvider::CODE,
        AnydayConfigProvider::CODE,
        ApplePayConfigProvider::CODE,
        GooglePayConfigProvider::CODE,
        MobilePayConfigProvider::CODE,
        SwishConfigProvider::CODE,
        StripeKlarnaConfigProvider::CODE,
        StripeIdealConfigProvider::CODE,
        VippsPspConfigProvider::CODE,
        PaypalConfigProvider::CODE,
        KlarnaConfigProvider::CODE,
        StripePaycoConfigProvider::CODE,
        StripeKakaoPayConfigProvider::CODE,
        StripeNaverPayConfigProvider::CODE,
        StripeBillieConfigProvider::CODE,
        StripeTwintConfigProvider::CODE,
        StripeBancontactConfigProvider::CODE,
        StripePrzelewy24ConfigProvider::CODE,
        StripeBlikConfigProvider::CODE,
        StripeSepaConfigProvider::CODE,
        StripeMbwayConfigProvider::CODE,
    ];

    const ACCOUNT_METHOD_API_IDS = [
        ViabillConfigProvider::CODE          => ['viabill'],
        AnydayConfigProvider::CODE           => ['anyday'],
        ApplePayConfigProvider::CODE         => ['applepay'],
        GooglePayConfigProvider::CODE        => ['googlepay'],
        MobilePayConfigProvider::CODE        => ['mobilepay'],
        SwishConfigProvider::CODE            => ['swish', 'stripe_swish'],
        StripeKlarnaConfigProvider::CODE     => ['stripe_klarna'],
        StripeIdealConfigProvider::CODE      => ['stripe_ideal'],
        VippsPspConfigProvider::CODE         => ['vippspsp'],
        PaypalConfigProvider::CODE           => ['paypal'],
        KlarnaConfigProvider::CODE           => ['klarna'],
        StripePaycoConfigProvider::CODE      => ['stripe_payco'],
        StripeKakaoPayConfigProvider::CODE   => ['stripe_kakaopay'],
        StripeNaverPayConfigProvider::CODE   => ['stripe_naverpay'],
        StripeBillieConfigProvider::CODE     => ['stripe_billie'],
        StripeTwintConfigProvider::CODE      => ['stripe_twint'],
        StripeBancontactConfigProvider::CODE => ['stripe_bancontact'],
        StripePrzelewy24ConfigProvider::CODE => ['stripe_przelewy24'],
        StripeBlikConfigProvider::CODE       => ['stripe_blik'],
        StripeSepaConfigProvider::CODE       => ['stripe_sepa'],
        StripeMbwayConfigProvider::CODE      => ['stripe_mbway'],
    ];

    const TESTMODE_XML_PATH = 'payment/pensopay_gateway/testmode';
    const TRANSACTION_FEE_LABEL_XML_PATH = 'payment/pensopay_gateway/transaction_fee_label';
    const API_KEY_XML_PATH = 'payment/pensopay_gateway/api_key';
    const PRIVATE_KEY_XML_PATH = 'payment/pensopay_gateway/private_key';
    const AUTOCAPTURE_XML_PATH = 'payment/pensopay_gateway/autocapture';
    const NEW_ORDER_STATUS_XML_PATH = 'payment/pensopay_gateway/new_order_status';
    const PAYMENT_METHODS_XML_PATH = 'payment/pensopay_gateway/payment_methods';
    const PAYMENT_METHODS_SPECIFIED_XML_PATH = 'payment/pensopay_gateway/payment_methods_specified';
    const ACCOUNT_METHODS_XML_PATH = 'payment/pensopay_gateway/account_methods';

    protected Session $_backendSession;
    protected TransportBuilder $_transportBuilder;
    protected OrderRepository $_orderRepository;
    protected SearchCriteriaBuilder $_searchCriteriaBuilder;
    protected ConfigWriter $_configWriter;

    public function __construct(
        Context               $context,
        Session               $backendSession,
        TransportBuilder      $transportBuilder,
        OrderRepository       $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ConfigWriter          $configWriter
    )
    {
        parent::__construct($context);
        $this->_backendSession = $backendSession;
        $this->_transportBuilder = $transportBuilder;
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_configWriter = $configWriter;
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

    public function sendEmail($email, $name, $amount, $currency, $link, $storeId = null): bool
    {
        $vars = [
            'currency' => $currency,
            'amount' => $amount,
            'link' => $link
        ];

        $senderName = $this->scopeConfig->getValue('trans_email/ident_sales/name', ScopeInterface::SCOPE_STORE, $storeId);
        $senderEmail = $this->scopeConfig->getValue('trans_email/ident_sales/email', ScopeInterface::SCOPE_STORE, $storeId);

        $postObject = new DataObject();
        $postObject->setData($vars);

        $sender = [
            'name' => $senderName,
            'email' => $senderEmail,
        ];

        $transport = $this->_transportBuilder->setTemplateIdentifier('pensopay_gateway_paymentlink_email')
            ->setTemplateOptions([
                'area' => Area::AREA_FRONTEND,
                'store' => $storeId ?? Store::DEFAULT_STORE_ID
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

    public function getIsTestmode($storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::TESTMODE_XML_PATH, ScopeInterface::SCOPE_STORE, $storeId);
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

    /**
     * Returns the cached account methods list (Pensopay GET /v2/methods response),
     * or null when the cache has never been populated. An empty array means the
     * account has no methods enabled.
     */
    public function getAccountMethodsCache($storeId = null): ?array
    {
        $raw = $this->scopeConfig->getValue(self::ACCOUNT_METHODS_XML_PATH, ScopeInterface::SCOPE_STORE, $storeId);
        if ($raw === null || $raw === '') {
            return null;
        }
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Persist the account methods list at the appropriate scope. Pass null for
     * $methods to clear the cache.
     */
    public function writeAccountMethodsCache(?array $methods, ?int $storeId, ?int $websiteId): void
    {
        $payload = $methods === null ? '' : json_encode(array_values($methods));

        if ($storeId) {
            $this->_configWriter->save(self::ACCOUNT_METHODS_XML_PATH, $payload, ScopeInterface::SCOPE_STORES, $storeId);
        } elseif ($websiteId) {
            $this->_configWriter->save(self::ACCOUNT_METHODS_XML_PATH, $payload, ScopeInterface::SCOPE_WEBSITES, $websiteId);
        } else {
            $this->_configWriter->save(self::ACCOUNT_METHODS_XML_PATH, $payload, 'default', 0);
        }
    }
}
