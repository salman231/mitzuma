<?php declare(strict_types=1);

namespace Mstore\PaymentResolver\Block;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;

class Display extends \Hyperpay\Extension\Block\Display
{
    /**
     * Retrieve payment form shopper url
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getShopperUrl()
    {
        $base = $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB);
        return $base . "mspayment/resolver_hyperpay/status";
    }
}
