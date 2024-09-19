<?php

namespace Pensopay\Gateway\V2\Validator;

use InvalidArgumentException;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Psr\Log\LoggerInterface;

class ResponseCodeValidator extends AbstractValidator
{
    protected LoggerInterface $_logger;

    public function __construct(ResultInterfaceFactory $resultFactory, LoggerInterface $logger)
    {
        parent::__construct($resultFactory);
        $this->_logger = $logger;
    }

    public function validate(array $validationSubject): ResultInterface
    {
        if (!isset($validationSubject['response']) || !is_array($validationSubject['response'])) {
            throw new InvalidArgumentException('Response does not exist');
        }

        $response = $validationSubject['response'];

        if ($this->isSuccessfulTransaction($response)) {
            return $this->createResult(
                true,
                []
            );
        } else {
            return $this->createResult(
                false,
                [__('Gateway rejected the transaction.')]
            );
        }
    }

    /**
     * Validate transaction response
     *
     * @param $response
     * @return bool
     */
    private function isSuccessfulTransaction($response)
    {
        //Check that the transaction was accepted
        $responseObject = $response['object'];
        if (isset($responseObject)) {
            if (isset($responseObject['id'])) {
                return true; //status 200 means we have an object
            }
        }

        return false;
    }
}
