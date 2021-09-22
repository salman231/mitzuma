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

class Restwoslidestoscroll implements \Magento\Framework\Option\ArrayInterface
{
    const _1 = 1;
    const _2 = 2;
    const _3 = 3;
    const _4 = 4;
    const _5 = 5;
    const _6 = 6;


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
        ];
        return $options;

    }
}
