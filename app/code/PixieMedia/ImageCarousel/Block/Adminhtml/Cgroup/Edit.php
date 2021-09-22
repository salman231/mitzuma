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
namespace PixieMedia\ImageCarousel\Block\Adminhtml\Cgroup;

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
     * Initialize Carousel Group edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'cgroup_id';
        $this->_blockGroup = 'PixieMedia_ImageCarousel';
        $this->_controller = 'adminhtml_cgroup';
        parent::_construct();
        $this->buttonList->update('save', 'label', __('Save Carousel Group'));
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
        $this->buttonList->update('delete', 'label', __('Delete Carousel Group'));
    }
    /**
     * Retrieve text for header element depending on loaded Carousel Group
     *
     * @return string
     */
    public function getHeaderText()
    {
        /** @var \PixieMedia\ImageCarousel\Model\Cgroup $cgroup */
        $cgroup = $this->coreRegistry->registry('pixiemedia_imagecarousel_cgroup');
        if ($cgroup->getId()) {
            return __("Edit Carousel Group '%1'", $this->escapeHtml($cgroup->getLabel()));
        }
        return __('New Carousel Group');
    }
}
