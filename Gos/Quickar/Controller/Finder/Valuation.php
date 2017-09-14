<?php
/**
 * Author: Nhan Nguyen
 * Date: 28/11/2016
 */

namespace Gos\Quickar\Controller\Finder;

class Valuation extends \Magento\Framework\App\Action\Action
{
    protected $layoutFactory;
    protected $request;
    protected $productFactory;
    protected $_catalogSession;

    public function __construct(\Magento\Framework\App\Action\Context $context,
                                \Magento\Framework\View\LayoutFactory $layoutFactory,
                                \Magento\Framework\App\Request\Http $request,
                                \Magento\Catalog\Model\ProductFactory $productFactory,
                                \Magento\Catalog\Model\Session $catalogSession)
    {
        $this->request = $request;
        $this->layoutFactory = $layoutFactory;
        $this->productFactory = $productFactory;
        $this->_catalogSession = $catalogSession;
        return parent::__construct($context);
    }

    public function execute()
    {
        $tradeInKeys = array('model', 'year');
        $params = $this->request->getPost();
        $productCollection = $this->productFactory->create()->getCollection();
        $productCollection->addAttributeToSelect(array('description', 'price', 'tradein_trade_min'));

        if (count($params) > 0) {
            foreach ($params as $key => $value) {
                if (in_array($key, $tradeInKeys)) {
                    $productCollection->addAttributeToFilter('tradein_'.$key, $value);
                }

                if ($key == 'make') {
                    $productCollection->addCategoriesFilter(array('in' => array($params['make'])));
                }
            }
        }

        if ($productCollection->getSize() > 0) {
            $tradeInProduct = $productCollection->getFirstItem();
        }

        if (empty($tradeInProduct)) {
            echo "{}";
        } else {
            $checkoutParams = $this->_catalogSession->getData('qc_checkout_data');

            if (!$checkoutParams) {
                $checkoutParams = [];
            }

            if (isset($params['mileage'])) {
                $checkoutParams['tradein_mileage'] = $params['mileage'];
            }

            $checkoutParams['trade_in'] = $tradeInProduct->getData('tradein_trade_min');
            $this->_catalogSession->setData('qc_checkout_data', $checkoutParams);
            echo json_encode($tradeInProduct->getData());
        }
    }
}