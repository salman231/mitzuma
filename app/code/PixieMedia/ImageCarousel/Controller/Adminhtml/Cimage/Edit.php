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
namespace PixieMedia\ImageCarousel\Controller\Adminhtml\Cimage;

class Edit extends \PixieMedia\ImageCarousel\Controller\Adminhtml\Cimage
{
    /**
     * Backend session
     * 
     * @var \Magento\Backend\Model\Session
     */
    

    /**
     * Page factory
     * 
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Result JSON factory
     * 
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * constructor
     * 
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \PixieMedia\ImageCarousel\Model\CimageFactory $cimageFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \PixieMedia\ImageCarousel\Model\CimageFactory $cimageFactory,
        \Magento\Framework\Registry $registry,
        //\Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Backend\App\Action\Context $context
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
		$resultRedirectFactory = $context->getResultRedirectFactory();
        parent::__construct($cimageFactory, $registry, $context);
    }

    /**
     * is action allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('PixieMedia_ImageCarousel::cimage');
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('cimage_id');
        /** @var \PixieMedia\ImageCarousel\Model\Cimage $cimage */
        $cimage = $this->initCimage();
        /** @var \Magento\Backend\Model\View\Result\Page|\Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('PixieMedia_ImageCarousel::cimage');
        $resultPage->getConfig()->getTitle()->set(__('Carousel Images'));
        if ($id) {
            $cimage->load($id);
            if (!$cimage->getId()) {
                $this->messageManager->addError(__('This Carousel Image no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath(
                    'pixiemedia_imagecarousel/*/edit',
                    [
                        'cimage_id' => $cimage->getId(),
                        '_current' => true
                    ]
                );
                return $resultRedirect;
            }
        }
        $title = $cimage->getId() ? $cimage->getName() : __('New Carousel Image');
        $resultPage->getConfig()->getTitle()->prepend($title);
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getData('pixiemedia_imagecarousel_cimage_data', true);
        if (!empty($data)) {
            $cimage->setData($data);
        }
        return $resultPage;
    }
}
