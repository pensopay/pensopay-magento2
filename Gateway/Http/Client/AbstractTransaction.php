<?php

namespace PensoPay\Gateway\Gateway\Http\Client;

use PensoPay\Gateway\Model\Adapter\PensoPayAdapter;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use Psr\Log\LoggerInterface;

abstract class AbstractTransaction implements ClientInterface
{
    protected Logger $logger;
    protected LoggerInterface $monolog;
    protected PensoPayAdapter $adapter;

    public function __construct(Logger $logger, LoggerInterface $monolog, PensoPayAdapter $adapter)
    {
        $this->logger = $logger;
        $this->monolog = $monolog;
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
        } catch (\Exception $e) {
            $this->logger->debug([$e->getMessage()]);
            throw new ClientException(__($e->getMessage()));
        } finally {
            $log['response'] = (array) $response['object'];
            $this->logger->debug($log);
        }

        return $response;
    }

    abstract protected function process(array $data);
}
