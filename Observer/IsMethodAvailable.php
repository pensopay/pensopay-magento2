<?php

namespace Pensopay\Gateway\Observer;

use hisorange\BrowserDetect\Parser as Browser;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * This class is necessary because there's no apparent way to do this through the validator pool
 */
class IsMethodAvailable implements ObserverInterface
{
    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $result = $observer->getEvent()->getResult();
        $methodInstance = $observer->getEvent()->getMethodInstance();
        /** @var \Magento\Quote\Api\Data\CartInterface $quote */
        $quote = $observer->getEvent()->getQuote();

        switch ($methodInstance->getCode()) {
            case 'pensopay_gateway_stripeideal':
                if ($quote->getCurrency()->getQuoteCurrencyCode() !== 'EUR') {
                    $result->setData('is_available', false);
                }
                break;
            case 'pensopay_gateway_stripeklarna':
                if (!in_array($quote->getCurrency()->getQuoteCurrencyCode(),
                    ['EUR', 'GBP', 'DKK', 'NOK', 'SEK', 'CZK', 'RON', 'PLN', 'CHF'],
              true
                )) {
                    $result->setData('is_available', false);
                }
                break;
            case 'pensopay_gateway_googlepay':
                if (!Browser::isChrome()) {
                    $result->setData('is_available', false);
                }
            default:
                break;
        }
    }
}
