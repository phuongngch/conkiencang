<?php
namespace Gos\Quickar\Observer;

/**
 * Created by PhpStorm.
 * User: doanthuan
 * Date: 12/13/2016
 * Time: 11:23 AM
 */

use Magento\Framework\Event\ObserverInterface;

class MyObserver implements ObserverInterface
{
    protected $_catalogSession;
    protected $_date;

    public function __construct(
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Framework\Stdlib\DateTime\DateTime $date)
    {
        //$this->_logger = $logger;
        //parent::__construct($data);
        $this->_catalogSession = $catalogSession;
        $this->_date = $date;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $orderIds = $observer->getData('order_ids');

        if (isset($orderIds[0])) {
            $orderId = $orderIds[0];

            $this->_resources = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Framework\App\ResourceConnection');

            $connection= $this->_resources->getConnection();
            $qc_checkout_data = $this->_catalogSession->getData('qc_checkout_data');
            $qc_checkout_data['order_id'] = $orderId;
            $qc_checkout_data['created_at'] = $this->_date->date();
            $qc_checkout_data['updated_at'] = $this->_date->date();
            $themeTable = $this->_resources->getTableName('quickar_order_info');
            $columns = implode(", ", array_keys($qc_checkout_data));
            $escaped_values = array_values($qc_checkout_data);
            $values = "'" . implode("', '", $escaped_values) . "'";
            $sql = "INSERT INTO `{$themeTable}`({$columns}) VALUES ({$values})";
            $connection->query($sql);
        }
    }
}
