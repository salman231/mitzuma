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
namespace PixieMedia\ImageCarousel\Model;

/**
 * @method Cimage setName($name)
 * @method Cimage setImage($image)
 * @method Cimage setLink($link)
 * @method Cimage setSort($sort)
 * @method Cimage setStatus($status)
 * @method mixed getName()
 * @method mixed getImage()
 * @method mixed getLink()
 * @method mixed getSort()
 * @method mixed getStatus()
 * @method Cimage setCreatedAt(\string $createdAt)
 * @method string getCreatedAt()
 * @method Cimage setUpdatedAt(\string $updatedAt)
 * @method string getUpdatedAt()
 */
class Cimage extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Cache tag
     * 
     * @var string
     */
    const CACHE_TAG = 'pixiemedia_imagecarousel_cimage';

    /**
     * Cache tag
     * 
     * @var string
     */
    protected $_cacheTag = 'pixiemedia_imagecarousel_cimage';

    /**
     * Event prefix
     * 
     * @var string
     */
    protected $_eventPrefix = 'pixiemedia_imagecarousel_cimage';


    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('PixieMedia\ImageCarousel\Model\ResourceModel\Cimage');
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * get entity default values
     *
     * @return array
     */
    public function getDefaultValues()
    {
        $values = [];

        return $values;
    }
}
