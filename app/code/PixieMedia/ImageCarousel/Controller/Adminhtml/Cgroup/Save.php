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

class Save extends \PixieMedia\ImageCarousel\Controller\Adminhtml\Cgroup
{
    

    /**
     * constructor
     * 
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \PixieMedia\ImageCarousel\Model\CgroupFactory $cgroupFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \PixieMedia\ImageCarousel\Model\CgroupFactory $cgroupFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\App\Action\Context $context
    )
    {
		$resultRedirectFactory = $context->getResultRedirectFactory();
        parent::__construct($cgroupFactory, $registry, $context);
    }

    /**
     * run the action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost('cgroup');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $data = $this->filterData($data);
            $cgroup = $this->initCgroup();
            $cgroup->setData($data);
            $this->_eventManager->dispatch(
                'pixiemedia_imagecarousel_cgroup_prepare_save',
                [
                    'cgroup' => $cgroup,
                    'request' => $this->getRequest()
                ]
            );
            try {
                $cgroup->save();
                $this->messageManager->addSuccess(__('The Carousel Group has been saved.'));
				$this->_objectManager->get('Magento\Backend\Model\Session')->setPixieMediaImageCarouselCgroupData(false);
                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath(
                        'pixiemedia_imagecarousel/*/edit',
                        [
                            'cgroup_id' => $cgroup->getId(),
                            '_current' => true
                        ]
                    );
                    return $resultRedirect;
                }
                $resultRedirect->setPath('pixiemedia_imagecarousel/*/');
                return $resultRedirect;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Carousel Group.'));
            }
            $this->_getSession()->setPixieMediaImageCarouselCgroupData($data);
            $resultRedirect->setPath(
                'pixiemedia_imagecarousel/*/edit',
                [
                    'cgroup_id' => $cgroup->getId(),
                    '_current' => true
                ]
            );
            return $resultRedirect;
        }
        $resultRedirect->setPath('pixiemedia_imagecarousel/*/');
        return $resultRedirect;
    }

    /**
     * filter values
     *
     * @param array $data
     * @return array
     */
    protected function filterData($data)
    {
        if (isset($data['slidestoshow'])) {
            if (is_array($data['slidestoshow'])) {
                $data['slidestoshow'] = implode(',', $data['slidestoshow']);
            }
        }
        return $data;
    }
}
