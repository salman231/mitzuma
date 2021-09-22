<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageside\ShippingMatrixRates\Block\Adminhtml\Carrier\Matrixrates;

/**
 * Shipping carrier matrix rates grid block
 * WARNING: This grid used for export matrix rates
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Website filter
     *
     * @var int
     */
    protected $_websiteId;

    /**
     * @var \Mageside\ShippingMatrixRates\Model\Carrier\Matrixrates
     */
    protected $_matrixrates;

    /**
     * @var \Mageside\ShippingMatrixRates\Model\ResourceModel\Carrier\Matrixrates\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Mageside\ShippingMatrixRates\Helper\Config
     */
    protected $_carrierConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Mageside\ShippingMatrixRates\Model\ResourceModel\Carrier\Matrixrates\CollectionFactory $collectionFactory
     * @param \Mageside\ShippingMatrixRates\Model\Carrier\Matrixrates $matrixrates
     * @param \Mageside\ShippingMatrixRates\Helper\Config $carrierConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Mageside\ShippingMatrixRates\Model\ResourceModel\Carrier\Matrixrates\CollectionFactory $collectionFactory,
        \Mageside\ShippingMatrixRates\Model\Carrier\Matrixrates $matrixrates,
        \Mageside\ShippingMatrixRates\Helper\Config $carrierConfig,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_matrixrates = $matrixrates;
        $this->_carrierConfig = $carrierConfig;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Define grid properties
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('shippingMatrixratesGrid');
        $this->_exportPageSize = 10000;
    }

    /**
     * Set current website
     *
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId)
    {
        $this->_websiteId = $this->_storeManager->getWebsite($websiteId)->getId();
        return $this;
    }

    /**
     * Retrieve current website id
     *
     * @return int
     */
    public function getWebsiteId()
    {
        if ($this->_websiteId === null) {
            $this->_websiteId = $this->_storeManager->getWebsite()->getId();
        }
        return $this->_websiteId;
    }

    /**
     * Prepare shipping matrix rates collection
     *
     * @return \Mageside\ShippingMatrixRates\Block\Adminhtml\Carrier\Matrixrates\Grid
     */
    protected function _prepareCollection()
    {
        /** @var $collection \Mageside\ShippingMatrixRates\Model\ResourceModel\Carrier\Matrixrates\Collection */
        $collection = $this->_collectionFactory->create();
        $collection->setWebsiteFilter($this->getWebsiteId());
        $collection->setExportColumnsView();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare table columns
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'dest_country',
            ['header' => __('Country'), 'index' => 'dest_country', 'default' => '*']
        );

        $this->addColumn(
            'dest_region',
            ['header' => __('Region/State'), 'index' => 'dest_region', 'default' => '*']
        );

        $this->addColumn(
            'dest_city',
            ['header' => __('City'), 'index' => 'dest_city', 'default' => '*']
        );

        $this->addColumn(
            'dest_zip_from',
            ['header' => __('Zip/Postal Code From'), 'index' => 'dest_zip_from', 'default' => '*']
        );

        $this->addColumn(
            'dest_zip_to',
            ['header' => __('Zip/Postal Code To'), 'index' => 'dest_zip_to', 'default' => '*']
        );

        $this->addColumn(
            'weight_from',
            ['header' => __('Weight From'), 'index' => 'weight_from', 'default' => '*']
        );

        $this->addColumn(
            'weight_to',
            ['header' => __('Weight To'), 'index' => 'weight_to', 'default' => '*']
        );

        $this->addColumn(
            'qty_from',
            ['header' => __('Qty From'), 'index' => 'qty_from', 'default' => '*']
        );

        $this->addColumn(
            'qty_to',
            ['header' => __('Qty To'), 'index' => 'qty_to', 'default' => '*']
        );

        $this->addColumn(
            'price_from',
            ['header' => __('Price From'), 'index' => 'price_from', 'default' => '*']
        );

        $this->addColumn(
            'price_to',
            ['header' => __('Price To'), 'index' => 'price_to', 'default' => '*']
        );

        $this->addColumn(
            'shipping_group',
            ['header' => __('Shipping Group'), 'index' => 'shipping_group', 'default' => '*']
        );

        $this->addColumn(
            'customer_group',
            ['header' => __('Customer Group'), 'index' => 'customer_group', 'default' => '*']
        );

        $this->addColumn(
            'calc_logic',
            ['header' => __('Advanced Calculations'), 'index' => 'calc_logic', 'default' => '*']
        );

        $this->addColumn('price', ['header' => __('Shipping Price'), 'index' => 'price']);

        $this->addColumn('cost', ['header' => __('Cost'), 'index' => 'cost']);

        $this->addColumn(
            'delivery_method',
            ['header' => __('Delivery Method Name'), 'index' => 'delivery_method']
        );

        $this->addColumn('notes', ['header' => __('Notes'), 'index' => 'notes']);

        return parent::_prepareColumns();
    }

    /**
     * Retrieve a file container array by grid data as CSV
     *
     * Return array with keys type and value
     *
     * @return array
     */
    public function getCsvFile()
    {
        $delimiter = $this->_carrierConfig->getConfig('csv_delimiter') ?
            $this->_carrierConfig->getConfig('csv_delimiter') :
            ',';

        $this->_isExport = true;
        $this->_prepareGrid();

        $name = md5(microtime());
        $file = $this->_path . '/' . $name . '.csv';

        $this->_directory->create($this->_path);
        $stream = $this->_directory->openFile($file, 'w+');

        $stream->lock();

        $stream->writeCsv($this->_getExportHeaders(), $delimiter);
        $this->_exportIterateCollection('_exportCsvItem', [$stream]);

        if ($this->getCountTotals()) {
            $stream->writeCsv($this->_getExportTotals(), $delimiter);
        }

        $stream->unlock();
        $stream->close();

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true  // can delete file after use
        ];
    }

    /**
     * Export item with original data and skip item with empty data
     *
     * @param \Magento\Framework\DataObject $item
     * @param \Magento\Framework\Filesystem\File\WriteInterface $stream
     */
    protected function _exportCsvItem(
        \Magento\Framework\DataObject $item,
        \Magento\Framework\Filesystem\File\WriteInterface $stream
    ) {
        $data = $item->getData('original_record_data');
        if ($data != null) {
            $stream->write($data . "\n");
        }
    }
}
