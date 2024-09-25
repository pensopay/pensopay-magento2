<?php

namespace Pensopay\Gateway\Ui\DataProvider\Payment\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Pensopay\Gateway\Model\ResourceModel\Payment\CollectionFactory;

class PaymentDataProvider extends AbstractDataProvider
{
    private PoolInterface $pool;

    protected array $_loadedData = [];

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param PoolInterface $pool
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        PoolInterface $pool,
        array $meta = [],
        array $data = []
    )
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->pool = $pool;
    }

    public function getData()
    {
        if (empty($this->_loadedData)) {
            $items = $this->collection->getItems();
            foreach ($items as $payment) {
                if ($payment->getId()) {
                    $this->_loadedData[$payment->getId()] = $payment->getData();
                }
            }
        }
        return $this->_loadedData;
    }

    public function getFirstItem()
    {
        if (!empty($this->_loadedData)) {
            return $this->_loadedData[0];
        }
        return false;
    }
}
