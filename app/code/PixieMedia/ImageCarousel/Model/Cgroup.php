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
 * @method Cgroup setLabel($label)
 * @method Cgroup setImagewidth($imagewidth)
 * @method Cgroup setImageheight($imageheight)
 * @method Cgroup setInfinite($infinite)
 * @method Cgroup setSlidestoshow($slidestoshow)
 * @method Cgroup setSlidestoscroll($slidestoscroll)
 * @method Cgroup setBreakpointone($breakpointone)
 * @method Cgroup setResoneslidestoshow($resoneslidestoshow)
 * @method Cgroup setResoneslidestoscroll($resoneslidestoscroll)
 * @method Cgroup setBreakpointtwo($breakpointtwo)
 * @method Cgroup setRestwoslidestoshow($restwoslidestoshow)
 * @method Cgroup setRestwoslidestoscroll($restwoslidestoscroll)
 * @method Cgroup setBreakpointthree($breakpointthree)
 * @method Cgroup setResthreeslidestoshow($resthreeslidestoshow)
 * @method Cgroup setResthreeslidestoscroll($resthreeslidestoscroll)
 * @method mixed getLabel()
 * @method mixed getImagewidth()
 * @method mixed getImageheight()
 * @method mixed getInfinite()
 * @method mixed getSlidestoshow()
 * @method mixed getSlidestoscroll()
 * @method mixed getBreakpointone()
 * @method mixed getResoneslidestoshow()
 * @method mixed getResoneslidestoscroll()
 * @method mixed getBreakpointtwo()
 * @method mixed getRestwoslidestoshow()
 * @method mixed getRestwoslidestoscroll()
 * @method mixed getBreakpointthree()
 * @method mixed getResthreeslidestoshow()
 * @method mixed getResthreeslidestoscroll()
 * @method Cgroup setCreatedAt(\string $createdAt)
 * @method string getCreatedAt()
 * @method Cgroup setUpdatedAt(\string $updatedAt)
 * @method string getUpdatedAt()
 */
class Cgroup extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Cache tag
     * 
     * @var string
     */
    const CACHE_TAG = 'pixiemedia_imagecarousel_cgroup';

    /**
     * Cache tag
     * 
     * @var string
     */
    protected $_cacheTag = 'pixiemedia_imagecarousel_cgroup';

    /**
     * Event prefix
     * 
     * @var string
     */
    protected $_eventPrefix = 'pixiemedia_imagecarousel_cgroup';


    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('PixieMedia\ImageCarousel\Model\ResourceModel\Cgroup');
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
        $values['imagewidth'] = '170';
        $values['imageheight'] = '170';
        $values['infinite'] = '1';
        $values['slidestoshow'] = '6';
        $values['slidestoscroll'] = '2';
        $values['breakpointone'] = '1200';
        $values['resoneslidestoshow'] = '4';
        $values['resoneslidestoscroll'] = '2';
        $values['breakpointtwo'] = '600';
        $values['restwoslidestoshow'] = '3';
        $values['restwoslidestoscroll'] = '2';
        $values['breakpointthree'] = '480';
        $values['resthreeslidestoshow'] = '2';
        $values['resthreeslidestoscroll'] = '1';
        return $values;
    }
}
