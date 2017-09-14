<?php
/**
 * Author: Nhan Nguyen
 * Date: 25/01/2017
 */

namespace Gos\Quickar\Controller\Cart;

class RemoveItem extends \Magento\Framework\App\Action\Action
{
    protected $layoutFactory;
    protected $request;
    protected $productFactory;
    protected $categoryFactory;
    protected $cart;

    public function __construct(\Magento\Framework\App\Action\Context $context,
                                \Magento\Framework\View\LayoutFactory $layoutFactory,
                                \Magento\Framework\App\Request\Http $request,
                                \Magento\Catalog\Model\ProductFactory $productFactory,
                                \Magento\Catalog\Model\CategoryFactory $categoryFactory,
                                \Magento\Checkout\Model\Cart $cart)
    {
        $this->request = $request;
        $this->layoutFactory = $layoutFactory;
        $this->productFactory = $productFactory;
        $this->categoryFactory = $categoryFactory;
        $this->cart = $cart;
        return parent::__construct($context);
    }

    public function execute()
    {
        $params = $this->request->getPost();
        $this->cart->removeItem($params['item_id'])->save();
        echo json_encode(array('message' => 'Removed an item from the cart successfully!'));
    }
}