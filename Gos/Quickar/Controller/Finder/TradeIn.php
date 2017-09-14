<?php
/**
 * Author: Nhan Nguyen
 * Date: 30/11/2016
 */

namespace Gos\Quickar\Controller\Finder;

use Magento\Framework\Controller\ResultFactory;

class TradeIn extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;
    protected $_cart;
    protected $_productRepository;
    protected $_catalogSession;
    protected $_checkoutSession;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        ResultFactory $resultFactory,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Checkout\Model\Session $checkoutSession)
    {
        $this->_cart = $cart;
        $this->_productRepository = $productRepository;
        $this->resultFactory = $resultFactory;
        $this->_pageFactory = $pageFactory;
        $this->_catalogSession = $catalogSession;
        $this->_checkoutSession = $checkoutSession;
        return parent::__construct($context);
    }

    public function execute()
    {
        $productId = (int)$this->getRequest()->getParam('product');
        $standing_finance = $this->getRequest()->getParam('standing_finance');

        // Remove all trade in products
        $allItems = $this->_checkoutSession->getQuote()->getAllVisibleItems();

        foreach ($allItems as $item) {
            $itemId = $item->getItemId();
            $itemSku = $item->getSku();

            if (strpos(strtolower($itemSku), 'tradein') !== false) {
                $this->_cart->removeItem($itemId)->save();
            }
        }

        $params = [
            'product' => $productId,
            'qty' => 1
        ];

        $product = $this->_productRepository->getById($productId);
        $this->_cart->addProduct($product, $params);
        $this->_cart->save();
        $checkoutParams = $this->_catalogSession->getData('qc_checkout_data');

        if (!$checkoutParams) {
            $checkoutParams = [];
        }

        if (isset($standing_finance)) {
            $checkoutParams['standing_finance'] = $standing_finance;
        }

        $this->_catalogSession->setData('qc_checkout_data', $checkoutParams);
        echo json_encode(array(
            'name' => $product->getData('name'),
            'description' => $product->getData('description'),
            'tradein_trade_min' => $product->getData('tradein_trade_min')));
    }
}
