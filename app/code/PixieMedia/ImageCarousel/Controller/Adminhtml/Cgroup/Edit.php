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
namespace PixieMedia\ImageCarousel\Controller\Adminhtml\Cgroup;

class Edit extends \PixieMedia\ImageCarousel\Controller\Adminhtml\Cgroup
{
    /**
     * Backend session
     * 
     * @var \Magento\Backend\Model\Session
     */
    //protected $backendSession;

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
     * @param \PixieMedia\ImageCarousel\Model\CgroupFactory $cgroupFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \PixieMedia\ImageCarousel\Model\CgroupFactory $cgroupFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\App\Action\Context $context
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
		$resultRedirectFactory = $context->getResultRedirectFactory();
        parent::__construct($cgroupFactory, $registry, $context);
    }

    /**
     * is action allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('PixieMedia_ImageCarousel::cgroup');
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('cgroup_id');
        /** @var \PixieMedia\ImageCarousel\Model\Cgroup $cgroup */
        $cgroup = $this->initCgroup();
        /** @var \Magento\Backend\Model\View\Result\Page|\Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('PixieMedia_ImageCarousel::cgroup');
        $resultPage->getConfig()->getTitle()->set(__('Carousel Groups'));
        if ($id) {
            $cgroup->load($id);
            if (!$cgroup->getId()) {
                $this->messageManager->addError(__('This Carousel Group no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath(
                    'pixiemedia_imagecarousel/*/edit',
                    [
                        'cgroup_id' => $cgroup->getId(),
                        '_current' => true
                    ]
                );
                return $resultRedirect;
            }
        }
        $title = $cgroup->getId() ? $cgroup->getLabel() : __('New Carousel Group');
        $resultPage->getConfig()->getTitle()->prepend($title);
		$data = $this->_objectManager->get('Magento\Backend\Model\Session')->getData('pixiemedia_imagecarousel_cgroup_data', true);
        if (!empty($data)) {
            $cgroup->setData($data);
        }
        return $resultPage;
    }
}
