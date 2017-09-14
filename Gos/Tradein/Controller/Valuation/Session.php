<?php
/**
 * Gos_Tradein extension
 *                     NOTICE OF LICENSE
 * 
 *                     This source file is subject to the MIT License
 *                     that is bundled with this package in the file LICENSE.txt.
 *                     It is also available through the world-wide-web at this URL:
 *                     http://opensource.org/licenses/mit-license.php
 * 
 *                     @category  Gos
 *                     @package   Gos_Tradein
 *                     @copyright Copyright (c) 2017
 *                     @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Gos\Tradein\Controller\Valuation;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;

class Session extends \Magento\Framework\App\Action\Action
{
    /**
     * Tradein Factory
     * 
     * @var \Gos\Tradein\Model\TradeinFactory
     */
    protected $_tradeinFactory;
    protected $_tradeinSession;
    protected $_catalogSession;


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
		\Magento\Checkout\Model\Session $tradeinSession,
        \Magento\Catalog\Model\Session $catalogSession,
        \Gos\Tradein\Model\TradeinFactory $tradeinFactory
    ) {
        parent::__construct($context);
		$this->_tradeinSession 		  = $tradeinSession;
        $this->_tradeinFactory        = $tradeinFactory;
        $this->_catalogSession         = $catalogSession;
		
    }

    public function execute()
    {

        if ($this->_tradeinSession->getTradeinValue()) {
                 
            $responseData['tradein_state'] = $this->_tradeinSession->getTradeinState();
            $responseData['tradein_plate'] = $this->_tradeinSession->getTradeinLicense();
            $responseData['owingSession'] = $this->_tradeinSession->getTradeinOwing();
            $responseData['tradeInValuationSession'] = $this->_tradeinSession->getTradeinValue();
            $responseData['vehicle_nvic'] = $this->_tradeinSession->getNvic();
            //$this->_tradeinSession->getCarYear()
            //$this->_tradeinSession->getTradeinVehicle();

        }else{

                $responseData['owingSession'] = 0;
                $responseData['tradeInValuationSession'] = 0;
        }

        //$responseData = $this->_catalogSession->getData();

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseData);
		
        return $resultJson;

    }

    
}