<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageside\ShippingMatrixRates\Model\Config\Source;

class FromFilterOperator implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'gt', 'label' => 'Greater than'],
            ['value' => 'gteq', 'label' => 'Greater than or Equal to'],
        ];
    }
}
