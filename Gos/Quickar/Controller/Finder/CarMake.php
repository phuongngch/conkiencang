<?php
/**
 * Author: Nhan Nguyen
 * Date: 30/11/2016
 */

namespace Gos\Quickar\Controller\Finder;

class CarMake extends \Magento\Framework\App\Action\Action
{
    protected $layoutFactory;
    protected $request;
    protected $categoryFactory;

    public function __construct(\Magento\Framework\App\Action\Context $context,
                                \Magento\Framework\View\LayoutFactory $layoutFactory,
                                \Magento\Framework\App\Request\Http $request,
                                \Magento\Catalog\Model\CategoryFactory $categoryFactory)
    {
        $this->request = $request;
        $this->layoutFactory = $layoutFactory;
        $this->categoryFactory = $categoryFactory;
        return parent::__construct($context);
    }

    public function execute()
    {
        return false;// Comment out this  function to make the checkout faster
        /*
        $result = array();
        $categoryCollection = $this->categoryFactory->create()->getCollection();
        $categoryCollection->addAttributeToFilter('name', 'Trade In');

        if ($categoryCollection->getSize() > 0) {
            $tradeInCategory = $categoryCollection->getFirstItem();
            $carMakes = $tradeInCategory->getChildren();
            $categoryCollection = $this->categoryFactory->create()->getCollection();
            $categoryCollection->addAttributeToSelect('name');
            $categoryCollection->addIdFilter(array('in' => explode(',', $carMakes)));

            foreach ($categoryCollection as $category) {
                $result[] = array('id' => $category->getId(), 'name' => $category->getName());
            }
            
            //echo json_encode($result);
        }
        */
    }
}