<?php
/**
 * Created by PhpStorm.
 * User: doanthuan
 * Date: 10/5/2016
 * Time: 6:12 PM
 */

namespace Gos\Quickar\Controller\Finder;

use Magento\Framework\Controller\ResultFactory;

class PaymentSummary extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;
    protected $_cart;
    protected $_productRepository;
    protected $_catalogSession;
    protected $_resultFactory;


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        ResultFactory $resultFactory,
        \Magento\Catalog\Model\Session $catalogSession

    )
    {
        $this->_cart = $cart;
        $this->_productRepository = $productRepository;
        $this->_resultFactory = $resultFactory;
        $this->_pageFactory = $pageFactory;
        $this->_catalogSession = $catalogSession;
        return parent::__construct($context);
    }

    public function execute()
    {
        $postParams = $this->getRequest()->getParams();
        $checkoutParams = $this->_catalogSession->getData('qc_checkout_data');
        $postParamsKeys = array_keys($postParams);

        if (!$checkoutParams) {
            $checkoutParams = [];
        }

        foreach ($postParamsKeys as $postParamsKey) {
            if (isset($postParams[$postParamsKey])) {
                $checkoutParams[$postParamsKey] = $postParams[$postParamsKey];
            }
        }

        $this->_catalogSession->setData('qc_checkout_data', $checkoutParams);
        $resultJson = $this->_resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData(array('message' => 'Updated payment summary successfully!', 'data' => $checkoutParams));
        return $resultJson;
    }
}
