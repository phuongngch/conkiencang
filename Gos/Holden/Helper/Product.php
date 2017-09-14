<?php

namespace Gos\Holden\Helper;

class Product extends \Magento\Framework\App\Helper\AbstractHelper
{
	const BASE_API_URL = 'http://www.holden.com.au';

	const BASE_API_REQUEST = '/api/2/vehicles?showprice=true&postcode=3000';

	const ATTRIBUTE_SET_ID = 19;

    protected $enityAttribute;

    protected $productAttributeRepository;

    protected $zendClientFactory;

    protected $directoryList;

    protected $fileCsv;

    protected $productFactoryModel;

    protected $productFactory;

    protected $objectManager;

    protected $logger;

    protected $base_api_url;

    protected $base_api_request;

    protected $attribute_set_id;

    public function __construct(
    	\Magento\Framework\App\Helper\Context $context,
        \Magento\Eav\Model\Entity\Attribute $enityAttribute,
        \Magento\Catalog\Model\Product\Attribute\Repository $productAttributeRepository,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\File\Csv $fileCsv,
        \Magento\Catalog\Model\Product $productFactoryModel,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\HTTP\ZendClientFactory $zendClientFactory
    )
    {
    	$this->productAttributeRepository = $productAttributeRepository;
        $this->enityAttribute = $enityAttribute;
        $this->zendClientFactory = $zendClientFactory;
        $this->directoryList = $directoryList;
        $this->fileCsv = $fileCsv;
        $this->productFactoryModel = $productFactoryModel;
        $this->productFactory = $productFactory;
        $this->objectManager = $objectManager;
        // Logger
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/holden.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $this->logger = $logger;

        parent::__construct($context);

        $this->base_api_url = $this->getConfigValue('base_api_url', false) ? $this->getConfigValue('base_api_url', false) : self::BASE_API_URL;
        $this->base_api_request = $this->getConfigValue('base_api_request', false) ? $this->getConfigValue('base_api_request', false) : self::BASE_API_REQUEST;
        $this->atrribute_set_id = $this->getConfigValue('atrribute_set_id', false) ? $this->getConfigValue('atrribute_set_id', false) : self::ATTRIBUTE_SET_ID;
    }

    public function isEnabled()
    {
        return (bool)$this->getConfigValue('enabled', false);
    }

    public function getConfigValue($key = null, $defaultValue = null)
    {
        $value = $this->scopeConfig->getValue(
            'gosholden/settings/' . $key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if (empty($value)) {
            $value = $defaultValue;
        }

        return $value;
    }

    public function executeCron()
    {
        if ($this->isEnabled())
        {
        	$this->getDataFromAPI();
        	//$this->getPriceFromAPI();
        	$this->objectManager->create('Magento\Indexer\Model\Processor')->reindexAll();
        }
    }

    public function executeCronPrice()
    {
        if ($this->isEnabled())
        {
        	$this->getPriceFromAPI();
        	$this->objectManager->create('Magento\Indexer\Model\Processor')->reindexAll();
        }
    }

    protected function getDataFromCSV() {
		$this->logger->info('---Run HOLDEN sync script via CSV---');
		$directory = $this->directoryList->getPath('media');
		$fileCsv = $this->fileCsv;
		$file = $directory . '/import/csv' . '/holden.csv';

		if (file_exists($file)) {
		    $data = $fileCsv->getData($file);
		    $this->logger->info('Load file csv: '.(count($data)-1)); 
		    for($i=1; $i<count($data); $i++) {
				$productData = array();
				$productData['name'] = $data[$i][0];
				$productData['description'] = $data[$i][1];
				$productData['sku'] = $data[$i][2];
				$productData['price'] = $data[$i][3];   
		        $this->createProduct($productData, 100);
		    }
		    rename($file,$file.'.bak');
		}
		$this->logger->info('---Finished HOLDEN sync script via CSV---');
    }

    protected function getDataFromAPI() {
    	$this->logger->info('---Run HOLDEN sync script via API---');
    	$listProduct = $this->makeRequest($this->base_api_request);
    	$this->logger->info('Load list product: '.count($listProduct));
    	foreach ($listProduct as $productApi) {
    		$this->logger->info('-------');
    		$this->logger->info('Sync product: '.$productApi->id);
    		if ($productApi->_self)
    		{
    			$productApiData = $this->makeRequest($productApi->_self);
    			foreach ($productApiData->models as $index => $model) {
	    			// Get model data	
    				$modelData = $this->makeRequest($model->_self);

                    // specifications
                    if (count($modelData->specifications) > 0)
                    {
                        $productModels = array();
                        foreach ($modelData->specifications as $specification) {
                            // Get color data
                            $colours = $this->makeRequest($modelData->colours->_self);
                            $productModels = array();
                            $configImage = array();
                            $sku = substr($specification->_self, strrpos($specification->_self, '/') + 1);
                            foreach ($colours as $color) {
                                // Create simple product
                                $productData = array();
                                $productData['name'] = $productApi->name . " - " . $model->name . " - " . $specification->name . " - " . $color->name . " - ". $productApiData->currentYear;
                                $this->logger->info('Simple: '.$productData['name']); // Log and check the simple product name
                                if (isset($model->description) && isset($color->description))
                                {
                                    $productData['description'] = $model->description . " " . $color->description;
                                    $productData['short_description'] = $model->description . " " . $color->description;
                                }
                                else
                                {
                                    $productData['description'] = $productApi->name;
                                    $productData['short_description'] = $productApi->name;  
                                }
                                $productData['sku'] = $sku . "-" . ucfirst(strtolower(str_replace(' ','-',(string) rtrim(preg_replace("/\([^)]+\)/","",$color->name)))));
                                $productData['price'] = (float)$productApi->baseDriveAwayPrice->price;
                                $productData['holden_model'] = $this->getAttribute('holden_model', $model->name);
                                $productData['holden_colour'] = $this->getAttribute('holden_colour', $color->name); 
                                $productData['tradein_model'] = $model->bodyCategoryName;
                                $productData['year'] = $productApiData->currentYear;
                                $productData['store_id'] = 0;
                                $productData['type_id'] = 'simple'; 

                                // Holden Data
                                foreach ($modelData as $key => $value) {
                                    if ($this->enityAttribute->getIdByCode('catalog_product', 'holden_'.$key))
                                    {
                                        if ($key == 'specifications')
                                        {
                                            $specificationData = $this->makeRequest($specification->_self);
                                            $productData['holden_specifications'] = '';
                                            foreach ($specificationData->specifications as $value) {
                                                $productData['holden_specifications'] = $productData['holden_specifications'] . "\n" . $value->categoryName . ': ' .$value->name;
                                            }
                                        }
                                        else $productData['holden_'.$key] = $this->getAttributeData((string)$modelData->$key->_self);
                                    }
                                }
                

                                // Holden features Data
                                foreach ($modelData->features as $feature) {
                                    $key = strtolower(str_replace(' ','_',(string) $feature->name));
                                    if ($this->enityAttribute->getIdByCode('catalog_product', 'holden_'.$key))
                                    {
                                        $productData['holden_'.$key] = $this->getAttributeData((string)$feature->_self);
                                    }
                                }

                                // Holden engine and transmission
                                $specificationData = $this->makeRequest($specification->_self);
                                $productData['holden_engine_and_transmission'] = $specificationData->engine->name."\n";
                                $productData['holden_engine_and_transmission'] .= $specificationData->transmission->name;

                                $simpleId = $this->createProduct($productData, 50, (string)$color->colourModelImage->id, $this->base_api_url . $color->colourModelImage->imageLocation);
                                $configImage[] = array((string)$color->colourModelImage->id,$color->colourModelImage->imageLocation);
                                if ($simpleId) {
                                    $simpleProduct = $this->productFactory->create()->load($simpleId);
                                    // $productModels[] = $simpleId;
                                    $link = $this->objectManager->create('Magento\Bundle\Model\Link');
                                    $link->setPosition(0);
                                    $link->setSku($simpleProduct->getSku());
                                    $link->setIsDefault(false);
                                    $link->setQty(1);
                                    // $link->setPrice(10);
                                    // $link->setPriceType(\Magento\Bundle\Api\Data\LinkInterface::PRICE_TYPE_FIXED);

                                    $productModels[] = $link;
                                }
                            }    

                            // bundle
                            $productData = array();
                            if (isset($model->description))
                            {
                                $productData['description'] = $productApi->description . " " . $model->description;
                                $productData['short_description'] = $productApi->description . " " . $model->description;                    
                            }
                            else
                            {
                                $productData['description'] = $productApi->name;
                                $productData['short_description'] = $productApi->name;
                            }
                            $productData['sku'] = $sku;
                            $productData['price'] = (float)$productApi->baseDriveAwayPrice->price;
                            $productData['type_id'] = 'bundle'; 
                            $productData['tradein_model'] = $model->bodyCategoryName;
                            $productData['year'] = $productApiData->currentYear;
                            $productData['price_view'] = 0; 
                            $productData['price_type'] = 0; 
                            $productData['sku_type'] = 0; 
                            $productData['store_id'] = 0;
                            $productData['name'] = $productApi->name . " - " . $model->name . " - " . $specification->name . " - " .$productApiData->currentYear;
                            $productData['transmission_type'] = $specification->name;

                            $this->logger->info('Bundle: '.$productData['name']); // Log and Check bundle product name

                            // Holden engine and transmission
                            $specificationData = $this->makeRequest($specification->_self);
                            $productData['holden_engine_and_transmission'] = $specificationData->engine->name."\n";
                            $productData['holden_engine_and_transmission'] .= $specificationData->transmission->name;

                            // create parrent product
                            $configurableId = $this->createProduct($productData, 10, 'null', false, $productModels, $configImage); 
                        }
                    }
    			}

    			// Create configurable product
    			// $productData = array();
    			// $productData['name'] = $productApi->name . ' config';
    			// $productData['description'] = $productApi->description. ' config';
    			// $productData['sku'] = $productApi->id .'-config';
    			// $productData['price'] = (float)$productApi->baseDriveAwayPrice->price;
    			// $productData['type_id'] = 'configurable';	

    			// // bundle
    			// $productData = array();
    			// $productData['name'] = $productApi->name ;
    			// $productData['description'] = $productApi->description;
    			// $productData['short_description'] = $productApi->description;
    			// $productData['sku'] = $productApi->id .'-bundle';
    			// $productData['price'] = (float)$productApi->baseDriveAwayPrice->price;
    			// $productData['type_id'] = 'bundle';	
    			// $productData['tradein_model'] = $productApiData->models[0]->bodyCategoryName;
    			// $productData['tradein_year'] = $productApiData->currentYear;
    			// $productData['price_view'] = 0;	
    			// $productData['store_id'] = 0;

    			// // create parrent product
    			// $urlImage = $this->makeRequest($productApiData->vehicleImage->_self);
    			// $configurableId = $this->createProduct($productData, 10, 'null', false, $productModels, $configImage);
    		}
    	}
    	$this->logger->info('---Finished HOLDEN sync script via API---');
    }

    protected function getPriceFromAPI() {
    	$this->logger->info('---Run HOLDEN sync price script via API---');
    	$listProduct = $this->makeRequest($this->base_api_request);
    	$this->logger->info('Load list product: '.count($listProduct));
    	foreach ($listProduct as $productApi) {
    		$this->logger->info('-------');
    		$this->logger->info('Sync price product: '.$productApi->id);
    		$configImage = array();
    		if ($productApi->_self)
    		{
    			$productModels = array();
    			$stores = array(3000 => 1, 2000 => 7, 4000 => 2);
    			foreach ($stores as $postcode => $store) {
    				$this->logger->info('Sync price product for store : '. $store);
	    			$productApiData = $this->makeRequest($productApi->_self . '?showprice=true&postcode=' . $postcode);
	    			foreach ($productApiData->models as $index => $model) {
		    			// Get model data	
	    				$modelData = $this->makeRequest($model->_self . '?showprice=true&postcode=' . $postcode);

                        // specifications
                        if (count($modelData->specifications) > 0)
                        {
                            foreach ($modelData->specifications as $specification) {
                                // Get color data
                                $colours = $this->makeRequest($modelData->colours->_self . '?showprice=true&postcode=' . $postcode);
                                $sku = substr($specification->_self, strrpos($specification->_self, '/') + 1);
                                foreach ($colours as $color) {
                                    // Create simple product
                                    $productData = array();
                                    $productData['sku'] = $sku . "-" . ucfirst(strtolower(str_replace(' ','-',(string) rtrim(preg_replace("/\([^)]+\)/","",$color->name)))));
                                    if (isset($specification->baseDriveAwayPrice->price))
                                          $productData['price'] = (float)$specification->baseDriveAwayPrice->price + (float)$color->colourPrice;
                                    else $productData['price'] = 0;
                                    try {
                                        $product = $this->productFactory->create();
                                        if ($productId = $this->productFactoryModel->getIdBySku($productData['sku']))
                                        {
                                            $product->load($productId);
                                            $product->setStoreId($store);
                                            $product->setPrice($productData['price']);
                                            $product->save();
                                            $this->logger->info($productData['sku'].'='.$productData['price']);
                                            echo $productData['sku'].'='.$productData['price']."\n";
                                        }
                                    } catch (Exception $e) {
                                        $this->logger->info('This product price has not been synced');
                                        $this->logger->info($e->getMessage());
                                    }
                                }
                            }
                        }
	    			}
    			}
    		}
    	}
    	$this->logger->info('---Finished HOLDEN sync price script via API---');
    }

	protected function createProduct($productData, $qty, $imageId = 'null', $urlImage = false, $productModels = false, $configImage = false)
	{
		try {
			$this->logger->info('Syncing holden product id #'.$productData['sku']);
			$product = $this->productFactory->create();
			$image = $imageId . '.png';
			$updateImage = true;
			if ($productId = $this->productFactoryModel->getIdBySku($productData['sku']))
			{
				$product->load($productId);
				$productBaseData = $product->getData();
				$gallery = $product->getMediaGalleryImages();
				foreach ($gallery as $galleries) {
					if (strpos(basename($galleries['file']),$imageId) !== false) 
						{
							$updateImage = false;
							break;
						}
				}
				// stock data
				$stockData = array(
				    'use_config_manage_stock' => 0, //'Use config settings' checkbox
				    'manage_stock' => 1, //manage stock
				    'min_sale_qty' => 1, //Minimum Qty Allowed in Shopping Cart
				    'max_sale_qty' => 10, //Maximum Qty Allowed in Shopping Cart
				    'is_in_stock' => 1, //Stock Availability
				    'qty' => $qty //qty
			    );	
			    $productBaseData['stock_data'] = $stockData;
			    unset($productData['price']);  
                unset($productData['name']);  
			}
			else
			{
				// stock data
				$stockData = array(
				    'use_config_manage_stock' => 0, //'Use config settings' checkbox
				    'manage_stock' => 1, //manage stock
				    'min_sale_qty' => 1, //Minimum Qty Allowed in Shopping Cart
				    'max_sale_qty' => 10, //Maximum Qty Allowed in Shopping Cart
				    'is_in_stock' => 1, //Stock Availability
				    'qty' => $qty //qty
			    );	
			    // base product data
				$productBaseData = array(
					'attribute_set_id' => $this->atrribute_set_id,
					'website_ids' => array(1,2,3),
					'visibility' => $productData['type_id'] == 'simple' ? 1 : 4,
					'status' => 1,
					'category_ids' => $productData['type_id'] == 'simple' ? array() : array(4,5),
					'stock_data' => $stockData
				);
			}
			$productData = array_merge($productBaseData,$productData);
			$product->setData($productData);
			$product->setQuantityAndStockStatus($stockData);
			if ($updateImage && $urlImage)
			{
				$directory = $this->directoryList->getPath('media');
				$filePath = $directory . '/import' . '/' . $image;
				file_put_contents($filePath, file_get_contents($urlImage));
				if (file_exists($filePath))
				{
					$product->setMediaGallery(array('images'=>array (), 'values'=>array ()));
					$product->addImageToMediaGallery($filePath, array('image','thumbnail','small_image'), false, false);
				}
			}

			$accessoriesModels = array();
			if ($accessoriesConfig = $this->getConfigValue('accessories_sku', false))
			{
				$accessoriesConfig = explode(',', $accessoriesConfig);
				foreach ($accessoriesConfig as $value) {
					$link = $this->objectManager->create('Magento\Bundle\Model\Link');
					$link->setPosition(0);
					$link->setSku($value);
					$link->setIsDefault(false);
					$link->setQty(1);
					$accessoriesModels[] = $link;
				}
			}
			$this->assignBundleProduct($product, $productModels, $accessoriesModels,$configImage);

			$product->save();
			$this->logger->info('This product has been synced');
			echo $product->getId() . " product has been synced\n";
			return $product->getId();
		} catch (Exception $e) {
			$this->logger->info('This product has not been synced');
			$this->logger->info($e->getMessage());
		}
		return false;
	}

	protected function assignProduct($configurableId, $productModels = false)
	{
		try {
			// Assgin simple product to configurable product
			$product = $this->productFactory->create()->load($configurableId);
			if ($productModels)
			{
				$modelAttributeId = $this->enityAttribute->getIdByCode('catalog_product', 'holden_model');
				$colourAttributeId = $this->enityAttribute->getIdByCode('catalog_product', 'holden_colour');
				$superAttribute = $product->getTypeInstance()->getUsedProductAttributeIds($product);
				$attributes = array($modelAttributeId,$colourAttributeId);
				$availableAttribute = array_diff($attributes,$superAttribute);
				if (count($availableAttribute)>0)
				{
					$product->getTypeInstance()->setUsedProductAttributeIds(array($modelAttributeId,$colourAttributeId),$product); 
				}					

			    $configurableAttributesData = $product->getTypeInstance()->getConfigurableAttributesAsArray($product);
			    $product->setCanSaveConfigurableAttributes(true);
			    $product->setConfigurableAttributesData($configurableAttributesData);

				$product->setAssociatedProductIds($productModels);

				$product->save();
				$this->logger->info('This product has been assigned');
			}
		} catch (Exception $e) {
			$this->logger->info('This product has not been assigned');
			$this->logger->info($e->getMessage());
		}
	}

	protected function assignBundleProduct($product, $productModels = false, $accessoriesModels = false, $configImage = false)
	{
		// Assgin simple product to configurable product
		if ($productModels)
		{
			$productExtension = $product->getExtensionAttributes();
			$bundleProductOptions = $productExtension->getBundleProductOptions();

			if (count($bundleProductOptions) == 0)
			{
				// Car option
				$car = $this->objectManager->create('Magento\Bundle\Model\Option');
				$car->setTitle('Car');
				$car->setType('select');
				$car->setRequired(true);
				$car->setPosition(1);
				$car->setProductLinks($productModels);

				// Accessories option
				$accessories = $this->objectManager->create('Magento\Bundle\Model\Option');
				$accessories->setTitle('Accessories');
				$accessories->setType('multi');
				$accessories->setRequired(true);
				$accessories->setPosition(2);
				$accessories->setProductLinks($accessoriesModels);

				$productExtension->setBundleProductOptions([$car,$accessories]);
				$product->setExtensionAttributes($productExtension);

				$this->logger->info('This product has been assigned');
			}
		}

        $gallery = $product->getMediaGalleryImages();
		if ($configImage && count($gallery) == 0)
		{
			$product->setMediaGallery(array('images'=>array (), 'values'=>array ()));
			$base = false;
			foreach ($configImage as $image) {
				$updateImage = true;
				if (is_array($gallery))
				foreach ($gallery as $galleries) {
					if (strpos(basename($galleries['file']),$image[0]) !== false) 
						{
							$updateImage = false;
							break;
						}
				}
				$directory = $this->directoryList->getPath('media');
				$filePath = $directory . '/import' . '/' . $image[0] . '.png';
				if ($updateImage)
				{
					if (!file_exists($filePath)) 
						file_put_contents($filePath, file_get_contents($image[1]));
					if(!$base) {
					    $product->addImageToMediaGallery($filePath, array('image','thumbnail','small_image'), false, false);
					    $base = true;
					}
					else $product->addImageToMediaGallery($filePath, array(), false, false);
				}
			}
			$this->logger->info('This product has been updated image');
		}	
	}

	protected function getAttribute($arg_attribute, $arg_value) {
		$result = $this->getAttributeOptionValue($arg_attribute, $arg_value);
		if (!$result)
		{
			$result = $this->addAttributeOption($arg_attribute, $arg_value);
		}
		return $result;
	}

	protected function getAttributeOptionValue($arg_attribute, $arg_value) {
		$options = $this->productAttributeRepository->get($arg_attribute)->getOptions();  

		$value = false;
		foreach($options as $option) {
			if (strtolower($option->getLabel()) == strtolower($arg_value)) {
				$value = $option->getValue();
				break;
			}
		}

		return $value;
	}

	protected function addAttributeOption($arg_attribute, $arg_value) {
		$attribute_code         = $this->enityAttribute->getIdByCode('catalog_product', $arg_attribute);
		$attribute              = $this->enityAttribute->load($attribute_code);

		$value['option'] = array($arg_value,$arg_value);
		$result = array('value' => $value);
		$attribute->addData(array('option' => $result));
		$attribute->save();

		return $this->getAttributeOptionValue($arg_attribute, $arg_value);
	}

	protected function getAttributeData($self) {
		$values = $this->makeRequest($self);
		$returnData = '';
		if (is_array($values) || is_object($values))
		foreach ($values as $value) {
			if(property_exists($value, "name"))
				$returnData = $returnData . "\n" . $value->name;
			else $returnData = $returnData . "\n" . (string)$value;
		}
		return $returnData;
	}

	protected function makeRequest($url, $method=\Zend_Http_Client::GET)
	{
		$client = $this->zendClientFactory->create();
		$client->setUri($this->base_api_url . $url);
		$client->setConfig(['maxredirects' => 0, 'timeout' => 30]);
		// $client->setRawData(utf8_encode($request));
		return json_decode($client->request($method)->getBody());	
	}
}