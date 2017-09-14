<?php
/**
 * Author: Nhan Nguyen
 * Date: 25/01/2017
 */

namespace Gos\Quickar\Controller\Finder;

class Car extends \Magento\Framework\App\Action\Action
{
    protected $layoutFactory;
    protected $request;
    protected $productFactory;
    protected $categoryFactory;

    public function __construct(\Magento\Framework\App\Action\Context $context,
                                \Magento\Framework\View\LayoutFactory $layoutFactory,
                                \Magento\Framework\App\Request\Http $request,
                                \Magento\Catalog\Model\ProductFactory $productFactory,
                                \Magento\Catalog\Model\CategoryFactory $categoryFactory)
    {
        $this->request = $request;
        $this->layoutFactory = $layoutFactory;
        $this->productFactory = $productFactory;
        $this->categoryFactory = $categoryFactory;
        return parent::__construct($context);
    }

    public function execute()
    {
        $result = array();
        $params = $this->request->getPost();
        $productCollection = $this->productFactory->create()->getCollection();
        $productCollection->addAttributeToSelect(array('tradein_model', 'tradein_model', 'tradein_year', 'tradein_trade_min'));

        if (isset($params['car_id'])) {
            $productCollection->addAttributeToFilter('entity_id', $params['car_id']);
        }

        $categoryCollection = $this->categoryFactory->create()->getCollection();
        $categoryCollection->addAttributeToFilter('name', 'Trade In');

        if ($categoryCollection->getSize() > 0) {
            $tradeInCategory = $categoryCollection->getFirstItem();
        }

        $product = $productCollection->getFirstItem();
        $result['product'] = $product->getData();

        if ($tradeInCategory) {
            $categoryIds = $product->getCategoryIds();

            foreach ($categoryIds as $key => $value) {
                if ($value == $tradeInCategory->getId()) {
                    unset($categoryIds[$key]);
                }
            }

            $result['category_id'] = reset($categoryIds);
        }

        echo json_encode($result);
    }
}