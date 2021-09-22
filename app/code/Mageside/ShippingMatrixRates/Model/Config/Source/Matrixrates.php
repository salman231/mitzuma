<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageside\ShippingMatrixRates\Model\Config\Source;

class Matrixrates implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Mageside\ShippingMatrixRates\Model\Carrier\Matrixrates
     */
    protected $_carrierMatrixrates;

    /**
     * @param \Mageside\ShippingMatrixRates\Model\Carrier\Matrixrates $carrierMatrixrates
     */
    public function __construct(\Mageside\ShippingMatrixRates\Model\Carrier\Matrixrates $carrierMatrixrates)
    {
        $this->_carrierMatrixrates = $carrierMatrixrates;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $arr = [];
        foreach ($this->_carrierMatrixrates->getCode('condition_name') as $k => $v) {
            $arr[] = ['value' => $k, 'label' => $v];
        }
        return $arr;
    }
}
