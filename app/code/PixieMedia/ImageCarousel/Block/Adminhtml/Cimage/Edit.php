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
namespace PixieMedia\ImageCarousel\Block\Adminhtml\Cimage;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     * 
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * constructor
     * 
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize Carousel Image edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'cimage_id';
        $this->_blockGroup = 'PixieMedia_ImageCarousel';
        $this->_controller = 'adminhtml_cimage';
        parent::_construct();
        $this->buttonList->update('save', 'label', __('Save Carousel Image'));
        $this->buttonList->add(
            'save-and-continue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'saveAndContinueEdit',
                            'target' => '#edit_form'
                        ]
                    ]
                ]
            ],
            -100
        );
        $this->buttonList->update('delete', 'label', __('Delete Carousel Image'));
    }
    /**
     * Retrieve text for header element depending on loaded Carousel Image
     *
     * @return string
     */
    public function getHeaderText()
    {
        /** @var \PixieMedia\ImageCarousel\Model\Cimage $cimage */
        $cimage = $this->coreRegistry->registry('pixiemedia_imagecarousel_cimage');
        if ($cimage->getId()) {
            return __("Edit Carousel Image '%1'", $this->escapeHtml($cimage->getName()));
        }
        return __('New Carousel Image');
    }
}
