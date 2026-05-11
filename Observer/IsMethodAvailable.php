<?php

namespace Pensopay\Gateway\Observer;

use hisorange\BrowserDetect\Parser as Browser;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Pensopay\Gateway\Helper\Data as PensopayHelperData;
use Pensopay\Gateway\Model\Ui\Method\StripeIdealConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\StripeKlarnaConfigProvider;
use Pensopay\Gateway\Model\Ui\Method\GooglePayConfigProvider;

/**
 * This class is necessary because there's no apparent way to do this through the validator pool
 */
class IsMethodAvailable implements ObserverInterface
{
    public function __construct(
        private PensopayHelperData $pensoHelper
    ) {
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $result = $observer->getEvent()->getResult();
        $methodInstance = $observer->getEvent()->getMethodInstance();
        /** @var \Magento\Quote\Api\Data\CartInterface $quote */
        $quote = $observer->getEvent()->getQuote();
        $code = $methodInstance->getCode();

        // Per-method special gates (currency whitelists, browser sniffs, etc.)
        switch ($code) {
            case StripeIdealConfigProvider::CODE:
                if ($quote->getCurrency()->getQuoteCurrencyCode() !== 'EUR') {
                    $result->setData('is_available', false);
                    return;
                }
                break;
            case StripeKlarnaConfigProvider::CODE:
                if (!in_array($quote->getCurrency()->getQuoteCurrencyCode(),
                    ['AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'NOK', 'NZD', 'PLN', 'RON', 'SEK', 'USD'],
                    true
                )) {
                    $result->setData('is_available', false);
                    return;
                }
                break;
            case GooglePayConfigProvider::CODE:
                if (!Browser::isChrome()) {
                    $result->setData('is_available', false);
                    return;
                }
                break;
        }

        if (isset(PensopayHelperData::ACCOUNT_METHOD_API_IDS[$code])) {
            $cache = $this->pensoHelper->getAccountMethodsCache($quote->getStoreId());
            if (is_array($cache)) {
                $apiIds = PensopayHelperData::ACCOUNT_METHOD_API_IDS[$code];
                $found = false;
                foreach ($apiIds as $apiId) {
                    if (in_array($apiId, $cache, true)) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $result->setData('is_available', false);
                }
            }
        }
    }
}
