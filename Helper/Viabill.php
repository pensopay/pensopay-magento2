<?php

namespace Pensopay\Gateway\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Viabill helper
 */
class Viabill extends AbstractHelper
{
    const ALLOWED_CURRENCIES = [
        'DKK',
        'NOK',
        'USD'
    ];

    const TYPE_PRODUCT = 'product';
    const TYPE_CATALOG = 'list';
    const TYPE_CART = 'basket';
    const TYPE_CHECKOUT = 'payment';

    const XML_VIABILL_SHOP_ID = 'payment/pensopay_viabill/shop_id';
    const XML_VIABILL_ACTIVE = 'payment/pensopay_viabill/active';
    const XML_VIABILL_SHOW_IN_PRODUCT = 'payment/pensopay_viabill/show_in_product';
    const XML_VIABILL_SHOW_IN_CATEGORY = 'payment/pensopay_viabill/show_in_category';
    const XML_VIABILL_SHOW_IN_CART = 'payment/pensopay_viabill/show_in_cart';
    const XML_VIABILL_SHOW_IN_CHECKOUT = 'payment/pensopay_viabill/show_in_checkout';
    const XML_VIABILL_UPDATE_SELECTOR_BUNDLE = 'payment/pensopay_viabill/bundle_update_selector';
    const XML_VIABILL_UPDATE_SELECTOR_CONFIGURABLE = 'payment/pensopay_viabill/configurable_update_selector';
    const XML_VIABILL_UPDATE_SELECTOR_CATALOG = 'payment/pensopay_viabill/catalog_update_selector';

    protected LayoutInterface $_layout;
    protected StoreManagerInterface $_storeManager;

    public function __construct(
        Context               $context,
        LayoutInterface       $layout,
        StoreManagerInterface $storeManager
    )
    {
        $this->_layout = $layout;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Checks if viabill is active and valid
     */
    public function isActive(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_VIABILL_ACTIVE, ScopeInterface::SCOPE_STORE) && $this->getShopId();
    }

    /** Get viabill shop id */
    public function getShopId(): string
    {
        return $this->scopeConfig->getValue(self::XML_VIABILL_SHOP_ID, ScopeInterface::SCOPE_STORE) ?? '';
    }

    /** Should show in product page */
    public function getShowInProductPage(): bool
    {
        return $this->isActive() && $this->scopeConfig->isSetFlag(self::XML_VIABILL_SHOW_IN_PRODUCT, ScopeInterface::SCOPE_STORE);
    }

    /** Should show in category page */
    public function getShowInCategoryPage(): bool
    {
        return $this->isActive() && $this->scopeConfig->isSetFlag(self::XML_VIABILL_SHOW_IN_CATEGORY, ScopeInterface::SCOPE_STORE);
    }

    /** Should show in cart page */
    public function getShowInCartPage(): bool
    {
        return $this->isActive() && $this->scopeConfig->isSetFlag(self::XML_VIABILL_SHOW_IN_CART, ScopeInterface::SCOPE_STORE);
    }

    /** Should show in checkout page */
    public function getShowInCheckoutPage(): bool
    {
        return $this->isActive() && $this->scopeConfig->isSetFlag(self::XML_VIABILL_SHOW_IN_CHECKOUT, ScopeInterface::SCOPE_STORE);
    }

    public function getBundleProductUpdateSelector(): string
    {
        return $this->scopeConfig->getValue(self::XML_VIABILL_UPDATE_SELECTOR_BUNDLE, ScopeInterface::SCOPE_STORE) ?? '';
    }

    public function getConfigurableProductUpdateSelector(): string
    {
        return $this->scopeConfig->getValue(self::XML_VIABILL_UPDATE_SELECTOR_CONFIGURABLE, ScopeInterface::SCOPE_STORE) ?? '';
    }

    public function getCatalogUpdateSelector(): string
    {
        return $this->scopeConfig->getValue(self::XML_VIABILL_UPDATE_SELECTOR_CATALOG, ScopeInterface::SCOPE_STORE) ?? '';
    }

    public function isCurrentCurrencyAllowed(): bool
    {
        return in_array($this->_storeManager->getStore()->getCurrentCurrency()->getCode(), self::ALLOWED_CURRENCIES, true);
    }

    public function renderViabillPrice($type, $price, $dynamicPriceSelector = ''): string
    {
        if ($this->isCurrentCurrencyAllowed() && (
                ($type === self::TYPE_PRODUCT && $this->getShowInProductPage()) ||
                ($type === self::TYPE_CART && $this->getShowInCartPage()) ||
                ($type === self::TYPE_CHECKOUT && $this->getShowInCheckoutPage()) ||
                ($type === self::TYPE_CATALOG && $this->getShowInCategoryPage())
            )) {
            return $this->_layout
                ->createBlock(Template::class)
                ->setTemplate('Pensopay_Gateway::viabill/tag.phtml')
                ->setType($type)
                ->setPrice($price)
                ->setDynamicPriceSelector($dynamicPriceSelector)
                ->toHtml();
        }
        return '';
    }
}
