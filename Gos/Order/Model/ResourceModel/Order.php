<?php
namespace Gos\Order\Model\ResourceModel;

class Order extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Date model
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * constructor
     *
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    )
    {
        $this->_date = $date;
        parent::__construct($context);
    }


    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('quickar_order_info', 'id');
    }

    /**
     * Retrieves Order Id from DB by passed id.
     *
     * @param string $id
     * @return integer
     */
    public function getOrderIdById($id)
    {
        $adapter = $this->getConnection();

        $select = $adapter->select()
            ->from($this->getMainTable(), 'order_id')
            ->where('id = :id');

        $binds = ['id' => (int)$id];
        return $adapter->fetchOne($select, $binds);
    }

    /**
     * before save callback
     *
     * @param \Magento\Framework\Model\AbstractModel|\Gos\Order\Model\Order $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $object->setUpdatedAt($this->_date->date());

        if ($object->isObjectNew()) {
            $object->setCreatedAt($this->_date->date());
        }

        return parent::_beforeSave($object);
    }
}
