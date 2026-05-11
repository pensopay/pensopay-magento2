<?php

namespace Pensopay\Gateway\V2\Validator\StripePayco;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;

class CurrencyValidator extends AbstractValidator
{
    /**
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $isValid = false;
        $fails = [];

        $currencyCode = $validationSubject['currency'];
        if ($currencyCode === 'KRW') {
            $isValid = true;
        } else {
            $fails[] = __('PAYCO can only be used when buying in South Korean Won (KRW)');
        }

        return $this->createResult($isValid, $fails);
    }
}
