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
namespace PixieMedia\ImageCarousel\Model\Source;

class Cgroup implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Carousel Group Collection factory
     * 
     * @var \PixieMedia\ImageCarousel\Model\ResourceModel\Cgroup\CollectionFactory
     */
    protected $cgroupCollectionFactory;

    /**
     * constructor
     * 
     * @param \PixieMedia\ImageCarousel\Model\ResourceModel\Cgroup\CollectionFactory $cgroupCollectionFactory
     */
    public function __construct(
        \PixieMedia\ImageCarousel\Model\ResourceModel\Cgroup\CollectionFactory $cgroupCollectionFactory
    )
    {
        $this->cgroupCollectionFactory = $cgroupCollectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        /** @var \PixieMedia\ImageCarousel\Model\ResourceModel\Cgroup\Collection $collection */
        $collection = $this->cgroupCollectionFactory->create();
        return $collection->toOptionArray();
    }
}
