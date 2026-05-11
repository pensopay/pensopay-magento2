<?php

namespace Pensopay\Gateway\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Store\Model\StoreManagerInterface;
use Pensopay\Gateway\Helper\Data as PensopayHelperData;
use Pensopay\Gateway\Model\Adapter\PensopayAdapter;
use Throwable;

class AdminConfigSaveRefreshAccountMethods implements ObserverInterface
{
    public function __construct(
        private RequestInterface      $request,
        private StoreManagerInterface $storeManager,
        private PensopayHelperData    $pensoHelper,
        private PensopayAdapter       $adapter,
        private MessageManager        $messageManager
    ) {
    }

    public function execute(Observer $observer)
    {
        $storeParam = $this->request->getParam('store');
        $websiteParam = $this->request->getParam('website');

        $storeId = $storeParam ? (int)$storeParam : null;
        $websiteId = $websiteParam ? (int)$websiteParam : null;

        if ($storeId !== null) {
            $apiKeyStoreId = $storeId;
        } elseif ($websiteId !== null) {
            try {
                $apiKeyStoreId = (int)$this->storeManager->getWebsite($websiteId)->getDefaultStore()->getId();
            } catch (Throwable $e) {
                $this->messageManager->addWarningMessage(
                    __('Pensopay: could not resolve website %1 to refresh account methods: %2', $websiteId, $e->getMessage())
                );
                return;
            }
        } else {
            $apiKeyStoreId = 0;
        }

        $methods = $this->adapter->fetchAccountMethods($apiKeyStoreId);

        if ($methods === null) {
            $this->messageManager->addWarningMessage(
                __('Pensopay account methods could not be refreshed from the API. Any existing cached methods are still in effect. See var/log/pensopay.log for details.')
            );
            return;
        }

        try {
            $this->pensoHelper->writeAccountMethodsCache($methods, $storeId, $websiteId);
        } catch (Throwable $e) {
            $this->messageManager->addWarningMessage(
                __('Pensopay account methods were fetched but could not be saved: %1', $e->getMessage())
            );
        }
    }
}
