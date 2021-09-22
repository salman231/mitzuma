<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */

namespace Mageside\ShippingMatrixRates\Helper;

class MergeResultHelper
{
    /**
     * @param $itemRateResult
     * @param $result
     * @param string $method sum_first|sum_last|highest
     * @return array
     */
    public function _mergeResults($itemRateResult, $result, $method = 'sum_first')
    {
        if (empty($result) && empty($itemRateResult)) {
            return $result = [];
        }

        if (empty($result)) {
            return $result = $this->prepareFirstResults($itemRateResult, $method);
        } elseif (!empty($itemRateResult)) {
            return $result = $this->prepareAndMergeResults($result, $itemRateResult, $method);
        } else {
            return $result = [];
        }
    }

    /**
     * @param $itemRateResult
     * @param $method
     * @return array
     */
    public function prepareFirstResults($itemRateResult, $method)
    {
        $result = [];
        foreach ($itemRateResult as $keyRate => $itemRate) {
            if (empty($result)) {
                $result[] = $itemRate;
                continue;
            }
            reset($result);
            $found = false;
            foreach ($result as $keyResult => $itemResult) {
                if ($itemResult['delivery_method'] != $itemRate['delivery_method']) {
                    continue;
                }
                $found = true;
                if ($method == 'sum_first') {
                    //First one rate already got
                    continue;
                } elseif ($method == 'sum_last') {
                    //Get last one rate from result
                    $result[$keyResult] = $itemRate;
                } else {
                    //Rewrite rate to highest one if find
                    if ($itemResult['price'] < $itemRate['price']) {
                        $result[$keyResult] = $itemRate;
                    }
                }
            }
            if (!$found) {
                $result[] = $itemRate;
            }
        }

        return $result;
    }

    /**
     * @param $result
     * @param $itemRateResult
     * @param $method
     * @return mixed
     */
    public function prepareAndMergeResults($result, $itemRateResult, $method)
    {
        reset($result);
        foreach ($result as $keyResult => $itemResult) {
            $price = 0;
            $cost = 0;
            $found = false;
            $firstFound = false;
            reset($itemRateResult);
            foreach ($itemRateResult as $keyRate => $itemRate) {
                if ($itemResult['delivery_method'] != $itemRate['delivery_method']) {
                    continue;
                }
                if ($method == 'sum_first') {
                    //Get first one rate from result
                    if (!$firstFound) {
                        $price = $itemRate['price'];
                        $cost = $itemRate['cost'];
                        $firstFound = true;
                    }
                } elseif ($method == 'sum_last') {
                    //Get last one rate from result
                    $price = $itemRate['price'];
                    $cost = $itemRate['cost'];
                } else {
                    //Rewrite rate to highest one if find
                    if ($itemResult['price'] < $itemRate['price']) {
                        $itemResult = $itemRate;
                        $result[$keyResult] = $itemRate;
                    }
                }
                $found = true;
            }
            if ($found) {
                if ($method == 'sum_last' || $method == 'sum_first') {
                    $result[$keyResult]['price'] = $itemResult['price'] + $price;
                    $result[$keyResult]['cost'] = $itemResult['cost'] + $cost;
                }
                $result[$keyResult]['found'] = true;
            } else {
                $result[$keyResult]['found'] = false;
            }
        }

        $result = $this->cleanResult($result);

        return $result;
    }

    /**
     * @param $result
     * @return mixed
     */
    public function cleanResult($result)
    {
        //Deleting records that were not found in the processing
        reset($result);
        foreach ($result as $key => $item) {
            if (!isset($result[$key]['found'])) {
                unset($result[$key]);
            } elseif (isset($result[$key]['found']) && !$result[$key]['found']) {
                unset($result[$key]);
            }
        }

        return $result;
    }

    /**
     * Multiplication result on quantity
     *
     * @param $itemRateResult
     * @param $qty
     * @return array
     */
    public function _multipleResult($itemRateResult, $qty)
    {
        $result = [];
        foreach ($itemRateResult as $key => $item) {
            $result[] = $item;
            $result[$key]['price'] = $item['price'] * $qty;
            $result[$key]['cost'] = $item['cost'] * $qty;
        }

        return $result;
    }
}
