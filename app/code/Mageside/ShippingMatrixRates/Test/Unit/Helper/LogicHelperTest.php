<?php

namespace Mageside\ShippingMatrixRates\Test\Unit\Helper;

class LogicHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Mageside\ShippingMatrixRates\Helper\LogicHelper
     */
    protected $_logic;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_logic = $objectManager->getObject('Mageside\ShippingMatrixRates\Helper\LogicHelper');
    }

    /**
     * Test for getEndResult method
     *
     * @param $price
     * @param $priceBefore
     * @param $flag
     * @param $expected
     * @dataProvider additionProvider
     */
    public function testGetEndResult($price, $priceBefore, $flag, $expected)
    {
        $this->assertEquals($expected, $this->_logic->getEndResult($price, $priceBefore, $flag));
    }

    /**
     * Data provider for testGetEndResult
     *
     * @return array
     */
    public function additionProvider()
    {
        return [
            [1, 2, "override", 1],
            [1, 2, "lowest", 1],
            [1, 2, "highest", 2],
            [1, 2, "sum", 3]
        ];
    }

    /**
     * Test for parseLogic method
     */
    public function testParseLogic()
    {
        $logicArray = $this->_logic
            ->parseLogic(
                "FULL_WEIGHT(0.1*1);EXTRA(5);BLA_BLA_LOGIC(2/9);LOWEST_RESULT;PERCENT(10);MAX(10);SUM();".
                "OVER_WEIGHT(0.1*2);ROUND(2:up:99);HIGHEST_RESULT;MIN(3);EXTRA_ITEM(5*10);"
            );

        $i = 1;
        foreach ($logicArray as $logic) {
            $item = $this->_logic->getLogicList();
            $this->assertEquals($item[key($logic)], $i * 10);
            $i++;
        }

        $this->assertEquals(count($logicArray), 11);
    }

    /**
     * Test for calculateLogic method
     *
     * @param $rates
     * @param $expected
     * @dataProvider calcProvider
     */
    public function testCalcLogic($rates, $expected)
    {
        $item = [
            'price' => '100',
            'weight' => '9.5',
            'qty' => '16'
        ];

        $result = $this->_logic->calculateLogic($rates, $item);
        $this->assertEquals($expected, $result[0]['price']);
    }

    /**
     * Test for calculateLogic method
     *
     * @param $rates
     * @param $expected
     * @dataProvider calcProvider2
     */
    public function testCalcLogic2($rates, $expected)
    {
        $item = [
            'price' => '14',
            'weight' => '1.2',
            'qty' => '7'
        ];

        $result = $this->_logic->calculateLogic($rates, $item);
        $this->assertEquals($expected, $result[0]['price']);
    }

    /**
     * Data provider for testCalcLogic
     *
     * @return array
     */
    public function calcProvider()
    {
        return [
            [[['calc_logic' => 'LOWEST_RESULT', 'weight_from' => '-0.1', 'price' => '11']], '11'],
            [[['calc_logic' => 'HIGHEST_RESULT', 'weight_from' => '-0.1', 'price' => '12']], '12'],
            [[['calc_logic' => 'SUM', 'weight_from' => '-0.1', 'price' => '15']], '15'],
            [[['calc_logic' => 'PERCENT(10)', 'weight_from' => '-0.1', 'price' => '5']], '15'],
            [[['calc_logic' => 'OVER_WEIGHT(0.1*2*ceil)', 'weight_from' => '2', 'price' => '50']], '210'],
            [[['calc_logic' => 'OVER_WEIGHT(0.1*2)', 'weight_from' => '2', 'price' => '50']], '200'],
            [[['calc_logic' => 'FULL_WEIGHT(0.1*1*ceil)', 'weight_from' => '-0.1', 'price' => '50']], '150'],
            [[['calc_logic' => 'FULL_WEIGHT(0.1*1)', 'weight_from' => '-0.1', 'price' => '50']], '145'],
            [[['calc_logic' => 'EXTRA(5)', 'weight_from' => '-0.1', 'price' => '10']], '15'],
            [[['calc_logic' => 'EXTRA_ITEM(5*2)', 'weight_from' => '-0.1', 'price' => '0', 'qty_from' => '10']], '4'],
            [[['calc_logic' => 'EXTRA(2.1589);ROUND(2:up:99)', 'weight_from' => '-0.1', 'price' => '0']], '3.99'],
            [[['calc_logic' => 'EXTRA(2.1589);ROUND(2:down:99)', 'weight_from' => '-0.1', 'price' => '0']], '2.99'],
            [[['calc_logic' => 'EXTRA(2.1589);ROUND(2:normal:77)', 'weight_from' => '-0.1', 'price' => '0']], '2.77'],
            [[['calc_logic' => 'EXTRA(2.8589);ROUND(2:normal:77)', 'weight_from' => '-0.1', 'price' => '0']], '3.77'],
            [[['calc_logic' => 'MIN(10)', 'weight_from' => '-0.1', 'price' => '0']], '10'],
            [[['calc_logic' => 'EXTRA(30);MAX(20)', 'weight_from' => '-0.1', 'price' => '0']], '20'],
            [[['calc_logic' => 'OVER_WEIGHT(0.1*2);LOWEST_RESULT;FULL_WEIGHT(0.1*1)', 'weight_from' => '2', 'price' => '50']], '145'],
            [[['calc_logic' => 'HIGHEST_RESULT;OVER_WEIGHT(0.1*2);LOWEST_RESULT;FULL_WEIGHT(0.1*1)', 'weight_from' => '2', 'price' => '50']], '200'],
            [[['calc_logic' => 'SUM;HIGHEST_RESULT;OVER_WEIGHT(0.1*2);LOWEST_RESULT;FULL_WEIGHT(0.1*1)', 'weight_from' => '2', 'price' => '50']], '295'],
            [[['calc_logic' => 'EXTRA(5);PERCENT(10);SUM;HIGHEST_RESULT;OVER_WEIGHT(0.1*2);LOWEST_RESULT;FULL_WEIGHT(0.1*1)', 'weight_from' => '2', 'price' => '50']], '310'],
        ];
    }

    public function calcProvider2()
    {
        return [
            [
                [
                    [
                        'calc_logic'    => 'LOWEST_RESULT;PERCENT(10);OVER_WEIGHT(0.1*2);FULL_WEIGHT(0.1*2);OVER_WEIGHT(0.1*2*ceil);FULL_WEIGHT(0.1*2*ceil);',
                        'qty_from'      => '2',
                        'weight_from'   => '1',
                        'price'         => '15'
                    ]
                ],
                '16.4'
            ],
            [
                [
                    [
                        'calc_logic'    => 'HIGHEST_RESULT;PERCENT(10);OVER_WEIGHT(0.1*2);FULL_WEIGHT(0.1*2);OVER_WEIGHT(0.1*2*ceil);FULL_WEIGHT(0.1*2*ceil);',
                        'qty_from'      => '2',
                        'weight_from'   => '1',
                        'price'         => '15'
                    ]
                ],
                '55'
            ],
            [
                [
                    [
                        'calc_logic'    => 'SUM;PERCENT(10);OVER_WEIGHT(0.1*2);FULL_WEIGHT(0.1*2);OVER_WEIGHT(0.1*2*ceil);FULL_WEIGHT(0.1*2*ceil);',
                        'qty_from'      => '2',
                        'weight_from'   => '1',
                        'price'         => '15'
                    ]
                ],
                '104.4'
            ],
            [
                [
                    [
                        'calc_logic'    => 'EXTRA_ITEM(3*5)',
                        'qty_from'      => '2',
                        'weight_from'   => '1',
                        'price'         => '0'
                    ]
                ],
                '10'
            ],
            [
                [
                    [
                        'calc_logic'    => 'EXTRA(25)',
                        'qty_from'      => '2',
                        'weight_from'   => '1',
                        'price'         => '0'
                    ]
                ],
                '25'
            ],
            [
                [
                    [
                        'calc_logic'    => 'SUM;PERCENT(10);OVER_WEIGHT(0.1*2);FULL_WEIGHT(0.1*2);OVER_WEIGHT(0.1*2*ceil);FULL_WEIGHT(0.1*2*ceil);EXTRA_ITEM(3*5);EXTRA(25);',
                        'qty_from'      => '2',
                        'weight_from'   => '1',
                        'price'         => '15'
                    ]
                ],
                '139.4'
            ],
            [
                [
                    [
                        'calc_logic'    => 'SUM;PERCENT(10);OVER_WEIGHT(0.1*2);FULL_WEIGHT(0.1*2);OVER_WEIGHT(0.1*2*ceil);FULL_WEIGHT(0.1*2*ceil);EXTRA_ITEM(3*5);EXTRA(25);ROUND(2:down:77);',
                        'qty_from'      => '2',
                        'weight_from'   => '1',
                        'price'         => '15'
                    ]
                ],
                '139.77'
            ],
            [
                [
                    [
                        'calc_logic'    => 'SUM;PERCENT(10);OVER_WEIGHT(0.1*2);FULL_WEIGHT(0.1*2);OVER_WEIGHT(0.1*2*ceil);FULL_WEIGHT(0.1*2*ceil);EXTRA_ITEM(3*5);EXTRA(25);ROUND(2:down:77);MIN(60);MAX(120);',
                        'qty_from'      => '2',
                        'weight_from'   => '1',
                        'price'         => '15'
                    ]
                ],
                '120'
            ],
            [
                [
                    [
                        'calc_logic'    => 'LOWEST_RESULT;PERCENT(10);OVER_WEIGHT(0.1*2);FULL_WEIGHT(0.1*2);OVER_WEIGHT(0.1*2*ceil);FULL_WEIGHT(0.1*2*ceil);EXTRA_ITEM(3*5);EXTRA(25);ROUND(2:down:77);MIN(60);MAX(120);',
                        'qty_from'      => '2',
                        'weight_from'   => '1',
                        'price'         => '15'
                    ]
                ],
                '60'
            ],
            [
                [
                    [
                        'calc_logic'    => 'LOWEST_RESULT;PERCENT(10);OVER_WEIGHT(0.1*2);FULL_WEIGHT(0.1*2);OVER_WEIGHT(0.1*2*ceil);FULL_WEIGHT(0.1*2*ceil);EXTRA_ITEM(3*5);EXTRA(25);ROUND(2:up:99);',
                        'qty_from'      => '2',
                        'weight_from'   => '1',
                        'price'         => '15'
                    ]
                ],
                '52.99'
            ],
        ];
    }
}
