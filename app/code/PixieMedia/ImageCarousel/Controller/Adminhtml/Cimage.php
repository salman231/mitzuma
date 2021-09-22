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
namespace PixieMedia\ImageCarousel\Controller\Adminhtml;

abstract class Cimage extends \Magento\Backend\App\Action
{
    /**
     * Carousel Image Factory
     * 
     * @var \PixieMedia\ImageCarousel\Model\CimageFactory
     */
    protected $cimageFactory;

    /**
     * Core registry
     * 
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;


    /**
     * constructor
     * 
     * @param \PixieMedia\ImageCarousel\Model\CimageFactory $cimageFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \PixieMedia\ImageCarousel\Model\CimageFactory $cimageFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\App\Action\Context $context
    )
    {
        $this->cimageFactory         = $cimageFactory;
        $this->coreRegistry          = $coreRegistry;
		$resultRedirectFactory = $context->getResultRedirectFactory();
		$this->resultRedirectFactory = $resultRedirectFactory;
        parent::__construct($context);
    }

    /**
     * Init Carousel Image
     *
     * @return \PixieMedia\ImageCarousel\Model\Cimage
     */
    protected function initCimage()
    {
        $cimageId  = (int) $this->getRequest()->getParam('cimage_id');
        /** @var \PixieMedia\ImageCarousel\Model\Cimage $cimage */
        $cimage    = $this->cimageFactory->create();
        if ($cimageId) {
            $cimage->load($cimageId);
        }
        $this->coreRegistry->register('pixiemedia_imagecarousel_cimage', $cimage);
        return $cimage;
    }
}
