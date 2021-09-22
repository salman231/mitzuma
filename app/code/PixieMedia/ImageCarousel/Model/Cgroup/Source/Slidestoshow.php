<?php
/**
 * PixieMedia_ImageCarousel extension
 *                     NOTICE OF LICENSE
 * 
 *                     This source file is subject to the MIT License
 *                     that is bundled with this package in the file LICENSE.txt.
 *                     It is also available through the world-wide-web at this URL:
 *                     http://opensource.org/licenses/mit-license.php
 * 
 *                     @category  PixieMedia
 *                     @package   PixieMedia_ImageCarousel
 *                     @copyright Copyright (c) 2017
 *                     @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace PixieMedia\ImageCarousel\Model\Cgroup\Source;

class Slidestoshow implements \Magento\Framework\Option\ArrayInterface
{
    const _1 = 1;
    const _2 = 2;
    const _3 = 3;
    const _4 = 4;
    const _5 = 5;
    const _6 = 6;
    const _7 = 7;
    const _8 = 8;
    const _9 = 9;
    const _10 = 10;
    const _11 = 11;
    const _12 = 12;
    const _13 = 13;
    const _14 = 14;
    const _15 = 15;


    /**
     * to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => self::_1,
                'label' => __('1')
            ],
            [
                'value' => self::_2,
                'label' => __('2')
            ],
            [
                'value' => self::_3,
                'label' => __('3')
            ],
            [
                'value' => self::_4,
                'label' => __('4')
            ],
            [
                'value' => self::_5,
                'label' => __('5')
            ],
            [
                'value' => self::_6,
                'label' => __('6')
            ],
            [
                'value' => self::_7,
                'label' => __('7')
            ],
            [
                'value' => self::_8,
                'label' => __('8')
            ],
            [
                'value' => self::_9,
                'label' => __('9')
            ],
            [
                'value' => self::_10,
                'label' => __('10')
            ],
            [
                'value' => self::_11,
                'label' => __('11')
            ],
            [
                'value' => self::_12,
                'label' => __('12')
            ],
            [
                'value' => self::_13,
                'label' => __('13')
            ],
            [
                'value' => self::_14,
                'label' => __('14')
            ],
            [
                'value' => self::_15,
                'label' => __('15')
            ],
        ];
        return $options;

    }
}
