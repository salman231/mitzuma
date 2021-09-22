<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageside\ShippingMatrixRates\Model\Config\Backend;

/**
 * Backend model for shipping matrix rates CSV importing
 */
class Matrixrates extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Mageside\ShippingMatrixRates\Model\ResourceModel\Carrier\ImportFactory
     */
    protected $_matrixratesFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Mageside\ShippingMatrixRates\Model\ResourceModel\Carrier\ImportFactory $matrixratesFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Mageside\ShippingMatrixRates\Model\ResourceModel\Carrier\ImportFactory $matrixratesFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_matrixratesFactory = $matrixratesFactory;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return $this
     */
    public function afterSave()
    {
        /** @var \Mageside\ShippingMatrixRates\Model\ResourceModel\Carrier\Import $matrixrates */
        $matrixrates = $this->_matrixratesFactory->create();
        $matrixrates->uploadAndImport($this);
        return parent::afterSave();
    }
}
