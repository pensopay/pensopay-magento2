<?php

namespace Pensopay\Gateway\V2\Validator\StripeBlik;

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
        if ($currencyCode === 'PLN') {
            $isValid = true;
        } else {
            $fails[] = __('BLIK can only be used when buying in Polish Złoty (PLN)');
        }

        return $this->createResult($isValid, $fails);
    }
}
