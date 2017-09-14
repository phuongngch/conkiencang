<?php
/**
 * Created by PhpStorm.
 * User: doanthuan
 * Date: 10/5/2016
 * Time: 6:12 PM
 */

namespace Gos\Quickar\Controller\Finder;

use Magento\Framework\Controller\ResultFactory;

class GetOrderInfo extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        ResultFactory $resultFactory

    )
    {
        $this->resultFactory = $resultFactory;
        $this->_pageFactory = $pageFactory;
        return parent::__construct($context);
    }


    public function execute()
    {
        $params = $this->getRequest()->getParams();
        if(!isset($params['order_id'])){
            throw new \Exception('Order Id requires');
        }
        $orderId = $params['order_id'];

        $this->_resources = \Magento\Framework\App\ObjectManager::getInstance()
            ->get('Magento\Framework\App\ResourceConnection');
        $connection= $this->_resources->getConnection();

        $themeTable = $this->_resources->getTableName('quickar_order_info');
        $sql = "SELECT * FROM " . $themeTable ." WHERE order_id = ".$orderId;

        $row = $connection->fetchRow($sql);

        echo json_encode($row);exit;

    }
}