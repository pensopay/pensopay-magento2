<?php
namespace PensoPay\Gateway\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;

class ConfigProvider implements ConfigProviderInterface
{

    protected string $_code;

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                $this->_code => [
                    'redirectUrl' => 'pensopay/payment/redirect',
                ]
            ]
        ];
    }
}
