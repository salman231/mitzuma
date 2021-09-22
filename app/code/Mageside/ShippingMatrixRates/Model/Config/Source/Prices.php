<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageside\ShippingMatrixRates\Model\Config\Source;

class Prices implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'base', 'label' => 'Base'],
            ['value' => 'tax', 'label' => 'Tax'],
            ['value' => 'discount', 'label' => 'Discount'],
        ];
    }
}
