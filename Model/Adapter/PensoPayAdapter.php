<?php

namespace PensoPay\Gateway\Model\Adapter;

use Magento\Backend\Model\UrlInterface as BackendUrlInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Item;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Test\Di\WrappedClass\Logger;
use PensoPay\Gateway\Helper\Checkout as PensoPayHelperCheckout;
use PensoPay\Gateway\Helper\Data as PensoPayHelperData;
use PensoPay\Gateway\Model\Payment;
use PensoPay\Gateway\Model\PaymentFactory;
use PensoPay\Gateway\Model\Ui\Method\AnydayConfigProvider;
use PensoPay\Gateway\Model\Ui\Method\ViabillConfigProvider;
use PensoPay\Gateway\Model\Ui\Method\MobilePayConfigProvider;
use PensoPay\Gateway\Model\Ui\Method\ApplePayConfigProvider;
use PensoPay\Gateway\Model\Ui\Method\SwishConfigProvider;
use PensoPay\Gateway\Model\Ui\Method\KlarnaConfigProvider;
use Magento\Framework\Event\ManagerInterface as EventManager;

use Pensopay\Pensopay;
use Psr\Log\LoggerInterface;
use Symfony\Component\Intl\Countries;

class PensoPayAdapter
{
    protected LoggerInterface $logger;
    protected UrlInterface $url;
    protected PensoPayHelperData $helper;
    protected ResolverInterface $resolver;
    protected ScopeConfigInterface $scopeConfig;
    protected OrderRepositoryInterface $orderRepository;
    protected string $_apiKey = '';
    protected Pensopay $_client;
    protected PensoPayHelperCheckout $_checkoutHelper;
    protected PaymentFactory $_paymentFactory;
    protected StoreManagerInterface $_storeManager;
    protected ?StoreInterface $_frontStore = null;
    protected PensoPayHelperData $_pensoHelper;
    protected EncryptorInterface $_encryptor;
    protected EventManager $_eventManager;


    public function __construct(
        LoggerInterface $logger,
        UrlInterface $url,
        PensoPayHelperData $helper,
        ScopeConfigInterface $scopeConfig,
        ResolverInterface $resolver,
        OrderRepositoryInterface $orderRepository,
        PensoPayHelperCheckout $checkoutHelper,
        PaymentFactory $paymentFactory,
        StoreManagerInterface $storeManager,
        PensoPayHelperData $pensoHelper,
        EncryptorInterface $encryptor,
        EventManager $eventManager
    ) {
        $this->logger = $logger;
        $this->url = $url;
        $this->helper = $helper;
        $this->scopeConfig = $scopeConfig;
        $this->resolver = $resolver;
        $this->orderRepository = $orderRepository;
        $this->_checkoutHelper = $checkoutHelper;
        $this->_paymentFactory = $paymentFactory;
        $this->_storeManager = $storeManager;
        $this->_pensoHelper = $pensoHelper;
        $this->_encryptor = $encryptor;
        $this->_eventManager = $eventManager;

        $this->_apiKey = $this->helper->getApiKey();

//        $this->_apiKey = 'eda05a709b950ca9d78b2622d613bfd2068459ece3e64eb1efe97bceb791cfc0';
        $this->_client = Pensopay::create($this->_apiKey);
    }

    /**
     * @return StoreInterface|Store
     */
    protected function getFrontStore(): StoreInterface
    {
        if (!$this->_frontStore) {
            //This feels a little more reliable than get default store due to the ?: null
            //Intentionally structured as a loop to avoid having to check for empty results etc
            foreach ($this->_storeManager->getStores() as $store) {
                $this->_frontStore = $store;
                break;
            }
        }
        return $this->_frontStore;
    }

    protected function getFrontUrl($path = '', $params = []): string
    {
        if ($this->url instanceof BackendUrlInterface) {
            $store = $this->getFrontStore();
            return $store->getUrl($path, $params);
        }
        return $this->url->getUrl($path, $params);
    }

    /**
     * Authorize payment and create payment link
     *
     * @param array $attributes
     * @param bool $autoSave
     * @return array|bool
     */
    public function authorizeAndCreatePaymentLink(array $attributes, $autoSave = true)
    {
        try {
            $isVirtualTerminal = isset($attributes[PensoPayHelperCheckout::IS_VIRTUAL_TERMINAL]) && $attributes[PensoPayHelperCheckout::IS_VIRTUAL_TERMINAL];
            $paymentData = $this->_setupRequest($attributes);

            $paymentObject = \Pensopay\Model\Payments\Payment::create($paymentData);
            /** @var \Pensopay\Model\Payments\Payment $payment */
            $payment = $this->_client->payments()->create($paymentObject);

            $paymentArray = $payment->toArray();
            $paymentId = $paymentArray['id'];

            if ($isVirtualTerminal) {
                $this->_setExtraVirtualTerminalData($attributes, $paymentArray);
            }

//            if ($autoSave) {
//                $this->_autoSave($paymentArray);
//            }

            return $paymentArray;
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        return true;
    }

    protected function _setExtraVirtualTerminalData($attributes, &$paymentArray)
    {
        $paymentArray[PensoPayHelperCheckout::IS_VIRTUAL_TERMINAL] = true;
        $paymentArray['customer_name'] = $attributes['CUSTOMER_NAME'];
        $paymentArray['customer_email'] = $attributes['CUSTOMER_EMAIL'];
        $paymentArray['customer_street'] = $attributes['CUSTOMER_STREET'];
        $paymentArray['customer_zipcode'] = $attributes['CUSTOMER_ZIPCODE'];
        $paymentArray['customer_city'] = $attributes['CUSTOMER_CITY'];
        return $paymentArray;
    }

    protected function _setupRequest(&$attributes)
    {
        $paymentData = [
            'order_id' => $attributes['INCREMENT_ID'],
            'currency' => $attributes['CURRENCY'],
            'amount' => $attributes['AMOUNT'],
            'callback_url' => $this->getFrontUrl('pensopay/payment/callback', ['isAjax' => true]), //We add isAjax to counter magento 2.3 CSRF protection
            'testmode' => $this->helper->getIsTestMode(),
        ];

        $isVirtualTerminal = isset($attributes[PensoPayHelperCheckout::IS_VIRTUAL_TERMINAL]) && $attributes[PensoPayHelperCheckout::IS_VIRTUAL_TERMINAL];
        if (!$isVirtualTerminal) {
            $paymentData['autocapture'] = $this->helper->getIsAutocapture();
            $paymentData['success_url'] = $this->getFrontUrl('pensopay/payment/returnAction', ['_query' => ['ori' => $this->_encryptor->encrypt($attributes['INCREMENT_ID'])]]);
            $paymentData['cancel_url'] = $this->getFrontUrl('pensopay/payment/cancelAction');
        } else {
            $paymentData['autocapture'] = $attributes['AUTOCAPTURE'];
        }

        $orderData = [];
        $isVirtualTerminal = isset($attributes[PensoPayHelperCheckout::IS_VIRTUAL_TERMINAL]) && $attributes[PensoPayHelperCheckout::IS_VIRTUAL_TERMINAL];
        if (!$isVirtualTerminal) {
            $order = $this->orderRepository->get($attributes['ORDER_ID']);

            switch ($order->getPayment()->getMethod()) {
                case ViabillConfigProvider::CODE:
                    $paymentData['methods'][] = 'viabill';
                    break;
                case AnydayConfigProvider::CODE:
                    $paymentData['methods'][] = 'anyday';
                    break;
                case MobilePayConfigProvider::CODE:
                    $paymentData['methods'][] = 'mobilepay';
                    break;
                case ApplePayConfigProvider::CODE:
                    $paymentData['methods'][] = 'applepay';
                    break;
                case SwishConfigProvider::CODE:
                    $paymentData['methods'][] = 'swish';
                    break;
                case KlarnaConfigProvider::CODE:
                    $paymentData['methods'][] = 'klarna';
                    break;
                default: //Covers default payment method - pensopay
                    $paymentData['methods'][] = 'card';
                    break;
            }

            $storeId = $this->_pensoHelper->getStoreIdForOrderIncrement($attributes['INCREMENT_ID']);
            if (is_numeric($storeId)) {
                $this->setTransactionStore($storeId);
            }

            $shippingAddress = $attributes['SHIPPING_ADDRESS'];
            $orderData['shipping_address'] = [];
            $orderData['shipping_address']['name'] = $shippingAddress->getFirstName() . ' ' . $shippingAddress->getLastName();
            $orderData['shipping_address']['address'] = $shippingAddress->getStreetLine1();
            $orderData['shipping_address']['zipcode'] = $shippingAddress->getPostcode();
            $orderData['shipping_address']['city'] = $shippingAddress->getCity();
            $orderData['shipping_address']['country'] = Countries::getAlpha3Code($shippingAddress->getCountryId());
            $orderData['shipping_address']['email'] = $shippingAddress->getEmail();
            $orderData['shipping_address']['phone_number'] = $shippingAddress->getTelephone();

            $billingAddress = $attributes['BILLING_ADDRESS'];
            $orderData['billing_address'] = [];
            $orderData['billing_address']['name'] = $billingAddress->getFirstName() . ' ' . $billingAddress->getLastName();
            $orderData['billing_address']['address'] = $billingAddress->getStreetLine1();
            $orderData['billing_address']['zipcode'] = $billingAddress->getPostcode();
            $orderData['billing_address']['city'] = $billingAddress->getCity();
            $orderData['billing_address']['country'] = Countries::getAlpha3Code($billingAddress->getCountryId());
            $orderData['billing_address']['email'] = $billingAddress->getEmail();
            $orderData['billing_address']['phone_number'] = $billingAddress->getTelephone();

            $attributes['PAYMENT_METHOD'] = $order->getPayment()->getMethod();

//            if ($attributes['PAYMENT_METHOD'] !== KlarnaPaymentsConfigProvider::CODE) {
                $orderData['shipping'] = [
                    'amount' => $order->getBaseShippingInclTax() * 100,
                    'method' => $order->getShippingMethod(),
                    'company' => $order->getShippingDescription(),
                    'vat_rate' => 100 / ($order->getBaseShippingAmount() / (($order->getBaseShippingInclTax() - $order->getBaseShippingAmount()) ?: 1))
                ];
//            }

            //Build basket array
            $items = $attributes['ITEMS'];
            $orderData['basket'] = [];
            foreach ($items as $item) {
                if (!$item->getPrice() && $item->getParentItemId()) {
                    continue; //Simples of configurables that carry no prices aren't wanted
                }
                $orderData['basket'][] = [
                    'qty' => (int)$item->getQtyOrdered(),
                    'name' => $item->getName(),
                    'sku' => $item->getSku(),
                    'price' => (float)(round(($item->getBaseRowTotalInclTax() - $item->getBaseDiscountAmount()) / $item->getQtyOrdered(), 2) * 100),
                    'vat_rate' => $item->getTaxPercent() ?: 1
                ];
            }

//            if ($attributes['PAYMENT_METHOD'] === KlarnaPaymentsConfigProvider::CODE) {
//                $form['basket'][] = [
//                    'qty' => 1,
//                    'item_no' => 'shipping',
//                    'item_name' => 'Shipping',
//                    'item_price' => (int)($order->getShippingInclTax() * 100),
//                    'vat_rate' => 0,
//                ];
//            }

        } else {
            $paymentData['methods'][] = 'card';
            $orderData['basket'] = [
                [
                    'qty'        => 1,
                    'name'       => 'VirtualTerminal Payment',
                    'sku'        => 'virtualterminal',
                    'price'      => $attributes['AMOUNT'],
                    'vat_rate'   => 25, //TODO
                ]
            ];
            $orderData['shipping_address'] = [];
            $orderData['billing_address'] = [];
            $orderData['shipping'] = [];
        }

        $paymentData['order'] = $orderData;

        $dataObject = new DataObject();
        $dataObject->setPaymentData($paymentData);

        $this->_eventManager->dispatch('pensopay_gateway_setup_request_before', ['data_object' => $dataObject]);
        $paymentData = $dataObject->getPaymentData();

        return $paymentData;
    }

//    public function updatePaymentAndPaymentLink($attributes, $autoSave = true)
//    {
//        try {
//            $form = $this->_setupRequest($attributes);
//
//            $isVirtualTerminal = isset($attributes[PensoPayHelperCheckout::IS_VIRTUAL_TERMINAL]) && $attributes[PensoPayHelperCheckout::IS_VIRTUAL_TERMINAL];
//            if ($isVirtualTerminal) {
//                $form['id'] = $attributes['ORDER_ID'];
//            }
//
//            $payments = $this->_client->request->patch(sprintf('/payments/%s', $form['id']), $form);
//            $paymentArray = $payments->asArray();
//            $paymentId = $paymentArray['id'];
//
//            $paymentArray['link'] = $this->createPaymentLink($attributes, $paymentId);
//
//            if ($isVirtualTerminal) {
//                $this->_setExtraVirtualTerminalData($attributes, $paymentArray);
//            }
//
//            if ($autoSave) {
//                $paymentArray['payment_id'] = $attributes['payment_id'];
//                $this->_autoSave($paymentArray, true);
//            }
//
//            return $paymentArray;
//        } catch (\Exception $e) {
//            $this->logger->critical($e->getMessage());
//        }
//        return true;
//    }

    protected function _autoSave($payment, $update = false)
    {
        /** @var Payment $paymentObject */
        $paymentObject = $this->_paymentFactory->create();

        if ($update) {
            $paymentId = $payment['id'];
            $paymentObject->load($paymentId);
            if ($paymentObject->getId()) {
                $paymentObject->importFromRemotePayment($payment);
                $paymentObject->setId($paymentId);
            }
        } else {
            $paymentObject->importFromRemotePayment($payment);
        }
        $paymentObject->save();
    }

    /**
     * Capture payment
     */
    public function capture(array $attributes)
    {
        try {
            $this->setTransactionStore($attributes['STORE_ID']);

            $id = $attributes['TXN_ID'];
            $amount = $attributes['AMOUNT'];

            $payments = $this->_client->payments()->capture($id, $amount);
            return $payments->toArray();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        return true;
    }

    /**
     * Get Payment data from remote.
     *
     * @param $paymentId
     * @return array
     * @throws \Exception
     */
    public function getPayment($paymentId)
    {
        $this->logger->debug("Updating payment state for {$paymentId}");

        try {
            $payments = $this->_client->payments()->get($paymentId);
            return $payments->toArray();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            throw $e;
        }
    }

    public function setTransactionStore($storeId)
    {
        $apiKey = $this->helper->getApiKey($storeId);
        if (empty($apiKey)) {
            $apiKey = $this->helper->getApiKey(null);
        }
        $this->_apiKey = $apiKey;
        $this->_client = Pensopay::create($this->_apiKey);
    }

    /**
     * Cancel payment
     *
     * @param array $attributes
     * @return array|bool
     */
    public function cancel(array $attributes)
    {
        $this->logger->debug('Cancel payment');
        try {
            $this->setTransactionStore($attributes['STORE_ID']);

            $id = $attributes['TXN_ID'];

            $payments = $this->_client->payments()->cancel($id);
            return $payments->toArray();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        return true;
    }

    /**
     * Refund payment
     *
     * @param array $attributes
     * @return array|bool
     */
    public function refund(array $attributes)
    {
        $this->logger->debug('Refund payment');
        try {
            $this->setTransactionStore($attributes['STORE_ID']);

            $id = $attributes['TXN_ID'];
            $amount = $attributes['AMOUNT'];

            $payments = $this->_client->payments()->refund($id, $amount);
            return $payments->toArray();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        return true;
    }

    /**
     * Get language code from locale
     *
     * @return mixed
     */
    private function getLanguage()
    {
        return $this->getResolvedLanguage($this->resolver->getLocale());
    }

    private function getResolvedLanguage($lang)
    {
        //Map both norwegian locales to no
        $map = [
            'nb' => 'no',
            'nn' => 'no',
        ];

        $language = explode('_', $lang)[0];

        if (isset($map[$language])) {
            return $map[$language];
        }

        return $language;
    }
}
