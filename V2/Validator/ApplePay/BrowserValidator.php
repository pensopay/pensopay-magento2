<?php

namespace Pensopay\Gateway\V2\Validator\ApplePay;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Browser;

class BrowserValidator extends AbstractValidator
{
    /**
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $isValid = false;
        $fails = [];

        $countryCode = $validationSubject['country'];
        if (Browser::isSafari()) {
            $isValid = true;
        } else {
            $fails[] = __('This method can only be used on Safari');
        }

        return $this->createResult($isValid, $fails);
    }
}
