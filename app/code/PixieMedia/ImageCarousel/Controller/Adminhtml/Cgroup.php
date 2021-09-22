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

abstract class Cgroup extends \Magento\Backend\App\Action
{
    /**
     * Carousel Group Factory
     * 
     * @var \PixieMedia\ImageCarousel\Model\CgroupFactory
     */
    protected $cgroupFactory;

    /**
     * Core registry
     * 
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Result redirect factory
     * 
     * @var \Magento\Backend\Model\View\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * constructor
     * 
     * @param \PixieMedia\ImageCarousel\Model\CgroupFactory $cgroupFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \PixieMedia\ImageCarousel\Model\CgroupFactory $cgroupFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\App\Action\Context $context
    )
    {
        $this->cgroupFactory         = $cgroupFactory;
        $this->coreRegistry          = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Init Carousel Group
     *
     * @return \PixieMedia\ImageCarousel\Model\Cgroup
     */
    protected function initCgroup()
    {
        $cgroupId  = (int) $this->getRequest()->getParam('cgroup_id');
        /** @var \PixieMedia\ImageCarousel\Model\Cgroup $cgroup */
        $cgroup    = $this->cgroupFactory->create();
        if ($cgroupId) {
            $cgroup->load($cgroupId);
        }
        $this->coreRegistry->register('pixiemedia_imagecarousel_cgroup', $cgroup);
        return $cgroup;
    }
}
