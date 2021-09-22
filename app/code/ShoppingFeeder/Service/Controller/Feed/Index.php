<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ShoppingFeeder\Service\Controller\Feed;
use ShoppingFeeder\Service\Model\Offers;
use ShoppingFeeder\Service\Model\Auth;
use ShoppingFeeder\Service\ShoppingFeeder_Controller\FrontAuth;

/**
 * Responsible for loading page content.
 *
 * This is a basic controller that only loads the corresponding layout file. It may duplicate other such
 * controllers, and thus it is considered tech debt. This code duplication will be resolved in future releases.
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /** @var \Magento\Framework\View\Result\PageFactory  */
    protected $resultPageFactory;
    protected $jsonResultFactory;
    protected $context;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
    ) {
        $this->context = $context;
        $this->jsonResultFactory = $jsonResultFactory;
        parent::__construct($context);
    }
    /**
     * Load the page defined in view/frontend/layout/samplenewpage_index_index.xml
     *
     * @return \Magento\Framework\View\Result\Page
     */

    public function execute()
    {
        $auth = new FrontAuth($this->context, $this->jsonResultFactory);
        $unauthorized = $auth->checkAuth();
        if($unauthorized){
            return $unauthorized;
        }
        
        $page = $this->getRequest()->getParam('page', null);
        $numPerPage = $this->getRequest()->getParam('num_per_page', 1000);
        $offerId = $this->getRequest()->getParam('offer_id', null);
        $lastUpdate = $this->getRequest()->getParam('last_update', null);
        $store = $this->getRequest()->getParam('store', null);
        $currency = $this->getRequest()->getParam('currency', null);
        $allowVariants = (intval($this->getRequest()->getParam('allow_variants', 1)) == 1) ? true : false;

        /**
        * For per-store system
        */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');
        if (!is_null($store)) {
            $storeManager->setCurrentStore($store);
        } else {
            $defaultStoreCode = $storeManager
                ->getWebsite(true)
                ->getDefaultGroup()
                ->getDefaultStore()
                ->getCode();

            $storeManager->setCurrentStore($defaultStoreCode);
        }

        $baseCurrency = $storeManager->getStore()->getBaseCurrencyCode();
        $priceCurrency = (is_null($currency)) ? $storeManager->getStore()->getDefaultCurrencyCode() : $currency;
        $priceHelper = $objectManager->create('Magento\Directory\Helper\Data');
        $priceCurrencyRate = $priceHelper->currencyConvert(1, $baseCurrency, $priceCurrency);
            
        if (!function_exists('getallheaders')) {
            function getallheaders()
            {
                $headers = '';
                foreach ($_SERVER as $name => $value) {
                    if (substr($name, 0, 5) == 'HTTP_') {
                        $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                    }
                }
                return $headers;
            }
        }

        $headers = getallheaders();

        // return $this->resultPageFactory->create();
        $result = $this->jsonResultFactory->create();

        $data = array();
        $scheme = $this->getRequest()->getScheme();
        $method =  $this->getRequest()->getMethod();
        
        $authModel = new Auth();
        $authResult = $authModel->auth(
            $headers,
            $this->getRequest()->getScheme(),
            $this->getRequest()->getMethod()
        );

        if ($authResult !== true) {
            $responseData = array(
                'status' => 'fail',
                'data' => array (
                    'message' => $authResult
                )
            );

            $result->setData($responseData);
            return $result;
        }

        $offersModel = new Offers();
        // $data['all'] = $offers->getItems($page, $numPerPage, $lastUpdate, $store);
        // $result->setData($data);
        // return $result;
        if (is_null($offerId)) {
            $offers = $offersModel->getItems($page, $numPerPage, $lastUpdate, $store, $priceCurrency, $priceCurrencyRate, $allowVariants);
        } else {
            $offers = $offersModel->getItem($offerId, $store, $priceCurrency, $priceCurrencyRate);
        }

        $responseData = array(
            'status' => 'success',
            'data' => array(
                'page' => $page,
                'num_per_page' => $numPerPage,
                'offers' => $offers,
                'store' => $store,
                'base_currency' => $baseCurrency,
                'price_currency' => $priceCurrency,
                'exchange_Rate' => $priceCurrencyRate
            )
        );
        $result->setData($responseData);
        return $result; 
    }
}