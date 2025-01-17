<?php

namespace Pensopay\Gateway\Model\ResourceModel\Virtualterminal;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Pensopay\Gateway\Model\ResourceModel\Payment as ResourcePayment;
use Psr\Log\LoggerInterface as Logger;

class Collection extends SearchResult
{
    protected RequestInterface $_request;

    public function __construct(
        EntityFactory    $entityFactory,
        Logger           $logger,
        FetchStrategy    $fetchStrategy,
        EventManager     $eventManager,
        RequestInterface $request,
                         $mainTable = \Pensopay\Gateway\Model\Payment::PAYMENT_TABLE,
                         $resourceModel = ResourcePayment::class
    )
    {
        $this->_request = $request;

        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    public function _beforeLoad()
    {
        $this->addFieldToFilter('is_virtualterminal', 1);
        return parent::_beforeLoad();
    }
}
