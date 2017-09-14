<?php

namespace Gos\Quickar\Block\Product\View;

use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Attributes extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Product
     */
    protected $_product = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        if (!$this->_product) {
            $this->_product = $this->_coreRegistry->registry('product');
        }
        return $this->_product;
    }

    public function getAdditionalData(array $excludeAttr = [])
    {
        $data = [];
        $product = $this->getProduct();
        $attributes = $product->getAttributes();

        $groupIds = [
            101 => 'Engine',
            102 => 'Transmission',
            103 => 'Gear Ratio',
            104 => 'Dimensions',
            105 => 'Fuel',
            107 => 'Car Weight',
            108 => 'Towing Capacity',
            120 => 'General'
        ];
        $attributeSetId = 16;

        foreach ($attributes as $attribute) {
            if ($attribute->getIsVisibleOnFront() && !in_array($attribute->getAttributeCode(), $excludeAttr)) {
                $value = $attribute->getFrontend()->getValue($product);

                if (!$product->hasData($attribute->getAttributeCode())) {
                    $value = __('N/A');
                } elseif ((string)$value == '') {
                    $value = __('No');
                } elseif ($attribute->getFrontendInput() == 'price' && is_string($value)) {
                    $value = $this->priceCurrency->convertAndFormat($value);
                }

                foreach($groupIds as $groupId => $groupName){
                    if ($attribute->isInGroup($attributeSetId, $groupId) && is_string($value) && strlen($value)) {
                        if(!isset($data[$groupName])){
                            $data[$groupName] = [];
                        }
                        $data[$groupName][$attribute->getAttributeCode()] = [
                            'label' => __($attribute->getStoreLabel()),
                            'value' => $value,
                            'code' => $attribute->getAttributeCode(),
                        ];
                    }
                }
            }
        }
        return $data;
    }

    public function getTestData()
    {
        return [];
    }
}
