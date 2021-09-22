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
namespace PixieMedia\ImageCarousel\Block\Adminhtml\Cgroup\Edit\Tab;

class Cgroup extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Country options
     * 
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $booleanOptions;

    /**
     * Slides To Show options
     * 
     * @var \PixieMedia\ImageCarousel\Model\Cgroup\Source\Slidestoshow
     */
    protected $slidestoshowOptions;

    /**
     * Slides To Scroll options
     * 
     * @var \PixieMedia\ImageCarousel\Model\Cgroup\Source\Slidestoscroll
     */
    protected $slidestoscrollOptions;

    /**
     * Responsive 1: Slides To Show options
     * 
     * @var \PixieMedia\ImageCarousel\Model\Cgroup\Source\Resoneslidestoshow
     */
    protected $resoneslidestoshowOptions;

    /**
     * Responsive 1: Slides To Scroll options
     * 
     * @var \PixieMedia\ImageCarousel\Model\Cgroup\Source\Resoneslidestoscroll
     */
    protected $resoneslidestoscrollOptions;

    /**
     * Responsive 2: Slides To Show options
     * 
     * @var \PixieMedia\ImageCarousel\Model\Cgroup\Source\Restwoslidestoshow
     */
    protected $restwoslidestoshowOptions;

    /**
     * Responsive 2: Slides To Scroll options
     * 
     * @var \PixieMedia\ImageCarousel\Model\Cgroup\Source\Restwoslidestoscroll
     */
    protected $restwoslidestoscrollOptions;

    /**
     * Responsive 3: Slides To Show options
     * 
     * @var \PixieMedia\ImageCarousel\Model\Cgroup\Source\Resthreeslidestoshow
     */
    protected $resthreeslidestoshowOptions;

    /**
     * Responsive 3: Slides To Scroll options
     * 
     * @var \PixieMedia\ImageCarousel\Model\Cgroup\Source\Resthreeslidestoscroll
     */
    protected $resthreeslidestoscrollOptions;

    /**
     * constructor
     * 
     * @param \Magento\Config\Model\Config\Source\Yesno $booleanOptions
     * @param \PixieMedia\ImageCarousel\Model\Cgroup\Source\Slidestoshow $slidestoshowOptions
     * @param \PixieMedia\ImageCarousel\Model\Cgroup\Source\Slidestoscroll $slidestoscrollOptions
     * @param \PixieMedia\ImageCarousel\Model\Cgroup\Source\Resoneslidestoshow $resoneslidestoshowOptions
     * @param \PixieMedia\ImageCarousel\Model\Cgroup\Source\Resoneslidestoscroll $resoneslidestoscrollOptions
     * @param \PixieMedia\ImageCarousel\Model\Cgroup\Source\Restwoslidestoshow $restwoslidestoshowOptions
     * @param \PixieMedia\ImageCarousel\Model\Cgroup\Source\Restwoslidestoscroll $restwoslidestoscrollOptions
     * @param \PixieMedia\ImageCarousel\Model\Cgroup\Source\Resthreeslidestoshow $resthreeslidestoshowOptions
     * @param \PixieMedia\ImageCarousel\Model\Cgroup\Source\Resthreeslidestoscroll $resthreeslidestoscrollOptions
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Config\Model\Config\Source\Yesno $booleanOptions,
        \PixieMedia\ImageCarousel\Model\Cgroup\Source\Slidestoshow $slidestoshowOptions,
        \PixieMedia\ImageCarousel\Model\Cgroup\Source\Slidestoscroll $slidestoscrollOptions,
        \PixieMedia\ImageCarousel\Model\Cgroup\Source\Resoneslidestoshow $resoneslidestoshowOptions,
        \PixieMedia\ImageCarousel\Model\Cgroup\Source\Resoneslidestoscroll $resoneslidestoscrollOptions,
        \PixieMedia\ImageCarousel\Model\Cgroup\Source\Restwoslidestoshow $restwoslidestoshowOptions,
        \PixieMedia\ImageCarousel\Model\Cgroup\Source\Restwoslidestoscroll $restwoslidestoscrollOptions,
        \PixieMedia\ImageCarousel\Model\Cgroup\Source\Resthreeslidestoshow $resthreeslidestoshowOptions,
        \PixieMedia\ImageCarousel\Model\Cgroup\Source\Resthreeslidestoscroll $resthreeslidestoscrollOptions,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    )
    {
        $this->booleanOptions                = $booleanOptions;
        $this->slidestoshowOptions           = $slidestoshowOptions;
        $this->slidestoscrollOptions         = $slidestoscrollOptions;
        $this->resoneslidestoshowOptions     = $resoneslidestoshowOptions;
        $this->resoneslidestoscrollOptions   = $resoneslidestoscrollOptions;
        $this->restwoslidestoshowOptions     = $restwoslidestoshowOptions;
        $this->restwoslidestoscrollOptions   = $restwoslidestoscrollOptions;
        $this->resthreeslidestoshowOptions   = $resthreeslidestoshowOptions;
        $this->resthreeslidestoscrollOptions = $resthreeslidestoscrollOptions;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \PixieMedia\ImageCarousel\Model\Cgroup $cgroup */
        $cgroup = $this->_coreRegistry->registry('pixiemedia_imagecarousel_cgroup');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('cgroup_');
        $form->setFieldNameSuffix('cgroup');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Carousel Group Information'),
                'class'  => 'fieldset-wide'
            ]
        );
        if ($cgroup->getId()) {
            $fieldset->addField(
                'cgroup_id',
                'hidden',
                ['name' => 'cgroup_id']
            );
        }
        $fieldset->addField(
            'label',
            'text',
            [
                'name'  => 'label',
                'label' => __('Label'),
                'title' => __('Label'),
                'required' => true,
            ]
        );
        $fieldset->addField(
            'imagewidth',
            'text',
            [
                'name'  => 'imagewidth',
                'label' => __('Image Width'),
                'title' => __('Image Width'),
                'required' => true,
                'note' => __('All images in this group will be set to this width'),
            ]
        );
        $fieldset->addField(
            'imageheight',
            'text',
            [
                'name'  => 'imageheight',
                'label' => __('Image Height'),
                'title' => __('Image Height'),
                'required' => true,
                'note' => __('All images in this group will be set to this width'),
            ]
        );
        $fieldset->addField(
            'infinite',
            'select',
            [
                'name'  => 'infinite',
                'label' => __('Infinite Scroll'),
                'title' => __('Infinite Scroll'),
                'required' => true,
                'values' => $this->booleanOptions->toOptionArray(),
            ]
        );
        $fieldset->addField(
            'slidestoshow',
            'multiselect',
            [
                'name'  => 'slidestoshow',
                'label' => __('Slides To Show'),
                'title' => __('Slides To Show'),
                'required' => true,
                'note' => __('For desktop'),
                'values' => $this->slidestoshowOptions->toOptionArray(),
            ]
        );
        $fieldset->addField(
            'slidestoscroll',
            'select',
            [
                'name'  => 'slidestoscroll',
                'label' => __('Slides To Scroll'),
                'title' => __('Slides To Scroll'),
                'required' => true,
                'values' => array_merge(['' => ''], $this->slidestoscrollOptions->toOptionArray()),
            ]
        );
        $fieldset->addField(
            'breakpointone',
            'text',
            [
                'name'  => 'breakpointone',
                'label' => __('Responsive 1: Break Point'),
                'title' => __('Responsive 1: Break Point'),
                'required' => true,
                'note' => __('Page width break point'),
            ]
        );
        $fieldset->addField(
            'resoneslidestoshow',
            'select',
            [
                'name'  => 'resoneslidestoshow',
                'label' => __('Responsive 1: Slides To Show'),
                'title' => __('Responsive 1: Slides To Show'),
                'required' => true,
                'values' => array_merge(['' => ''], $this->resoneslidestoshowOptions->toOptionArray()),
            ]
        );
        $fieldset->addField(
            'resoneslidestoscroll',
            'select',
            [
                'name'  => 'resoneslidestoscroll',
                'label' => __('Responsive 1: Slides To Scroll'),
                'title' => __('Responsive 1: Slides To Scroll'),
                'required' => true,
                'values' => array_merge(['' => ''], $this->resoneslidestoscrollOptions->toOptionArray()),
            ]
        );
        $fieldset->addField(
            'breakpointtwo',
            'text',
            [
                'name'  => 'breakpointtwo',
                'label' => __('Responsive 2: Break Point'),
                'title' => __('Responsive 2: Break Point'),
                'required' => true,
                'note' => __('Page width break point'),
            ]
        );
        $fieldset->addField(
            'restwoslidestoshow',
            'select',
            [
                'name'  => 'restwoslidestoshow',
                'label' => __('Responsive 2: Slides To Show'),
                'title' => __('Responsive 2: Slides To Show'),
                'required' => true,
                'values' => array_merge(['' => ''], $this->restwoslidestoshowOptions->toOptionArray()),
            ]
        );
        $fieldset->addField(
            'restwoslidestoscroll',
            'select',
            [
                'name'  => 'restwoslidestoscroll',
                'label' => __('Responsive 2: Slides To Scroll'),
                'title' => __('Responsive 2: Slides To Scroll'),
                'required' => true,
                'values' => array_merge(['' => ''], $this->restwoslidestoscrollOptions->toOptionArray()),
            ]
        );
        $fieldset->addField(
            'breakpointthree',
            'text',
            [
                'name'  => 'breakpointthree',
                'label' => __('Responsive 3: Break Point'),
                'title' => __('Responsive 3: Break Point'),
                'required' => true,
                'note' => __('Page width break point'),
            ]
        );
        $fieldset->addField(
            'resthreeslidestoshow',
            'select',
            [
                'name'  => 'resthreeslidestoshow',
                'label' => __('Responsive 3: Slides To Show'),
                'title' => __('Responsive 3: Slides To Show'),
                'required' => true,
                'values' => array_merge(['' => ''], $this->resthreeslidestoshowOptions->toOptionArray()),
            ]
        );
        $fieldset->addField(
            'resthreeslidestoscroll',
            'select',
            [
                'name'  => 'resthreeslidestoscroll',
                'label' => __('Responsive 3: Slides To Scroll'),
                'title' => __('Responsive 3: Slides To Scroll'),
                'required' => true,
                'values' => array_merge(['' => ''], $this->resthreeslidestoscrollOptions->toOptionArray()),
            ]
        );

        $cgroupData = $this->_session->getData('pixiemedia_imagecarousel_cgroup_data', true);
        if ($cgroupData) {
            $cgroup->addData($cgroupData);
        } else {
            if (!$cgroup->getId()) {
                $cgroup->addData($cgroup->getDefaultValues());
            }
        }
        $form->addValues($cgroup->getData());
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
        return __('Carousel Group');
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
