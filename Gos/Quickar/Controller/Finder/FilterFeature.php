<?php
/**
 * Created by PhpStorm.
 * User: doanthuan
 * Date: 10/5/2016
 * Time: 6:12 PM
 */

namespace Gos\Quickar\Controller\Finder;

class FilterFeature extends \Magento\Framework\App\Action\Action {

    protected $productFactory;
    protected $request;
    protected $eavConfig;

    public function __construct(\Magento\Framework\App\Action\Context $context,
                                \Magento\Framework\View\LayoutFactory $layoutFactory,
                                \Magento\Framework\App\Request\Http $request,
                                \Magento\Catalog\Model\ProductFactory $productFactory,
                                \Magento\Eav\Model\Config $eavConfig) 
    {
        $this->request = $request;
        $this->productFactory = $productFactory;
        $this->eavConfig = $eavConfig;
        return parent::__construct($context);
    }

    public function execute() {
        //$result = 'Nguyen';
        $params = $this->request->getPost();

        $result = \Gos\Quickar\Helper\Filter::filterFeature($this->productFactory, $this->eavConfig, $params);
        
        echo json_encode($result);
    }
    
}