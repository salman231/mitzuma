<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */

namespace Mageside\ShippingMatrixRates\Helper;

class ItemHelper
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var \Mageside\ShippingMatrixRates\Helper\Config
     */
    protected $_carrierConfig;

    /**
     * @param \Magento\Eav\Model\Config $config
     * @param \Mageside\ShippingMatrixRates\Helper\Config $carrierConfig
     */
    public function __construct(
        \Magento\Eav\Model\Config $config,
        \Mageside\ShippingMatrixRates\Helper\Config $carrierConfig
    ) {
        $this->_eavConfig = $config;
        $this->_carrierConfig = $carrierConfig;
    }

    /**
     * Filtering quote items
     *
     * @param $items
     * @return array
     */
    public function _prepareItems($items)
    {
        $itemsArray = [];
        foreach ($items as $key => $item) {
            //Skip product if it is virtual and we don't need add it price
            if ($item->getProduct()->isVirtual() && !$this->_carrierConfig->getConfig('include_virtual_price')) {
                continue;
            }
            //Skip product if it has free shipping
            if ($item->getFreeShipping() === true) {
                continue;
            }
            //Skip product if it is children of bundle product
            if ($item->getParentItem()) {
                continue;
            }
            //Calculate weight and qty if bundle product ship separately
            $preparedItem = null;
            if ($item->getHasChildren() && $item->isShipSeparately()) {
                foreach ($item->getChildren() as $child) {
                    //Skip product if it is virtual and we don't need add it price
                    if ($child->getProduct()->isVirtual() &&
                        !$this->_carrierConfig->getConfig('include_virtual_price')
                    ) {
                        continue;
                    }
                    $preparedItem = $this->_getItemData($child);
                    //Prepared item hasn't any data
                    if (!$preparedItem) {
                        continue;
                    }
                    $itemsArray[] = $preparedItem;
                }
            } else {
                $preparedItem = $this->_getItemData($item);
                //Prepared item hasn't any data
                if (!$preparedItem) {
                    continue;
                }
                $itemsArray[] = $preparedItem;
            }
        }

        return $itemsArray;
    }

    /**
     * Get Item data
     * return item array or false if item hasn't any data
     *
     * @param $item
     * @return array|bool
     */
    public function _getItemData($item)
    {
        $qty = $item->getQty() ? $item->getQty() : 1;
        $weight = $item->getWeight() ? $item->getWeight() * $qty : 0;
        $shipping_group = $item->getShippingGroup() ?
            $item->getShippingGroup() :
            '*';
        if ($this->_carrierConfig->getConfig('use_base_price')) {
            if ($this->_carrierConfig->getConfig('use_tax_price')) {
                $price = $item->getBaseRowTotalInclTax() ?
                    $item->getBaseRowTotalInclTax() :
                    0;
            } else {
                $price = $item->getBaseRowTotal() ?
                    $item->getBaseRowTotal() :
                    0;
            }
            if ($this->_carrierConfig->getConfig('use_discount')) {
                $price -= $this->_getDiscountAmount($item, 'base');
            }
        } else {
            if ($this->_carrierConfig->getConfig('use_tax_price')) {
                $price = $item->getRowTotalInclTax() ?
                    $item->getRowTotalInclTax() :
                    0;
            } else {
                $price = $item->getRowTotal() ?
                    $item->getRowTotal() :
                    0;
            }
            if ($this->_carrierConfig->getConfig('use_discount')) {
                $price -= $this->_getDiscountAmount($item, 'final');
            }
        }
        //Recalculate data if part of item has free shipping
        if (is_numeric($item->getFreeShipping())) {
            $freeQty = $item->getFreeShipping();
            if ($qty > $freeQty) {
                $price = $price / $qty * ($qty - $freeQty);
                $qty -= $freeQty;
                $weight = $item->getWeight() ? $item->getWeight() * $qty : 0;
            } else {
                //All quantity of item has free shipping
                return false;
            }
        }

        return [
            'weight' => $weight,
            'qty' => $qty,
            'price' => $price,
            'shipping_group' => $shipping_group
        ];
    }

    /**
     * Merging items array by group
     *
     * @param $items
     * @return array
     */
    public function _sortItemsByGroup($items)
    {
        $itemsSorted = [];
        foreach ($items as $key => $item) {
            if (empty($itemsSorted)) {
                $itemsSorted[] = $item;
            } else {
                $found = false;
                reset($itemsSorted);
                foreach ($itemsSorted as $keySorted => $itemPrepared) {
                    if ($itemPrepared['shipping_group'] == $item['shipping_group']) {
                        $itemsSorted[$keySorted] = [
                            'weight' => $itemPrepared['weight'] + $item['weight'],
                            'qty' => $itemPrepared['qty'] + $item['qty'],
                            'price' => $itemPrepared['price'] + $item['price'],
                            'shipping_group' => $itemPrepared['shipping_group']
                        ];
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $itemsSorted[] = $item;
                }
            }
        }

        return $itemsSorted;
    }

    /**
     * Calculate discount amount
     *
     * @param $item
     * @param $type
     * @return int
     */
    public function _getDiscountAmount($item, $type)
    {
        $discountAmount = 0;
        if ($item->getHasChildren() &&
            $item->getProductType() != 'configurable'
        ) {
            foreach ($item->getChildren() as $child) {
                $discountAmount += ($type == 'base') ?
                    $child->getBaseDiscountAmount() :
                    $child->getDiscountAmount();
            }
        } else {
            $discountAmount = ($type == 'base') ?
                $item->getBaseDiscountAmount() :
                $item->getDiscountAmount();
        }

        return $discountAmount;
    }
}
