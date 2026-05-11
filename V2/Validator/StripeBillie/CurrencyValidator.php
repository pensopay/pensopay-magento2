<?php

namespace Pensopay\Gateway\V2\Validator\StripeBillie;

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
        if (in_array($currencyCode, ['EUR', 'SEK', 'NOK', 'DKK', 'GBP', 'CHF'], true)) {
            $isValid = true;
        } else {
            $fails[] = __('Billie can only be used when buying in EUR, SEK, NOK, DKK, GBP, or CHF');
        }

        return $this->createResult($isValid, $fails);
    }
}
