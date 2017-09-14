<?php
namespace Gos\Order\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;
    protected $_orderFactory;
    protected $_orderHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $_pageFactory,
        \Gos\Order\Model\OrderFactory $orderFactory,
        \Gos\Order\Helper\Data $orderHelper)
    {
        $this->_pageFactory = $_pageFactory;
        $this->_orderFactory = $orderFactory;
        $this->_orderHelper = $orderHelper;
        return parent::__construct($context);
    }

    public function execute()
    {
        // var_dump($this->_orderHelper->infoFunc());
        return $this->_pageFactory->create();
        // $order = $this->_orderFactory->create();
        // $collection = $order->getCollection();

        // foreach($collection as $item) {
        //     var_dump($item->getData());
        // }
    }
}
