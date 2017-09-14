<?php
namespace Gos\Order\Model;

use Magento\Framework\Model\AbstractModel;

class Order extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface, \Gos\Order\Model\Api\Data\OrderInterface
{
    const CACHE_TAG = 'quickar_order_info';
    protected $_cacheTag = 'quickar_order_info';
    protected $_eventPrefix = 'quickar_order_info';

    protected function _construct()
    {
        $this->_init('Gos\Order\Model\ResourceModel\Order');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues()
    {
        $values = [];
        return $values;
    }

    public function getOrderId() {
        return 0;
    }
}
