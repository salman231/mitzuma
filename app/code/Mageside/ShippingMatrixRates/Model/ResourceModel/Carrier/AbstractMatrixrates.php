<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */

/**
 * Shipping matrix rates
 */
namespace Mageside\ShippingMatrixRates\Model\ResourceModel\Carrier;

use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DirectoryList;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractMatrixrates extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_coreConfig;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Mageside\ShippingMatrixRates\Model\Carrier\Matrixrates
     */
    protected $_carrierMatrixrates;

    /**
     * @var \Mageside\ShippingMatrixRates\Helper\Config
     */
    protected $_carrierConfig;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected $_countryCollectionFactory;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Region\CollectionFactory
     */
    protected $_regionCollectionFactory;

    /**
     * Filesystem instance
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Customer\Model\GroupFactory
     */
    protected $_customerGroupFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\GroupRepository
     */
    protected $_customerGroupRepository;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var \Mageside\ShippingMatrixRates\Helper\MergeResultHelper
     */
    protected $_merger;

    /**
     * @var \Mageside\ShippingMatrixRates\Helper\ItemHelper
     */
    protected $_itemHandler;

    /**
     * @var \Mageside\ShippingMatrixRates\Helper\LogicHelper
     */
    protected $_logicHandler;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Mageside\ShippingMatrixRates\Model\Carrier\Matrixrates $carrierMatrixrates
     * @param \Mageside\ShippingMatrixRates\Helper\Config $carrierConfig
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\GroupFactory $customerGroupFactory
     * @param \Magento\Customer\Model\ResourceModel\GroupRepository $customerGroupRepository
     * @param \Magento\Eav\Model\Config $config
     * @param \Mageside\ShippingMatrixRates\Helper\MergeResultHelper $merger
     * @param \Mageside\ShippingMatrixRates\Helper\ItemHelper $itemHandler
     * @param \Mageside\ShippingMatrixRates\Helper\LogicHelper $logicHandler
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Mageside\ShippingMatrixRates\Model\Carrier\Matrixrates $carrierMatrixrates,
        \Mageside\ShippingMatrixRates\Helper\Config $carrierConfig,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\GroupFactory $customerGroupFactory,
        \Magento\Customer\Model\ResourceModel\GroupRepository $customerGroupRepository,
        \Magento\Eav\Model\Config $config,
        \Mageside\ShippingMatrixRates\Helper\MergeResultHelper $merger,
        \Mageside\ShippingMatrixRates\Helper\ItemHelper $itemHandler,
        \Mageside\ShippingMatrixRates\Helper\LogicHelper $logicHandler,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->_coreConfig = $coreConfig;
        $this->_coreRegistry = $coreRegistry;
        $this->_logger = $logger;
        $this->_storeManager = $storeManager;
        $this->_carrierMatrixrates = $carrierMatrixrates;
        $this->_carrierConfig = $carrierConfig;
        $this->_countryCollectionFactory = $countryCollectionFactory;
        $this->_regionCollectionFactory = $regionCollectionFactory;
        $this->_filesystem = $filesystem;
        $this->_customerSession = $customerSession;
        $this->_customerGroupFactory = $customerGroupFactory;
        $this->_customerGroupRepository = $customerGroupRepository;
        $this->_eavConfig = $config;
        $this->_merger = $merger;
        $this->_itemHandler = $itemHandler;
        $this->_logicHandler = $logicHandler;
    }

    /**
     * Define main table and id field name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('shipping_matrixrates', 'pk');
    }
}
