<?php

namespace Pensopay\Gateway\V2\Validator\StripeBancontact;

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
        if ($currencyCode === 'EUR') {
            $isValid = true;
        } else {
            $fails[] = __('Bancontact can only be used when buying in Euro (EUR)');
        }

        return $this->createResult($isValid, $fails);
    }
}
