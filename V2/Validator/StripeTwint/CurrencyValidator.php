<?php

namespace Pensopay\Gateway\V2\Validator\StripeTwint;

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
        if ($currencyCode === 'CHF') {
            $isValid = true;
        } else {
            $fails[] = __('TWINT can only be used when buying in Swiss Francs (CHF)');
        }

        return $this->createResult($isValid, $fails);
    }
}
