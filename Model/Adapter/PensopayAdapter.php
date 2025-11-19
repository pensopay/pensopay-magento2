<?php

namespace Pensopay\Gateway\Model\Adapter;

use Exception;
use GuzzleHttp\Client;
use Magento\Backend\Model\UrlInterface as BackendUrlInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Pensopay\Client\Api\PaymentsApi;
use Pensopay\Client\Configuration;
use Pensopay\Client\Model\PaymentCapturePaymentRequest;
use Pensopay\Client\Model\PaymentCreatePaymentRequest;
use Pensopay\Client\Model\PaymentRefundPaymentRequest;
use Pensopay\Gateway\Helper\Checkout as PensopayHelperCheckout;
use Pensopay\Gateway\Helper\Data as PensopayHelperData;
use Pensopay\Gateway\Model\Payment;
use Pensopay\Gateway\Model\PaymentFactory;
use Pensopay\Gateway\Model\Ui\Method\AnydayConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\ApplePayConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\GooglePayConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\KlarnaConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\MobilePayConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\StripeIdealConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\StripeKlarnaConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\SwishConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\ViabillConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\VippsPspConfigProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\Intl\Countries;

class PensopayAdapter
{
    protected LoggerInterface $logger;

    protected UrlInterface $url;

    protected PensopayHelperData $helper;

    protected ResolverInterface $resolver;

    protected ScopeConfigInterface $scopeConfig;

    protected OrderRepositoryInterface $orderRepository;

    protected $_client;

    protected PensopayHelperCheckout $_checkoutHelper;

    protected PaymentFactory $_paymentFactory;

    protected StoreManagerInterface $_storeManager;

    protected ?StoreInterface $_frontStore = null;

    protected PensopayHelperData $_pensoHelper;

    protected EncryptorInterface $_encryptor;

    protected EventManager $_eventManager;


    public function __construct(
        LoggerInterface          $logger,
        UrlInterface             $url,
        PensopayHelperData       $helper,
        ScopeConfigInterface     $scopeConfig,
        ResolverInterface        $resolver,
        OrderRepositoryInterface $orderRepository,
        PensopayHelperCheckout   $checkoutHelper,
        PaymentFactory           $paymentFactory,
        StoreManagerInterface    $storeManager,
        PensopayHelperData       $pensoHelper,
        EncryptorInterface       $encryptor,
        EventManager             $eventManager
    )
    {
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

        $this->_client = new PaymentsApi(new Client(), $this->getClientConfiguration(null));
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
            $isVirtualTerminal = isset($attributes[PensopayHelperCheckout::IS_VIRTUAL_TERMINAL]) && $attributes[PensopayHelperCheckout::IS_VIRTUAL_TERMINAL];
            $paymentData = $this->_setupRequest($attributes);

            try {
                $requestPayload = new PaymentCreatePaymentRequest($paymentData);
                $result = $this->_client->createPayment($requestPayload);
                $paymentArray = json_decode((string)$result, true);
            } catch (Exception $e) {
                throw new Exception('Exception when calling PaymentsApi->createPayment: ' . $e->getMessage());
            }

            $paymentId = $paymentArray['id'];

            if ($isVirtualTerminal) {
                $this->_setExtraVirtualTerminalData($attributes, $paymentArray);
            }

            if ($autoSave) {
                $this->_autoSave($paymentArray);
            }

            return $paymentArray;
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        return true;
    }

    protected function _setExtraVirtualTerminalData($attributes, &$paymentArray)
    {
        $paymentArray[PensopayHelperCheckout::IS_VIRTUAL_TERMINAL] = true;
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
            'currency' =>  $attributes['CURRENCY'],
            'amount' => $attributes['AMOUNT'],
//            'callback_url' => 'https://x.ngrok-free.app' . '/pensopaygw/payment/callback?isAjax=true', //We add isAjax to counter magento 2.3 CSRF protection
            'callback_url' => $this->getFrontUrl('pensopaygw/payment/callback', ['isAjax' => true]), //We add isAjax to counter magento 2.3 CSRF protection
            'testmode' => $this->helper->getIsTestMode(),
        ];

        $isVirtualTerminal = isset($attributes[PensopayHelperCheckout::IS_VIRTUAL_TERMINAL]) && $attributes[PensopayHelperCheckout::IS_VIRTUAL_TERMINAL];
        if (!$isVirtualTerminal) {
            $paymentData['autocapture'] = $this->helper->getIsAutocapture();
            $paymentData['success_url'] = $this->getFrontUrl('pensopaygw/payment/returnAction', ['_query' => ['ori' => $this->_encryptor->encrypt($attributes['INCREMENT_ID'])]]);
            $paymentData['cancel_url'] = $this->getFrontUrl('pensopaygw/payment/cancelAction');
        } else {
            $paymentData['autocapture'] = $attributes['AUTOCAPTURE'];
        }

        $orderData = [];
        $isVirtualTerminal = isset($attributes[PensopayHelperCheckout::IS_VIRTUAL_TERMINAL]) && $attributes[PensopayHelperCheckout::IS_VIRTUAL_TERMINAL];
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
                case GooglePayConfigProvider::CODE:
                    $paymentData['methods'][] = 'googlepay';
                    break;
                case SwishConfigProvider::CODE:
                    $paymentData['methods'][] = 'swish';
                    break;
                case KlarnaConfigProvider::CODE:
                    $paymentData['methods'][] = 'klarna';
                    break;
                case VippsPspConfigProvider::CODE:
                    $paymentData['methods'][] = 'vippspsp';
                    break;
                case StripeKlarnaConfigProvider::CODE:
                    $paymentData['methods'][] = 'stripe_klarna';
                    break;
                case StripeIdealConfigProvider::CODE:
                    $paymentData['methods'][] = 'stripe_ideal';
                    break;
                default: //Covers default payment method - pensopay
                    $paymentMethods = $this->_pensoHelper->getPaymentMethods();
                    if (!empty($paymentMethods)) {
                        $paymentData['methods'] = $paymentMethods;
                    }
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
            $orderData['shipping_address']['country'] = $shippingAddress->getCountryId();
            $orderData['shipping_address']['email'] = $shippingAddress->getEmail();
            $orderData['shipping_address']['phone_number'] = $shippingAddress->getTelephone();

            $billingAddress = $attributes['BILLING_ADDRESS'];
            $orderData['billing_address'] = [];
            $orderData['billing_address']['name'] = $billingAddress->getFirstName() . ' ' . $billingAddress->getLastName();
            $orderData['billing_address']['address'] = $billingAddress->getStreetLine1();
            $orderData['billing_address']['zipcode'] = $billingAddress->getPostcode();
            $orderData['billing_address']['city'] = $billingAddress->getCity();
            $orderData['billing_address']['country'] = $billingAddress->getCountryId();
            $orderData['billing_address']['email'] = $billingAddress->getEmail();
            $orderData['billing_address']['phone_number'] = $billingAddress->getTelephone();

            $attributes['PAYMENT_METHOD'] = $order->getPayment()->getMethod();

            $shippingPrice = 0;
            $vatRate = 0;
            if ($order->getBaseShippingAmount() > 0 && $order->getBaseShippingInclTax() > 0) {
                $shippingPrice = $order->getBaseShippingInclTax() * 100;
                if ($order->getBaseShippingInclTax() - $order->getBaseShippingAmount() > 0) {
                    $vatRate = 100 / ($order->getBaseShippingAmount() / ($order->getBaseShippingInclTax() - $order->getBaseShippingAmount()));
                }
            }

            $orderData['shipping'] = [
                'price' => $shippingPrice,
                'method' => $order->getShippingMethod(),
                'company' => $order->getShippingDescription(),
                'vat_rate' => $vatRate
            ];
        } else {
            $orderData['billing_address'] = [];
            $orderData['shipping_address'] = [];
            $orderData['shipping'] = [];

            $orderData['billing_address']['name'] = '';
            $orderData['shipping_address']['name'] = '';
            $orderData['shipping']['method'] = 'VirtualTerminal';

            $paymentData['methods'][] = 'card';
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
//            $isVirtualTerminal = isset($attributes[PensopayHelperCheckout::IS_VIRTUAL_TERMINAL]) && $attributes[PensopayHelperCheckout::IS_VIRTUAL_TERMINAL];
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

            $requestPayload = new PaymentCapturePaymentRequest(['amount' => $amount]);
            $result = $this->_client->capturePayment($id, $requestPayload);
            return json_decode((string)$result, true);
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        return true;
    }

    /**
     * Get Payment data from remote.
     *
     * @param $paymentId
     * @return array
     * @throws Exception
     */
    public function getPayment($paymentId)
    {
        $this->logger->debug("Updating payment state for {$paymentId}");

        try {
            return json_decode((string)$this->_client->getPayment($paymentId), true);
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
            throw $e;
        }
    }

    protected function getApiKey($storeId = null)
    {
        $apiKey = $this->helper->getApiKey($storeId);
        if (empty($apiKey) && $storeId) {
            $apiKey = $this->helper->getApiKey(null);
        }
        return $apiKey;
    }

    protected function getClientConfiguration($storeId = null)
    {
        return Configuration::getDefaultConfiguration()->setAccessToken($this->getApiKey($storeId));
    }

    public function setTransactionStore($storeId)
    {
        $this->_client = new PaymentsApi(new Client(), $this->getClientConfiguration($storeId));
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

            return json_decode((string)$this->_client->cancelPayment($id), true);
        } catch (Exception $e) {
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

            $requestPayload = new PaymentRefundPaymentRequest(['amount' => $amount]);
            $result = $this->_client->refundPayment($id, $requestPayload);
            return json_decode((string)$result, true);
        } catch (Exception $e) {
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
