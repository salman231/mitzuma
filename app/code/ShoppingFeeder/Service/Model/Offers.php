<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ShoppingFeeder\Service\Model;
use Magento\Framework\DataObject;

/**
 * Class Offer
 * @package ShoppingFeeder\Service\Model
 */
class Offers
{
    protected $_productCollectionFactory;
        
    public function __construct()
    {    

    }
    
    public function getProductCollection($page = null, $numPerPage = 50, $lastUpdate = null, $store = null)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
        $storeManager = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');
        $collection = $productCollection->create()
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
                    ->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
                    ->setStore($storeManager->getStore())
                    ->addPriceData()
                    // ->addAttributeToFilter('updated_at',['gt'=>'2017-08-15 08:14:16'])
                    ->setPageSize($numPerPage)
                    ->setCurPage($page)
                    ->load();
        return $collection;
        // foreach ($collection as $product){
        //     echo 'Name  =  '.$product->getName().'<br>';
        // }  

    }

    private function hasParent(\Magento\Catalog\Model\Product $product)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $configModel = $objectManager->create('\Magento\ConfigurableProduct\Model\Product\Type\Configurable');
        $parents = $configModel->getParentIdsByChild($product->getId());
        return !empty($parents);
    }

    public function getProductInfo(\Magento\Catalog\Model\Product $product, \Magento\Catalog\Model\Product $parent = null, $variantOptions = null, $lastUpdate = null, $priceCurrency, $priceCurrencyRate)
    {
        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configModel */
        // $configModel = Mage::getModel('catalog/product_type_configurable');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $configModel = $objectManager->create('\Magento\ConfigurableProduct\Model\Product\Type\Configurable');

        $p = array();

        $isVariant = !is_null($parent);
        
        /**
         * We only want to pull variants (children of configurable products) that are children, not as standalone products
         */
        //if this product's parent is visible in catalog and search, Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
        //we will find this product when we fetch all the children of this parent through a normal iteration, so return nothing
        if (!$isVariant && $this->hasParent($product)) {
            return array();
        }

        if ($isVariant) {
            $variant = $product;
            $product = $parent;
            /* @var \Magento\CatalogInventory\Model\Stock\Item $stockItem */
            $stockItem = $objectManager->create('\Magento\CatalogInventory\Model\Stock\Item')->setProduct($variant);
        } else {
            $stockItem = $objectManager->create('\Magento\CatalogInventory\Model\Stock\Item')->setProduct($product);
        }
        
        $data = $product->getData();
    

        $attributes = $product->getAttributes();
        $manufacturer = '';
        $brand = '';
        
        $usefulAttributes = array();

        /**
         * @var Mage_Eav_Model_Entity_Attribute_Abstract $attribute
         */
//            var_dump("");
//            var_dump("");
//            var_dump("");
        foreach ($attributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            $attributeLabel = $attribute->getData('frontend_label');
//                var_dump($attributeCode. ' : '.print_r($value, true));
//                var_dump($attributeLabel. ' : '.print_r($value, true));
            $value = null;
            try {
                if ($isVariant) {
                    $value = $attribute->getFrontend()->getValue($variant);
                } else {
                    $value = $attribute->getFrontend()->getValue($product);
                }
            } catch (\Exception $e) {
                //do nothing
                $value = null;
            }
            if (!is_null($value)) {
                if (preg_match('/^manufacturer$/i', $attributeCode) || preg_match('/^manufacturer$/i', $attributeLabel)) {
                    $manufacturer = $value;
                }

                if (preg_match('/^brand$/i', $attributeCode) || preg_match('/^brand$/i', $attributeLabel)) {
                    $brand = $value;
                }

                /*
                if (preg_match('/age/i', $attributeCode) || preg_match('/age/i', $attributeLabel))
                {
                    $usefulAttributes['age'] = $value;
                }
                if (preg_match('/color|colour/i', $attributeCode) || preg_match('/color|colour/i', $attributeLabel))
                {
                    $usefulAttributes['colour'] = $value;
                }
                if (preg_match('/size/i', $attributeCode) || preg_match('/size/i', $attributeLabel))
                {
                    $usefulAttributes['size'] = $value;
                }
                if (preg_match('/gender|sex/i', $attributeCode) || preg_match('/gender|sex/i', $attributeLabel))
                {
                    $usefulAttributes['gender'] = $value;
                }
                if (preg_match('/material/i', $attributeCode) || preg_match('/material/i', $attributeLabel))
                {
                    $usefulAttributes['material'] = $value;
                }
                if (preg_match('/pattern/i', $attributeCode) || preg_match('/pattern/i', $attributeLabel))
                {
                    $usefulAttributes['pattern'] = $value;
                }
                */
                $attributeValue = $attribute->getFrontend()->getValue($product);
                //don't deal with arrays
                if (!is_array($attributeValue)) {
                    if (!is_null($product->getData($attributeCode)) && ((string)$attributeValue != '')) {
                        $usefulAttributes[$attributeCode] = $value;
                    }
                }
            }
            
        }

        //category path
        $categories = $product->getCategoryIds();

        $categoryPathsToEvaluate = array();
        $maxDepth = 0;
        $categoryPathToUse = '';
        $lastCatUrl = '';

        $storeManager = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');
        $storeRootCategoryId = $storeManager->getStore()->getRootCategoryId();

        /** @var \Magento\Catalog\Model\Category $categoryModel */
        $categoryModel = $objectManager->create('\Magento\Catalog\Model\Category');

        /** @var \Magento\Catalog\Model\ResourceModel\Category $categoryModelResource */
        $categoryModelResource = $objectManager->create('\Magento\Catalog\Model\ResourceModel\Category');
        $categoryModelResource->load($categoryModel, $storeRootCategoryId);

        $storeRootCategoryName = $categoryModel->getName();

        $baseUrl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        if (!empty($categories)) {
            /** @var \Magento\Framework\Data\Collection $categoryCollection */
            $categoryCollection = $product->getCategoryCollection()->addAttributeToSelect('name');
            
            $depth = 0;
            $productCatPaths = array();

            try {
                /** @var \Magento\Catalog\Model\Category $cat1 */
                foreach($categoryCollection as $cat1){
                    $pathIds = explode('/', $cat1->getPath());
                    unset($pathIds[0]);

                    //get path count
                    $thisDepth = count($pathIds);
                    if ($thisDepth > $depth) {
                        $depth = $thisDepth;
                        $productCatPaths = $pathIds;
                    }
                }

                $pathByName = array();
                //use cat paths from above to get cat names
                foreach ($productCatPaths as $catId)
                {
                    /** @var \Magento\Catalog\Model\Category $currentCat */
                    $currentCat = $objectManager->create('\Magento\Catalog\Model\Category');
                    /** @var \Magento\Catalog\Model\Category $currentCat */
                    $categoryModelResource->load($currentCat, $catId);

                    $currentCatName = $currentCat->getName();
                    if ($currentCat->getName() != $storeRootCategoryName)
                    {
                        $pathByName[] = $currentCatName;

                        //try get the category URL
                        try {
                            $lastCatUrl = $currentCat->getUrl();
                        }
                        catch (\Exception $e) {

                        }
                    }
                }
                $categoryPathToUse = implode(' > ', $pathByName);
            }
            catch (\Exception $e) {
                //error getting the categories for some reason. Just fail gracefully for now
            }
        }

        if ($isVariant && isset($variant)) {
            $p['internal_variant_id'] = $variant->getId();

            $variantOptionsTitle = array();
            // not available
            $variantPrice = isset($variantOptions['basePrice']) ? $variantOptions['basePrice'] : 0;

            $urlHashParts = array();

            // Collect options applicable to the configurable product
            if (isset($variantOptions['refactoredOptions'][$variant->getId()])) {
                foreach ($variantOptions['refactoredOptions'][$variant->getId()] as $attributeCode => $option) {
                    $variantOptionsTitle[] = $option['value'];

                    //add these configured attributes to the set of parent's attributes
                    $usefulAttributes[$attributeCode] = $option['value'];
                    
                    if (is_null($option['price'])) {
                        $variantPrice = $variant->getPrice();
                    } else {
                        $variantPrice += $option['price'];
                    }

                    $urlHashParts[] = $option['attributeId'].'='.$option['valueId'];
                }
            }

            $variantOptionsTitle = implode(' / ', $variantOptionsTitle);
            $title = $data['name'] . ' - ' . $variantOptionsTitle;
            $sku = $variant->getData('sku');
            $price = $variantPrice;
            $salePrice = $variant->getSpecialPrice();
            $variantImage = $variant->getImage();

            if (!is_null($variantImage) && !empty($variantImage) && $variantImage!='no_selection')
            {
                $imageFile = $variant->getImage();
                // $imageUrl = $p['image_url'] = $baseUrl.
                //     'catalog/product'.$imageFile;
                $imageUrl = $p['image_url'] = $variant->getMediaConfig()->getMediaUrl($imageFile);
                $imageLocalPath = $variant->getMediaConfig()->getMediaPath($imageFile);
            }
            else
            {
                $imageFile = $product->getImage();
                // $imageUrl = $p['image_url'] = $baseUrl.
                //     'catalog/product'.$imageFile;
                $imageUrl = $p['image_url'] = $product->getMediaConfig()->getMediaUrl($imageFile);
                $imageLocalPath = $product->getMediaConfig()->getMediaPath($imageFile);
            }
            $productUrl = $product->getProductUrl().'#'.implode('&', $urlHashParts);
        }
        else
        {
            $p['internal_variant_id'] = '';
            $title = @$data['name'];
            $sku = @$data['sku'];


            if ($product->getTypeId() == 'bundle')
            {
                /**
                 * @var $priceModel Mage_Bundle_Model_Product_Price
                 */
                $priceModel  = $product->getPriceModel();

                list($price, $_maximalPriceTax) = $priceModel->getTotalPrices($product, null, null, false);
                list($priceInclTax, $_maximalPriceInclTax) = $priceModel->getTotalPrices($product, null, true, false);
            }
            else
            {
                $price = $product->getPrice();
            }

            $salePrice = $product->getSpecialPrice();

            $imageFile = $product->getImage();
//            $imageUrl = $p['image_url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).
//                'catalog/product/'.preg_replace('/^\//', '', $imageFile);
            $imageUrl = $p['image_url'] = $product->getMediaConfig()->getMediaUrl($imageFile);
            $imageLocalPath = $product->getMediaConfig()->getMediaPath($imageFile);
            $productUrl = $product->getProductUrl();
        }

        //if we have previously captured this product and it hasn't changed, don't send through full payload
        $wasPreviouslyCaptured = !is_null($lastUpdate) && isset($usefulAttributes['updated_at']) && strtotime($usefulAttributes['updated_at']) < $lastUpdate;
        if ($wasPreviouslyCaptured)
        {
            $p['internal_id'] = $product->getId();
            $p['internal_update_time'] = isset($usefulAttributes['updated_at']) ? $usefulAttributes['updated_at'] : null;
        }
        else
        {
            $p['category'] = $categoryPathToUse;
            $p['category_url'] = $lastCatUrl;
            $p['title'] = $title;
            $p['brand'] = ($brand=='No') ? (($manufacturer == 'No') ? '' : $manufacturer) : $brand;
            $p['manufacturer'] = ($manufacturer=='No') ? $brand : $manufacturer;
            $p['mpn'] = isset($data['model']) ? $data['model'] : $data['sku'];
            $p['internal_id'] = $product->getId();
            $p['description'] = isset($data['description']) ? $data['description'] : '';
            $p['short_description'] = isset($data['short_description']) ? $data['short_description'] : '';
            $p['weight'] = isset($data['weight']) ? $data['weight'] : 0.00;
            $p['sku'] = $sku;
            $p['gtin'] = '';

            //$priceModel = $product->getPriceModel();

            //do a currency conversion. if the currency is in base currency, it will be 1.0
            $price = $price * $priceCurrencyRate;
            $salePrice = $salePrice * $priceCurrencyRate;

            $p['currency'] = $priceCurrency;
            $p['price'] = $price;// Mage::helper('checkout')->convertPrice($priceModel->getPrice($product), false);
            $p['sale_price'] = '';
            $p['sale_price_effective_date'] = '';
            if ($salePrice != $p['price'])
            {
                $p['sale_price'] = $salePrice;
                if ($product->getSpecialFromDate()!=null && $product->getSpecialToDate()!=null)
                {
                    $p['sale_price_effective_date'] = date("c", strtotime(date("Y-m-d 00:00:00", strtotime($product->getSpecialFromDate())))).'/'.date("c", strtotime(date("Y-m-d 23:59:59", strtotime($product->getSpecialToDate()))));
                }
            }

            $p['delivery_cost'] = 0.00;
            $p['tax'] = 0.00;
            $p['url'] = $productUrl;
            $p['internal_update_time'] = isset($usefulAttributes['updated_at']) ? date("c", strtotime($usefulAttributes['updated_at'])) : '';

            $p['image_url'] = $imageUrl;
            if (file_exists($imageLocalPath))
            {
                $p['image_modified_time'] = date("c", filemtime($imageLocalPath));
            }
            $p['availability'] = ($stockItem->getIsInStock())?'in stock':'out of stock';
            $p['quantity'] = $stockItem->getQty();
            $p['condition'] = '';
            $p['availability_date'] = '';
            $p['attributes'] = $usefulAttributes;
            $imageGallery = array();
            foreach ($product->getMediaGalleryImages() as $image)
            {
                $galleryImage = array();
                $galleryImage['url'] = isset($image['url']) ? $image['url'] : '';
                if (file_exists($image['path']))
                {
                    $galleryImage['image_modified_time'] = date("c", filemtime($image['path']));
                }
                $imageGallery[] = $galleryImage;
            }
            $p['extra_images'] = $imageGallery;
        }

        return $p;
    }

        public function getItems($page = null, $numPerPage = 1000, $lastUpdate = null, $store = null, $priceCurrency = null, $priceCurrencyRate = null, $allowVariants = true)
    {
        /* @var Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');

        $collection = $productCollection->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
            ->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);

        /**
         * For per-store system
         */
        if (!is_null($store))
        {
            $storeManager = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');
            $storeRootCategoryId = $storeManager->getStore()->getRootCategoryId();
            $collection->addStoreFilter($storeManager->getStore());
        }

        if (!is_null($page))
        {
            $offset = ($page * $numPerPage) - $numPerPage;
            $productIds = $collection->getAllIds($numPerPage, $offset);
        }
        else
        {
            
            $productIds = $collection->getAllIds();
            
        }

        $products = array();
        // $categoryCreate = $objectManager->create('\Magento\Catalog\Model\ProductRepository');
        foreach ($productIds as $productId)
        {

            $productRepository = $objectManager->create('Magento\Catalog\Model\ProductFactory')->create();
            $productRepository->reset();
            /** @var Mage_Catalog_Model_Product $product */
            $product = $productRepository->load($productId);
            /**
             * Get variants, if there are any
             * If there are variants that are visible in the catalog, we will skip them when we iterate normally
             */
             
            //if we have a configurable product, capture the variants
            if ($product->getTypeId() == 'configurable' && $allowVariants)
            {
                /** @var Mage_Catalog_Model_Product_Type_Configurable $configModel */
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
         
                $configModel = $objectManager->create('\Magento\ConfigurableProduct\Model\Product\Type\Configurable');

                $timeStart = microtime(true);
                //$children = $configModel->getChildrenIds($product->getId());
                $children = $configModel->getChildrenIds($product->getId()); //$configModel->getUsedProducts($product);
                $children = array_pop($children);

                if (count($children) > 0)
                {
                    $parent = $product;

                    //get variant options
                    $layout = $objectManager->create('\Magento\Framework\View\LayoutFactory')->create();
                    $jsonHelper = $objectManager->create('\Magento\Framework\Json\Helper\Data');
                    
                    $block = $layout->createBlock('\Magento\ConfigurableProduct\Block\Product\View\Type\Configurable');
                    $block->setProduct($parent);
                    
                    $variantOptions = $jsonHelper->jsonDecode($block->getJsonConfig());

                    $variantAttributes = array();
                    foreach ($variantOptions['attributes'] as $attributeId => $options)
                    {
                        $code = @$options['code'];
                        foreach ($options['options'] as $option)
                        {
                            $value = @$option['label'];
                            $price = @$option['price'];
                            $valueId = @$option['id'];
                            foreach ($option['products'] as $productId)
                            {
                                //$children[] = $productId;
                                $variantAttributes[$productId][$code]['value'] = $value;
                                $variantAttributes[$productId][$code]['price'] = $price;
                                $variantAttributes[$productId][$code]['valueId'] = $valueId;
                                $variantAttributes[$productId][$code]['attributeId'] = $attributeId;
                            }
                        }
                    }
                    $variantOptions['refactoredOptions'] = $variantAttributes;


                    foreach ($children as $variantId)
                    {
                        /** @var Mage_Catalog_Model_Product $variant */
                        // $variant = Mage::getModel('catalog/product')->load($variantId);
                        $productRepository = $objectManager->create('Magento\Catalog\Model\ProductFactory')->create();
                        $variant = $productRepository->load($productId);

                        $productData = $this->getProductInfo($variant, $parent, $variantOptions, $lastUpdate, $priceCurrency, $priceCurrencyRate);
                        if (!empty($productData))
                        {
                            $products[] = $productData;
                        }
                    }
                }
            }
            else
            {
                
                $productData = $this->getProductInfo($product, null, null, $lastUpdate, $priceCurrency, $priceCurrencyRate);
                if (!empty($productData))
                {
                    $products[] = $productData;
                }
            }
        }

        return $products;
    }

    public function getConfigurableProduct($product, $lastUpdate)
    {
        $products = array();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
         
        $configModel = $objectManager->create('\Magento\ConfigurableProduct\Model\Product\Type\Configurable');

        //$children = $configModel->getChildrenIds($product->getId());
        $children = $configModel->getUsedProducts($product);

        if (count($children) > 0)
        {
            $parent = $product;

            //get variant options
            // $layout = Mage::getSingleton('core/layout');
                            

        $layout = $objectManager->create('\Magento\Framework\View\LayoutFactory')->create();
        $jsonHelper = $objectManager->create('\Magento\Framework\Json\Helper\Data');
        
            $block = $layout->createBlock('\Magento\ConfigurableProduct\Block\Product\View\Type\Configurable');
            $block->setProduct($parent);
            
            $variantOptions = $jsonHelper->jsonDecode($block->getJsonConfig());
            
            $variantAttributes = array();
            foreach ($variantOptions['attributes'] as $attributeId => $options)
            {
                $code = @$options['code'];
                foreach ($options['options'] as $option)
                {
                    $value = @$option['label'];
                    // not available
                    $price = isset($option['price']) ? $option['price'] : 0;
                    $valueId = @$option['id'];
                    foreach ($option['products'] as $productId)
                    {
                        //$children[] = $productId;
                        $variantAttributes[$productId][$code]['value'] = $value;
                        $variantAttributes[$productId][$code]['price'] = $price;
                        $variantAttributes[$productId][$code]['valueId'] = $valueId;
                        $variantAttributes[$productId][$code]['attributeId'] = $attributeId;
                    }
                }
            }
            $variantOptions['refactoredOptions'] = $variantAttributes;


            foreach ($children as $variant)
            {
                /** @var Mage_Catalog_Model_Product $variant */
                //$variant = Mage::getModel('catalog/product')->load($variantId);

                $productData = $this->getProductInfo($variant, $parent, $variantOptions, $lastUpdate);
                if (!empty($productData))
                {
                    $products[] = $productData;
                }
            }
        }

        return $products;
            
    }


    public function getItem($itemId, $store = null, $priceCurrency = null, $priceCurrencyRate = null, $allowVariants = true)
    {
        $lastUpdate = null;
        $products = array();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productRepository = $objectManager->create('Magento\Catalog\Model\ProductFactory')->create();
        $productRepository->reset();
        /** @var Mage_Catalog_Model_Product $product */
        $product = $productRepository->load($itemId);

        //if we have a configurable product, capture the variants
        if ($product->getTypeId() == 'configurable' && $allowVariants)
        {
            /** @var Mage_Catalog_Model_Product_Type_Configurable $configModel */
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        
            $configModel = $objectManager->create('\Magento\ConfigurableProduct\Model\Product\Type\Configurable');

            $timeStart = microtime(true);
            //$children = $configModel->getChildrenIds($product->getId());
            $children = $configModel->getChildrenIds($product->getId()); //$configModel->getUsedProducts($product);
            $children = array_pop($children);

            if (count($children) > 0)
            {
                $parent = $product;

                //get variant options
                $layout = $objectManager->create('\Magento\Framework\View\LayoutFactory')->create();
                $jsonHelper = $objectManager->create('\Magento\Framework\Json\Helper\Data');
                
                $block = $layout->createBlock('\Magento\ConfigurableProduct\Block\Product\View\Type\Configurable');
                $block->setProduct($parent);
                
                $variantOptions = $jsonHelper->jsonDecode($block->getJsonConfig());

                $variantAttributes = array();
                foreach ($variantOptions['attributes'] as $attributeId => $options)
                {
                    $code = @$options['code'];
                    foreach ($options['options'] as $option)
                    {
                        $value = @$option['label'];
                        $price = @$option['price'];
                        $valueId = @$option['id'];
                        foreach ($option['products'] as $productId)
                        {
                            //$children[] = $productId;
                            $variantAttributes[$productId][$code]['value'] = $value;
                            $variantAttributes[$productId][$code]['price'] = $price;
                            $variantAttributes[$productId][$code]['valueId'] = $valueId;
                            $variantAttributes[$productId][$code]['attributeId'] = $attributeId;
                        }
                    }
                }
                $variantOptions['refactoredOptions'] = $variantAttributes;


                foreach ($children as $variantId)
                {
                    /** @var Mage_Catalog_Model_Product $variant */
                    // $variant = Mage::getModel('catalog/product')->load($variantId);
                    $productRepository = $objectManager->create('Magento\Catalog\Model\ProductFactory')->create();
                    $variant = $productRepository->load($productId);

                    $productData = $this->getProductInfo($variant, $parent, $variantOptions, $lastUpdate, $priceCurrency, $priceCurrencyRate);
                    if (!empty($productData))
                    {
                        $products[] = $productData;
                    }
                }
            }
        }
        else
        {
            $products[] = $this->getProductInfo($product, null, null, null, $priceCurrency, $priceCurrencyRate);
        }

        return $products;
    }

    public function getStockQuantity($itemId, $store = null)
    {
        // $product = Mage::getModel('catalog/product')->load($itemId);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productRepository = $objectManager->create('Magento\Catalog\Model\ProductFactory')->create();
        $product = $productRepository->load($itemId);

        /* @var Mage_CatalogInventory_Model_Stock_Item $stockItem */
        $stockItem = $objectManager->create('\Magento\CatalogInventory\Model\Stock\Item')->setProduct($product);
        // $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);

        return $stockItem->getQty();
    }
}


