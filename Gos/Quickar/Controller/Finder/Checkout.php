<?php
/**
 * Created by PhpStorm.
 * User: doanthuan
 * Date: 10/5/2016
 * Time: 6:12 PM
 */

namespace Gos\Quickar\Controller\Finder;

use Magento\Framework\Controller\ResultFactory;

class Checkout extends \Magento\Framework\App\Action\Action
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
        \Magento\Checkout\Model\Session $checkoutSession
    )
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
        $postParams = $this->getRequest()->getParams();

        if(!isset($postParams['selectedCarOption']) || empty($postParams['selectedCarOption'])){
            //throw new \Exception('Please select car option');
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl('/car/finder');
            return $resultRedirect;
        }

        $postParams['selectedAccessories'] = explode(",", $postParams['selectedAccessories']);
        $params = [
            'product' => $postParams['product'],
            'related_product' => null,
            'bundle_option' => [
                $postParams['selectedCarOptionKey'] => $postParams['selectedCarOption'],
                $postParams['selectedAccessoriesKey'] => $postParams['selectedAccessories'],
            ],
            'qty' => 1
        ];
        //store some variables
        $checkoutParams = $this->_catalogSession->getData('qc_checkout_data');
        if(!$checkoutParams){
            $checkoutParams = [];
        }
        if(isset($postParams['duration'])){
            $checkoutParams['duration'] = $postParams['duration'];
        }
        if(isset($postParams['init_payment'])){
            $checkoutParams['init_payment'] = $postParams['init_payment'];
        }
        if(isset($postParams['monthly_payment'])){
            $checkoutParams['monthly_payment'] = $postParams['monthly_payment'];
        }
        if(isset($postParams['trade_in'])){
            $checkoutParams['trade_in'] = $postParams['trade_in'];
        }
        if(isset($postParams['amount_credit'])){
            $checkoutParams['amount_credit'] = $postParams['amount_credit'];
        }
        if(isset($postParams['rate'])){
            $checkoutParams['rate'] = $postParams['rate'];
        }
        if(isset($postParams['miles'])){
            $checkoutParams['miles'] = $postParams['miles'];
        }
        if(isset($postParams['payment_option'])){
            $checkoutParams['payment_option'] = $postParams['payment_option'];
        }
        if(isset($postParams['total_amount'])){
            $checkoutParams['total_amount'] = $postParams['total_amount'];
        }
        if(isset($postParams['amount_owning'])){
            $checkoutParams['amount_owning'] = $postParams['amount_owning'];
        }
        if(isset($postParams['amount_owning'])){
            $checkoutParams['amount_owning'] = $postParams['amount_owning'];
        }
        if(isset($postParams['amount_owning'])){
            $checkoutParams['amount_owning'] = $postParams['amount_owning'];
        }
        if(isset($postParams['amount_owning'])){
            $checkoutParams['amount_owning'] = $postParams['amount_owning'];
        }
        if(isset($postParams['product'])){
            $checkoutParams['product'] = $postParams['product'];
        }
        if(isset($postParams['option_price'])){
            $checkoutParams['option_price'] = $postParams['option_price'];
        }
        if(isset($postParams['option_color'])){
            $checkoutParams['option_color'] = $postParams['option_color'];
        }
        if(isset($postParams['accessory_price'])){
            $checkoutParams['accessory_price'] = $postParams['accessory_price'];
        }

        $this->_catalogSession->setData('qc_checkout_data', $checkoutParams);

        $productId = (int)$this->getRequest()->getParam('product');
        $product = $this->_productRepository->getById($productId);

        // Clear cart
        $allItems = $this->_checkoutSession->getQuote()->getAllVisibleItems();

        foreach ($allItems as $item) {
            $itemId = $item->getItemId();
//            $itemSku = $item->getSku();
//
//            if (strpos(strtolower($itemSku), 'tradein') === false) {
//                $this->_cart->removeItem($itemId)->save();
//            }

            $this->_cart->removeItem($itemId)->save();
        }

        $this->_cart->addProduct($product, $params);
        $this->_cart->save();

        $this->_eventManager->dispatch(
            'checkout_cart_add_product_complete',
            ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
        );

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl('/checkout/#part_exchange');
        return $resultRedirect;
    }
}