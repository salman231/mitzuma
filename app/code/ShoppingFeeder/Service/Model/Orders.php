<?php

namespace ShoppingFeeder\Service\Model;
use Magento\Framework\DataObject;

class Orders
{
    public function __construct()
    {

    }

    private function getOrderInfo(\Magento\Sales\Model\Order $order)
    {
        $data = array();

        $orderData = $order->getData();

        //normalise order data for ShoppingFeeder
        $data['order_id'] = @$orderData['entity_id'];
        $data['order_date'] = @$orderData['created_at'];
        $data['store_order_number'] = @$orderData['increment_id'];
        $data['store_order_user_id'] = @$orderData['customer_id'];
        $data['store_order_currency'] = @$orderData['order_currency_code'];
        $data['store_total_price'] = @$orderData['grand_total'];
        $data['store_total_line_items_price'] = @$orderData['subtotal_incl_tax'];
        $data['store_total_tax'] = @$orderData['tax_amount'];
        $data['store_order_total_discount'] = @$orderData['discount_amount'];
        $data['store_shipping_price'] = @$orderData['shipping_incl_tax'];

        $lineItems = array();
        foreach($order->getAllItems() as $item)
        {
            $lineItems[] = $item->toArray();
        }
        $data['line_items'] = $lineItems;

        return $data;
    }

    public function getItems($page = null, $numPerPage = 1000, $store = null)
    {
        /* @var Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $orderCollection = $objectManager->create('\Magento\Sales\Model\ResourceModel\Order\CollectionFactory');
        // $collection = Mage::getModel('sales/order')->getCollection()
        // $collection = $productCollection->create()
        $collection = $orderCollection->create()
            ->addAttributeToSelect('*')
            ->setPageSize($numPerPage)
            ->setCurPage($page)
            ->addAttributeToFilter('status', \Magento\Sales\Model\Order::STATE_COMPLETE);

            // $objectManager = Magento\Framework\App\ObjectManager::getInstance();
            // $orders = $objectManager->get('Magento\Sales\Model\Order')->getCollection();
        /**
         * For per-store system
         */
        if (!is_null($store))
        {
            $storeManager = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');
            $collection->addAttributeToFilter('store_id', $storeManager->getStore($store)->getId());
        }

        if (!is_null($page))
        {
            $offset = ($page * $numPerPage) - $numPerPage;
            $orderIds = $collection->getAllIds($numPerPage, $offset);
        }
        else
        {
            $orderIds = $collection->getAllIds();
        }

        $orders = array();
        /* @var 3 $order */
        foreach ($orderIds as $orderId)
        {
            $order = $objectManager->get('Magento\Sales\Model\Order')->load($orderId);
            $orders[$order->getId()] = $this->getOrderInfo($order);
        }

        return $orders;
    }

    public function getItem($itemId, $store = null)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $orderCollection = $objectManager->create('\Magento\Sales\Model\ResourceModel\Order\CollectionFactory');

        $orders = array();

        $order = $orderCollection->load($itemId);
        $orders[$order->getId()] = $this->getOrderInfo($order);

        return $orders;
    }
}