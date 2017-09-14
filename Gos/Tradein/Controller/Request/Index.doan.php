<?php
namespace Gos\Tradein\Controller\Request;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
class Index extends \Magento\Framework\App\Action\Action
{

    protected $resultJsonFactory;
    protected $_tradeinFactory;

    /**
     * Helper Factory
     * 
     * @var \Gos\Tradein\Model\TradeinFactory
     */
    protected $_tradeinHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Gos\Tradein\Helper\Api $tradeinHelper,
        \Gos\Tradein\Model\TradeinFactory $tradeinFactory
    ) {
        parent::__construct($context);
        $this->_tradeinFactory        = $tradeinFactory;
        $this->_tradeinHelper        = $tradeinHelper;
    }
    public function execute()
    {
        $state = $this->getRequest()->getParam('state', false);
		$license = $this->getRequest()->getParam('license', false);
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
 		$connection = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION');
 		$results = $connection->fetchAll("SELECT * FROM gos_tradein_tradein WHERE `state` LIKE '$state' AND `license` LIKE '$license' LIMIT 1");
 		if(count($results) > 0)
		{
			$responseData['status'] = 1;
			$responseData['content'] = $results[0]['year'].' '.$results[0]['vehicle_make'].' '.$results[0]['vehicle_model'];
		}
		else
		{
			$responseData['status'] = 0;
		}
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseData);
        return $resultJson;

    }
}
