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

class Destroy extends \Magento\Framework\App\Action\Action
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
        $this->_catalogSession         = $catalogSession;
        $this->_tradeinFactory        = $tradeinFactory;
		
    }

    public function execute()
    {
	
	 

        $responseData['status'] = 1;
        $responseData['content'] = 0;

		$this->_tradeinSession->unsTradeinSession();
        $this->_tradeinSession->unsTradeinOwing();
        $this->_tradeinSession->unsTradeinValue();
        $this->_tradeinSession->unsTradeinVehicle();
        
            $checkoutParams = $this->_catalogSession->getData('qc_checkout_data');

            //print_r($checkoutParams); die();

            if (!$checkoutParams) {
                $checkoutParams = [];
            }

            $checkoutParams['trade_in'] = 0;
            $checkoutParams['amount_owning'] = 0;

            $this->_catalogSession->setData('qc_checkout_data', $checkoutParams);

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseData);
        return $resultJson;

    }

    
}