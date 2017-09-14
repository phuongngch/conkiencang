<?php
namespace Gos\Order\Block;

class Ordertest extends \Magento\Framework\View\Element\Template
{
    protected $_orderFactory;

    public function _construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Gos\Order\Model\OrderFactory $orderFactory)
    {
        parent::_construct($context);
        $this->_orderFactory = $orderFactory;
    }

    // public function _prepareLayout()
    // {
    //     $order = $this->_orderFactory->create();
    //     $collection = $order->getCollection();

    //     foreach($collection as $item) {
    //         var_dump($item->getData());
    //     }

    //     exit;
    // }

    public function sayHello()
    {
        return __('Hello World');
    }
}
