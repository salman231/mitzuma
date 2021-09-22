<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */

namespace Mageside\ShippingMatrixRates\Helper;

class LogicHelper
{
    /**
     * Array of logic functions and priority
     *
     * @var array
     */
    protected $_logicList = [
        'LOWEST_RESULT'     => 10,
        'HIGHEST_RESULT'    => 20,
        'SUM'               => 30,
        'PERCENT'           => 40,
        'OVER_WEIGHT'       => 50,
        'FULL_WEIGHT'       => 60,
        'OVER_ITEM'         => 70,
        'EXTRA'             => 80,
        'ROUND'             => 90,
        'MIN'               => 100,
        'MAX'               => 110,
        'VARIANTS'          => 120,
    ];

    protected $_logicFinal = [
        'OVER_ITEM',
        'EXTRA',
        'ROUND',
        'MIN',
        'MAX',
    ];

    /**
     * @var array
     */
    protected $_roundDirection = ['up', 'down', 'normal'];

    /**
     * @param $rates
     * @param $item
     * @return array
     */
    public function calculateLogic($rates, $item)
    {
        $resultRates = [];
        foreach ($rates as $rate) {
            if ($rate['calc_logic'] == '*') {
                $resultRates[] = $rate;
                continue;
            }
            $logics = $this->parseLogic($rate['calc_logic']);
            $flag = 'override';
            $price = 0;
            $existFinal = false;
            $variants = false;
            foreach ($logics as $logic) {
                if (in_array(key($logic), $this->_logicFinal) && !$existFinal) {
                    $price += $rate['price'];
                    $existFinal = true;
                }
                $flag = $this->getFlag($logic, $flag);
                $price = $this->getResultLogic($price, $logic, $item, $rate, $flag);
                if (key($logic) === 'VARIANTS') {
                    $variants = $logic['VARIANTS'];
                }
            }
            if (!$existFinal) {
                $price += $rate['price'];
            }
            $rate['price'] = $price;
            $resultRates[] = $rate;
            if ($variants) {
                $types = explode(',', $variants);
                if (count($types) > 0) {
                    $copyData = $rate;
                    foreach ($types as $type) {
                        $copyData['delivery_method'] = $type;
                        $resultRates[] = $copyData;
                    }
                }
            }
        }
        
        return $resultRates;
    }

    /**
     * @param $logic
     * @param $flag
     * @return string
     */
    public function getFlag($logic, $flag)
    {
        switch (key($logic)) {
            case "LOWEST_RESULT":
                $flag = 'lowest';
                break;
            case "HIGHEST_RESULT":
                $flag = 'highest';
                break;
            case "SUM":
                $flag = 'sum';
                break;
        }

        return $flag;
    }

    /**
     * @param $price
     * @param $logic
     * @param $item
     * @param $rate
     * @param $flag
     * @return float|int
     */
    public function getResultLogic($price, $logic, $item, $rate, $flag)
    {
        switch (key($logic)) {
            case "PERCENT":
                if (isset($logic['PERCENT']) && is_numeric($logic['PERCENT']) && $logic['PERCENT'] > 0) {
                    $priceCalc = ($item['price'] * $logic['PERCENT'] / 100);
                    $price = $this->getEndResult($priceCalc, $price, $flag);
                }
                break;
            case "OVER_WEIGHT":
                $price = $this->calculateOverWeight($price, $logic, $item, $rate, $flag);
                break;
            case "FULL_WEIGHT":
                $price = $this->calculateFullWeight($price, $logic, $item, $rate, $flag);
                break;
            case "OVER_ITEM":
                $price = $this->calculateOverItem($price, $logic, $item, $rate, $flag);
                break;
            case "EXTRA":
                if (isset($logic['EXTRA']) && is_numeric($logic['EXTRA']) && $logic['EXTRA'] > 0) {
                    $price = $price + $logic['EXTRA'];
                }
                break;
            case "ROUND":
                $roundValues = explode(":", $logic['ROUND']);
                $precision = (
                    isset($roundValues[0]) && is_numeric($roundValues[0]) && $roundValues[0] > 0) ?
                    $roundValues[0] :
                    2;
                $direction = (
                    isset($roundValues[1]) && in_array($roundValues[1], $this->_roundDirection)) ?
                    $roundValues[1] :
                    'normal';
                $ending = (
                    isset($roundValues[2]) && is_numeric($roundValues[2]) && $roundValues[2] > 0) ?
                    $roundValues[2] :
                    0;
                if ($direction == 'up') {
                    $price = ceil($price);
                } elseif ($direction == 'down') {
                    $price = floor($price);
                } else {
                    $price = round($price, $precision);
                }
                if ($ending > 0) {
                    $base = ($price - floor($price)) > ($ending / 100) ? ceil($price) : floor($price);
                    $price = $base + $ending / 100;
                }
                break;
            case "MIN":
                $price = ($price < $logic['MIN']) ? $logic['MIN'] : $price;
                break;
            case "MAX":
                $price = ($price > $logic['MAX']) ? $logic['MAX'] : $price;
                break;
        }

        return $price;
    }

    /**
     * Calculation of OVER_WEIGHT formula
     *
     * OVER_WEIGHT ($fractional_weight_unit*$cost_increase_per_unit*$weight_round*$portion_round)
     * $fractional_weight_unit, type: float
     * $cost_increase_per_unit, type: float
     * $weight_round, value: weight_up|ceil|up|weight_down|floor|down
     * $portion_round, value: portion_up|ceil|up|portion_down|floor|down
     * @version 1.2.0
     *
     * @param $price
     * @param $logic
     * @param $item
     * @param $rate
     * @param $flag
     * @return int
     */
    public function calculateOverWeight($price, $logic, $item, $rate, $flag)
    {
        $weightIncrease = explode("*", $logic['OVER_WEIGHT']);
        if (!empty($weightIncrease) && count($weightIncrease) >= 2) {
            if ($rate['weight_from'] == -0.1) {
                $rate['weight_from'] = 0;
            }

            $weight = $item['weight'];
            if (isset($weightIncrease[2])) {
                switch (trim($weightIncrease[2])) {
                    case "weight_up":
                    case "ceil":
                    case "up":
                        $weight = ceil($weight);
                        break;
                    case "weight_down":
                    case "floor":
                    case "down":
                        $weight = floor($weight);
                        break;
                }
            }

            $weightDifference = $weight - $rate['weight_from'];
            $portion = $weightDifference / $weightIncrease[0];
            if (isset($weightIncrease[3])) {
                switch (trim($weightIncrease[3])) {
                    case "portion_up":
                    case "ceil":
                    case "up":
                        $portion = ceil($portion);
                        break;
                    case "portion_down":
                    case "floor":
                    case "down":
                        $portion = floor($portion);
                        break;
                }
            }

            $priceCalc = $weightIncrease[1] * $portion;
            $price = $this->getEndResult($priceCalc, $price, $flag);
        }

        return $price;
    }

    /**
     * Calculation of FULL_WEIGHT formula
     *
     * FULL_WEIGHT ($fractional_weight_unit*$cost_increase_per_unit*$weight_round*$portion_round)
     * $fractional_weight_unit, type: float
     * $cost_increase_per_unit, type: float
     * $weight_round, value: weight_up|ceil|up|weight_down|floor|down
     * $portion_round, value: portion_up|ceil|up|portion_down|floor|down
     * @version 1.2.0
     *
     * @param $price
     * @param $logic
     * @param $item
     * @param $rate
     * @param $flag
     * @return int
     */
    public function calculateFullWeight($price, $logic, $item, $rate, $flag)
    {
        $weightIncrease = explode("*", $logic['FULL_WEIGHT']);
        if (!empty($weightIncrease) && count($weightIncrease) >= 2) {
            $weight = $item['weight'];
            if (isset($weightIncrease[2])) {
                switch (trim($weightIncrease[2])) {
                    case "weight_up":
                    case "ceil":
                    case "up":
                        $weight = ceil($weight);
                        break;
                    case "weight_down":
                    case "floor":
                    case "down":
                        $weight = floor($weight);
                        break;
                }
            }

            $portion = $weight / $weightIncrease[0];
            if (isset($weightIncrease[3])) {
                switch (trim($weightIncrease[3])) {
                    case "portion_up":
                    case "ceil":
                    case "up":
                        $portion = ceil($portion);
                        break;
                    case "portion_down":
                    case "floor":
                    case "down":
                        $portion = floor($portion);
                        break;
                }
            }

            $priceCalc = $weightIncrease[1] * $portion;
            $price = $this->getEndResult($priceCalc, $price, $flag);
        }

        return $price;
    }

    /**
     * Calculation of OVER_ITEM formula
     *
     * OVER_ITEM ($fractional_quantity_unit*$cost_increase_per_unit*$portion_round)
     * $fractional_quantity_unit, type: float
     * $cost_increase_per_unit, type: float
     * $portion_round, value: portion_up|ceil|up|portion_down|floor|down
     * @version 1.2.0
     *
     * @param $price
     * @param $logic
     * @param $item
     * @param $rate
     * @param $flag
     * @return int
     */
    public function calculateOverItem($price, $logic, $item, $rate, $flag)
    {
        $qtyIncrease = explode("*", $logic['OVER_ITEM']);
        if (!empty($qtyIncrease) && count($qtyIncrease) >= 2) {
            $qty = $item['qty'] - $rate['qty_from'];

            $portion = $qty / $qtyIncrease[0];
            if (isset($qtyIncrease[2])) {
                switch (trim($qtyIncrease[2])) {
                    case "portion_up":
                    case "ceil":
                    case "up":
                        $portion = ceil($portion);
                        break;
                    case "portion_down":
                    case "floor":
                    case "down":
                        $portion = floor($portion);
                        break;
                }
            }

            $price = $price + $qtyIncrease[1] * $portion;
        }

        return $price;
    }

    /**
     * @param $data
     * @return array
     */
    public function parseLogic($data)
    {
        $data = trim($data);
        $logics = explode(";", $data);
        $logicArray = [];
        foreach ($logics as $logic) {
            if (preg_match('/^(\w*)\((.*)\)$/', $logic, $matches) === 1) {
                if (key_exists($matches[1], $this->_logicList)) {
                    $logicArray[] = [
                        $matches[1] => $matches[2]
                    ];
                }
            } elseif (preg_match('/^(\w*)$/', $logic, $matches) === 1) {
                if (key_exists($matches[1], $this->_logicList)) {
                    $logicArray[] = [
                        $matches[1] => ''
                    ];
                }
            }
        }
        if (!empty($logicArray)) {
            uasort($logicArray, [$this, 'sortLogicByPriority']);
        }

        return $logicArray;
    }

    /**
     * @param $a
     * @param $b
     * @return int
     */
    public function sortLogicByPriority($a, $b)
    {
        $priority = $this->_logicList;
        if ($priority[key($a)] == $priority[key($b)]) {
            return 0;
        }

        return ($priority[key($a)] > $priority[key($b)]) ? 1 : -1;
    }

    /**
     * @param $price
     * @param $priceBefore
     * @param string $flag
     * @return int
     */
    public function getEndResult($price, $priceBefore, $flag = "override")
    {
        $result = 0;
        $priceBefore = $priceBefore == 0 && $flag != "sum" ? $price : $priceBefore;
        switch ($flag) {
            case "override":
                $result = $price;
                break;
            case "lowest":
                $result = $price > $priceBefore ? $priceBefore : $price;
                break;
            case "highest":
                $result = $price > $priceBefore ? $price : $priceBefore;
                break;
            case "sum":
                $result = $price + $priceBefore;
                break;
        }
        
        return $result;
    }

    /**
     * @return array
     */
    public function getLogicList()
    {
        return $this->_logicList;
    }
}
