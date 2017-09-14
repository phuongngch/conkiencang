<?php
/**
 * Created by PhpStorm.
 * User: doanthuan
 * Date: 10/5/2016
 * Time: 6:12 PM
 */

namespace Gos\Quickar\Controller\Finder;

class Ajax extends \Magento\Framework\App\Action\Action
{
    protected $layoutFactory;
    protected $request;
    public function __construct(\Magento\Framework\App\Action\Context $context,
                                \Magento\Framework\View\LayoutFactory $layoutFactory,
                                \Magento\Framework\App\Request\Http $request
    )
    {
        $this->request = $request;
        $this->layoutFactory = $layoutFactory;
        return parent::__construct($context);
    }

    public function execute()
    {
        $params = $this->request->getPost();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $checkoutSession = $objectManager->get('\Magento\Checkout\Model\Session');
        if(!isset($params['first_time'])){
            $checkoutSession->setFilterParams($_POST);
        }
        else{
            unset($params['first_time']);
        }

        $output = $this->layoutFactory->create()
            ->createBlock('Magento\CatalogWidget\Block\Product\ProductsList')
            // ->createBlock('Gos\Quickar\Block\ProductsList')
            ->setTemplate('Gos_Quickar::products.phtml')
            ->setData('params',$params)
            ->setData('products_count',1000)
            ->toHtml();
        echo $output;
        //var_dump($params);
        exit();
       // $resultRaw = $this->resultRawFactory->create();
       // return $resultRaw->setContents();
    }

    
}