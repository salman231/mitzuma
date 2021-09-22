<?php

namespace ShoppingFeeder\Service\Observer;
use Magento\Framework\Event\ObserverInterface;

class OrderNotifyObserver implements ObserverInterface
{
    const SF_URL = 'https://www.shoppingfeeder.com/webhook/magento2-orders/';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    )
    {
        $this->_objectManager = $objectManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try{
            $sfEnabled = true;

            //$scopeConfig = $this->_objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');

            $order = $observer->getData('order');

//            //set the order for JS tracking code
//            $orderItems = array();
//            /** @var \Magento\Sales\Model\Order\Item $item */
//            foreach ($order->getAllItems() as $item)
//            {
//                $orderItems[] = '\''.$item->getProductId().'\'';
//            }
//
//            $orderInfo = array(
//                'items' => $orderItems,
//                'value' => $order->getGrandTotal()
//            );
//            $catalogSession = $this->_objectManager->get('\Magento\Catalog\Model\Session');
//            $catalogSession->setSfdrOrderForJsTracking($orderInfo);

            $this->_notifyShoppingFeeder($order);
        }
        catch (\Exception $e)
        {
            //do nothing
        }

        return $this;
    }

    protected function _notifyShoppingFeeder(\Magento\Sales\Model\Order $order)
    {
        try {
            $scopeConfig = $this->_objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
            $localApiKey = $scopeConfig->getValue('shoppingfeeder/service/apikey', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            //get API key value from admin settings
            $apiKey = $localApiKey;

            $data = array(
                'entity_id' => $order->getEntityId(),
                'created_at' => $order->getCreatedAt(),
                'increment_id' => $order->getIncrementId(),
                'customer_id' => $order->getCustomerId(),
                'order_currency_code' => $order->getOrderCurrencyCode(),
                'grand_total' => $order->getGrandTotal(),
                'subtotal_incl_tax' => $order->getSubtotalInclTax(),
                'tax_amount' => $order->getTaxAmount(),
                'shipping_incl_tax' => $order->getShippingInclTax(),
            );
            foreach ($order->getAllItems() as $lineItem)
            {
                $data['line_items'][] = $lineItem->toArray();
            }
            $data['landing_site_ref'] = isset($_COOKIE['SFDRREF']) ? $_COOKIE['SFDRREF'] : '';

            $httpHeaders = new \Zend\Http\Headers();
            $httpHeaders->addHeaders([
                'X-SFApiKey' => $apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]);

            $request = new \Zend\Http\Request();
            $request->setHeaders($httpHeaders);
            $request->setUri(self::SF_URL);
            $request->setMethod(\Zend\Http\Request::METHOD_POST);
            $request->setContent(json_encode($data));

            $http = new \Zend\Http\Client();
            $result = $http->send($request);
        }
        catch (\Exception $e)
        {
            //do nothing
        }
    }
}