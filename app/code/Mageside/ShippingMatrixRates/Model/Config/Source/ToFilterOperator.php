<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageside\ShippingMatrixRates\Model\Config\Source;

class ToFilterOperator implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'lt', 'label' => 'Less than'],
            ['value' => 'lteq', 'label' => 'Less than or Equal to'],
        ];
    }
}
