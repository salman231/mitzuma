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

class Delete extends \PixieMedia\ImageCarousel\Controller\Adminhtml\Cimage
{
    /**
     * execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('cimage_id');
        if ($id) {
            $name = "";
            try {
                /** @var \PixieMedia\ImageCarousel\Model\Cimage $cimage */
                $cimage = $this->cimageFactory->create();
                $cimage->load($id);
                $name = $cimage->getName();
                $cimage->delete();
                $this->messageManager->addSuccess(__('The Carousel Image has been deleted.'));
                $this->_eventManager->dispatch(
                    'adminhtml_pixiemedia_imagecarousel_cimage_on_delete',
                    ['name' => $name, 'status' => 'success']
                );
                $resultRedirect->setPath('pixiemedia_imagecarousel/*/');
                return $resultRedirect;
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_pixiemedia_imagecarousel_cimage_on_delete',
                    ['name' => $name, 'status' => 'fail']
                );
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                $resultRedirect->setPath('pixiemedia_imagecarousel/*/edit', ['cimage_id' => $id]);
                return $resultRedirect;
            }
        }
        // display error message
        $this->messageManager->addError(__('Carousel Image to delete was not found.'));
        // go to grid
        $resultRedirect->setPath('pixiemedia_imagecarousel/*/');
        return $resultRedirect;
    }
}
