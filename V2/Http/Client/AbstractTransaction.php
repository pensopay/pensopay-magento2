<?php

namespace Pensopay\Gateway\V2\Http\Client;

use Exception;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Pensopay\Gateway\Model\Adapter\PensopayAdapter;
use Psr\Log\LoggerInterface;

abstract class AbstractTransaction implements ClientInterface
{
    protected LoggerInterface $logger;

    protected PensopayAdapter $adapter;

    public function __construct(LoggerInterface $logger, PensopayAdapter $adapter)
    {
        $this->logger = $logger;
        $this->adapter = $adapter;
    }

    /**
     * @inheritdoc
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $data = $transferObject->getBody();
        $log = [
            'request' => $data,
            'client' => static::class
        ];
        $response['object'] = [];

        try {
            $response['object'] = $this->process($data);
        } catch (Exception $e) {
            $this->logger->debug($e->getMessage());
            throw new ClientException(__($e->getMessage()));
        }

        return $response;
    }

    abstract protected function process(array $data);
}
