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

class Save extends \PixieMedia\ImageCarousel\Controller\Adminhtml\Cimage
{
    /**
     * Upload model
     * 
     * @var \PixieMedia\ImageCarousel\Model\Upload
     */
    protected $uploadModel;

    /**
     * Image model
     * 
     * @var \PixieMedia\ImageCarousel\Model\Cimage\Image
     */
    protected $imageModel;


    /**
     * constructor
     * 
     * @param \PixieMedia\ImageCarousel\Model\Upload $uploadModel
     * @param \PixieMedia\ImageCarousel\Model\Cimage\Image $imageModel
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \PixieMedia\ImageCarousel\Model\CimageFactory $cimageFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \PixieMedia\ImageCarousel\Model\Upload $uploadModel,
        \PixieMedia\ImageCarousel\Model\Cimage\Image $imageModel,
        \PixieMedia\ImageCarousel\Model\CimageFactory $cimageFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\App\Action\Context $context
    )
    {
        $this->uploadModel    = $uploadModel;
        $this->imageModel     = $imageModel;
		$resultRedirectFactory = $context->getResultRedirectFactory();
		
        parent::__construct($cimageFactory, $registry, $context);
    }

    /**
     * run the action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost('cimage');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $cimage = $this->initCimage();
            $cimage->setData($data);
            $image = $this->uploadModel->uploadFileAndGetName('image', $this->imageModel->getBaseDir(), $data);
            $cimage->setImage($image);
            $this->_eventManager->dispatch(
                'pixiemedia_imagecarousel_cimage_prepare_save',
                [
                    'cimage' => $cimage,
                    'request' => $this->getRequest()
                ]
            );
            try {
                $cimage->save();
                $this->messageManager->addSuccess(__('The Carousel Image has been saved.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPixieMediaImageCarouselCimageData(false);
                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath(
                        'pixiemedia_imagecarousel/*/edit',
                        [
                            'cimage_id' => $cimage->getId(),
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
                $this->messageManager->addException($e, __('Something went wrong while saving the Carousel Image.'));
            }
            $this->_getSession()->setPixieMediaImageCarouselCimageData($data);
            $resultRedirect->setPath(
                'pixiemedia_imagecarousel/*/edit',
                [
                    'cimage_id' => $cimage->getId(),
                    '_current' => true
                ]
            );
            return $resultRedirect;
        }
        $resultRedirect->setPath('pixiemedia_imagecarousel/*/');
        return $resultRedirect;
    }
}
