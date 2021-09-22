<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */

namespace Mageside\ShippingMatrixRates\Helper;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Get carrier settings
     *
     * @param $key
     * @param null $store
     * @return mixed
     */
    public function getConfig($key, $store = null)
    {
        return $this->scopeConfig
            ->getValue(
                'carriers/matrixrates/'.$key,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store
            );
    }

    /**
     * Get module settings
     *
     * @param $key
     * @param null $store
     * @return mixed
     */
    public function getConfigModule($key, $store = null)
    {
        return $this->scopeConfig
            ->getValue(
                'mageside_shippingmatrixrates/general/'.$key,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store
            );
    }
}
