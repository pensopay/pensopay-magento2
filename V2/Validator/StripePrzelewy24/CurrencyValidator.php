<?php

namespace Pensopay\Gateway\V2\Validator\StripePrzelewy24;

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
        if (in_array($currencyCode, ['EUR', 'PLN'], true)) {
            $isValid = true;
        } else {
            $fails[] = __('Przelewy24 can only be used when buying in Euro (EUR) or Polish Zloty (PLN)');
        }

        return $this->createResult($isValid, $fails);
    }
}
