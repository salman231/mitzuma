<?php

namespace Mageside\ShippingMatrixRates\Test\Unit\Helper;

class MergeResultHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Mageside\ShippingMatrixRates\Helper\MergeResultHelper
     */
    protected $_merger;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_merger = $objectManager->getObject('Mageside\ShippingMatrixRates\Helper\MergeResultHelper');
    }

    /**
     * Test for _mergeResults method
     */
    public function testMergeResults()
    {
        $before = [
            ['delivery_method' => 'test1', 'price' => '10', 'cost' => '10'],
            ['delivery_method' => 'test2', 'price' => '25', 'cost' => '25'],
            ['delivery_method' => 'test4', 'price' => '40', 'cost' => '40'],
            ['delivery_method' => 'test5', 'price' => '50', 'cost' => '50'],
        ];
        $new = [
            ['delivery_method' => 'test1', 'price' => '15', 'cost' => '15'],
            ['delivery_method' => 'test1', 'price' => '35', 'cost' => '35'],
            ['delivery_method' => 'test1', 'price' => '25', 'cost' => '25'],
            ['delivery_method' => 'test2', 'price' => '10', 'cost' => '10'],
            ['delivery_method' => 'test2', 'price' => '15', 'cost' => '15'],
            ['delivery_method' => 'test3', 'price' => '40', 'cost' => '40'],
        ];

        $expectedSumFirstFirst = [
            ['delivery_method' => 'test1', 'price' => '15', 'cost' => '15'],
            ['delivery_method' => 'test2', 'price' => '10', 'cost' => '10'],
            ['delivery_method' => 'test3', 'price' => '40', 'cost' => '40'],
        ];
        $expectedSumFirst = [
            ['delivery_method' => 'test1', 'price' => '25', 'cost' => '25'],
            ['delivery_method' => 'test2', 'price' => '35', 'cost' => '35'],
        ];
        $expectedSumLastFirst = [
            ['delivery_method' => 'test1', 'price' => '25', 'cost' => '25'],
            ['delivery_method' => 'test2', 'price' => '15', 'cost' => '15'],
            ['delivery_method' => 'test3', 'price' => '40', 'cost' => '40'],
        ];
        $expectedSumLast = [
            ['delivery_method' => 'test1', 'price' => '35', 'cost' => '35'],
            ['delivery_method' => 'test2', 'price' => '40', 'cost' => '40'],
        ];
        $expectedHighestFirst = [
            ['delivery_method' => 'test1', 'price' => '35', 'cost' => '35'],
            ['delivery_method' => 'test2', 'price' => '15', 'cost' => '15'],
            ['delivery_method' => 'test3', 'price' => '40', 'cost' => '40'],
        ];
        $expectedHighest = [
            ['delivery_method' => 'test1', 'price' => '35', 'cost' => '35'],
            ['delivery_method' => 'test2', 'price' => '25', 'cost' => '25'],
        ];

        $resultSumFirstFirst = $this->_merger->_mergeResults($new, [], 'sum_first');
        $resultSumFirst = $this->_merger->_mergeResults($new, $before, 'sum_first');
        $resultSumLastFirst = $this->_merger->_mergeResults($new, [], 'sum_last');
        $resultSumLast = $this->_merger->_mergeResults($new, $before, 'sum_last');
        $resultHighestFirst = $this->_merger->_mergeResults($new, [], 'highest');
        $resultHighest = $this->_merger->_mergeResults($new, $before, 'highest');

        $methods = ['SumFirstFirst', 'SumFirst', 'SumLastFirst', 'SumLast', 'HighestFirst', 'Highest'];

        foreach ($methods as $method) {
            $expected = 'expected' . $method;
            $result = 'result' . $method;
            $this->assertSameSize($$expected, $$result);
            foreach ($$result as $key => $row) {
                $this->assertEquals($$expected[$key]['delivery_method'], $row['delivery_method']);
                $this->assertEquals($$expected[$key]['price'], $row['price']);
                $this->assertEquals($$expected[$key]['cost'], $row['cost']);
            }
        }
    }

    /**
     * Test for _multipleResult method
     */
    public function testMultipleResult()
    {
        $data = [
            ['delivery_method' => 'test1', 'price' => '10', 'cost' => '10'],
            ['delivery_method' => 'test2', 'price' => '25', 'cost' => '25'],
            ['delivery_method' => 'test4', 'price' => '40', 'cost' => '40'],
            ['delivery_method' => 'test5', 'price' => '50', 'cost' => '50'],
        ];

        $qty = 2;
        $result = $this->_merger->_multipleResult($data, $qty);

        $this->assertSameSize($data, $result);
        foreach ($result as $key => $item) {
            $this->assertEquals($data[$key]['price'] * $qty, $item['price']);
            $this->assertEquals($data[$key]['cost'] * $qty, $item['cost']);
        }
    }
}
