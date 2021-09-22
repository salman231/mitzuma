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
namespace PixieMedia\ImageCarousel\Block;
use Magento\Framework\View\Element\Template\Context;

class Carousel extends \Magento\Framework\View\Element\Template
{
    
    //protected $_filesystem;
    protected $_imageFactory;
  
	  public function __construct(
	    Context $context,
        \Magento\Framework\Image\AdapterFactory $imageFactory
		
    )
    {
        parent::__construct($context);
        $this->_imageFactory = $imageFactory;
		
    }
	
	public function getGroup($cid) {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
		$group = $objectManager->create('PixieMedia\ImageCarousel\Model\Cgroup')->load($cid);
		if($group) { return $group; }
		return false;
	}
	
	
	public function carouselimages($cid) {
		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
		$imageCollection = $objectManager->create('PixieMedia\ImageCarousel\Model\ResourceModel\Cimage\Collection')
					->addFieldToFilter('cgroup_id',$cid)
					->addFieldToFilter('status',1)
	    			->setOrder('sort', 'ASC');
		return $imageCollection; 
	}

    // pass imagename, width and height
    public function resize($image, $width = null, $height = null)
    {
        $absolutePath = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('pixiemedia/imagecarousel/cimage/image').$image;

        $imageResized = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('resized/'.$width.'/').$image;         
        //create image factory...
        $imageResize = $this->_imageFactory->create();         
        $imageResize->open($absolutePath);
        $imageResize->constrainOnly(false);         
        $imageResize->keepTransparency(TRUE);         
        $imageResize->keepFrame(TRUE);         
        $imageResize->keepAspectRatio(TRUE); 
		if($width && $height) {
        	$imageResize->resize($width,$height);  
		}
        //destination folder                
        $destination = $imageResized ;    
        //save image      
        $imageResize->save($destination);         

        $resizedURL = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'resized/'.$width.''.$image;
        return $resizedURL;
  } 
  
	
	
}