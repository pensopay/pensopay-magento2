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

        switch ($methodInstance->getCode()) {
            case 'pensopay_gateway_applepay':
                if (!Browser::isSafari()) {
                    $result->setData('is_available', false);
                }
                break;
            default:
                break;
        }
    }
}
