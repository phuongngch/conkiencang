<?php
/**
 * Created by PhpStorm.
 * User: doanthuan
 * Date: 10/5/2016
 * Time: 6:12 PM
 */

namespace Gos\Quickar\Controller\Finder;

use Magento\Framework\Controller\ResultFactory;

class GetTradeInCart extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;
    protected $_cart;
    protected $_productRepository;


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        ResultFactory $resultFactory

    )
    {
        $this->_cart = $cart;
        $this->_productRepository = $productRepository;

        $this->resultFactory = $resultFactory;

        $this->_pageFactory = $pageFactory;
        return parent::__construct($context);
    }


    public function execute()
    {
        $items = $this->_cart->getQuote()->getAllItems();
        if(isset($items[0])){
            $productId = $items[0]->getData('product_id');
            $product = $this->_productRepository->getById($productId);
            $price = $product->getData('tradein_trade_min');
            echo $price;exit;
        }
        echo 0;exit;
    }
}