<?php

namespace Pensopay\Gateway\V2\Validator\Swish;

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

        //Swish only allows SEK for now
        $currencyCode = $validationSubject['currency'];
        if ($currencyCode === 'SEK') {
            $isValid = true;
        } else {
            $fails[] = __('Swish can only be used when buying in Swedish Krone (SEK)');
        }

        return $this->createResult($isValid, $fails);
    }
}
