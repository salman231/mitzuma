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
namespace PixieMedia\ImageCarousel\Block\Adminhtml\Cimage\Edit\Tab;

class Cimage extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Country options
     * 
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $booleanOptions;

    /**
     * Carousel Group Source Model
     * 
     * @var \PixieMedia\ImageCarousel\Model\Source\Cgroup
     */
    protected $cgroupSourceModel;

    /**
     * constructor
     * 
     * @param \Magento\Config\Model\Config\Source\Yesno $booleanOptions
     * @param \PixieMedia\ImageCarousel\Model\Source\Cgroup $cgroupSourceModel
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Config\Model\Config\Source\Yesno $booleanOptions,
        \PixieMedia\ImageCarousel\Model\Source\Cgroup $cgroupSourceModel,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    )
    {
        $this->booleanOptions    = $booleanOptions;
        $this->cgroupSourceModel = $cgroupSourceModel;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \PixieMedia\ImageCarousel\Model\Cimage $cimage */
        $cimage = $this->_coreRegistry->registry('pixiemedia_imagecarousel_cimage');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('cimage_');
        $form->setFieldNameSuffix('cimage');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Carousel Image Information'),
                'class'  => 'fieldset-wide'
            ]
        );
        $fieldset->addType('image', '\PixieMedia\ImageCarousel\Block\Adminhtml\Cimage\Helper\Image');
        if ($cimage->getId()) {
            $fieldset->addField(
                'cimage_id',
                'hidden',
                ['name' => 'cimage_id']
            );
        }
        $fieldset->addField(
            'cgroup_id',
            'select',
            [
                'name'  => 'cgroup_id',
                'label' => __('Carousel Group'),
                'title' => __('Carousel Group'),
                'values' => array_merge(['' => ''], $this->cgroupSourceModel->toOptionArray()),
            ]
        );
        $fieldset->addField(
            'name',
            'text',
            [
                'name'  => 'name',
                'label' => __('Name'),
                'title' => __('Name'),
                'required' => true,
            ]
        );
		
        $fieldset->addField(
            'image',
            'image',
            [
                'name'  => 'image',
                'label' => __('Image'),
                'title' => __('Image'),
				'required' => true,
            ]
        );
        $fieldset->addField(
            'link',
            'text',
            [
                'name'  => 'link',
                'label' => __('Link'),
                'title' => __('Link'),
                'required' => true,
            ]
        );
        $fieldset->addField(
            'sort',
            'text',
            [
                'name'  => 'sort',
                'label' => __('Sort Order'),
                'title' => __('Sort Order'),
                'required' => true,
                'note' => __('0 is highest'),
            ]
        );
        $fieldset->addField(
            'status',
            'select',
            [
                'name'  => 'status',
                'label' => __('Enabled'),
                'title' => __('Enabled'),
                'required' => true,
                'values' => $this->booleanOptions->toOptionArray(),
            ]
        );

        $cimageData = $this->_session->getData('pixiemedia_imagecarousel_cimage_data', true);
        if ($cimageData) {
            $cimage->addData($cimageData);
        } else {
            if (!$cimage->getId()) {
                $cimage->addData($cimage->getDefaultValues());
            }
        }
        $form->addValues($cimage->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Carousel Image');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}
