<?php

/**
 * Created by PhpStorm.
 * User: doanthuan
 * Date: 10/6/2016
 * Time: 2:56 PM
 */
namespace Gos\Quickar\Block;

use Magento\Catalog\Model\ProductFactory;

class Finder extends \Magento\Framework\View\Element\Template
{
    protected $_productFactory;
    protected $eavConfig;
    protected $defaultValSlider;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        ProductFactory $productFactory,
        \Magento\Eav\Model\Config $eavConfig
    )
    {
        $this->_productFactory = $productFactory;
        $this->eavConfig = $eavConfig;
        $this->defaultValSlider = array(
            'payPerMonth' => 450,
            'duration' => 48,
            'initPayment' => 750,
            'numMiles' => 10000
        );

        parent::__construct($context);
    }

    public function getDefaultSlider() {
        return $this->defaultValSlider;
    }

    public function getPropertyCounts() {
        $result = \Gos\Quickar\Helper\Filter::filterFeature($this->_productFactory, $this->eavConfig, $this->defaultValSlider);

        return $result;
    }

    public function countCarModelItems($val) {

        
        $condition = array();
        
        $val = str_replace(' ','%',$val);
        $condition[] = array('like' => '%'.$val.'%');

        $collection = \Magento\Framework\App\ObjectManager::getInstance()
        ->create('Magento\Catalog\Model\ResourceModel\Product\Collection')
        ->addFieldToFilter('type_id','bundle')
        ->addAttributeToFilter('name',$condition)
        ->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);

        return count($collection);

    }

}