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

class Save extends \Magento\Framework\App\Action\Action
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
        $this->_catalogSession        = $catalogSession;
		
    }

    public function execute()
    {
	
	// get license data
	$amountOwing = $this->getRequest()->getPost('amount_owing');
    $tradeinValue = $this->getRequest()->getPost('tradein_value');
    $tradeinVehicle = $this->getRequest()->getPost('tradein_vehicle');

	$licensePlate = $this->getRequest()->getPost('license_plate');
	$tradeinState = $this->getRequest()->getPost('tradein_state');
	$carYear = $this->getRequest()->getPost('car_year');



	//$valuation = 9000;

    $tradein = $this->_tradeinFactory->create();
    $tradein->load($licensePlate,'license');
    $nvic = $tradein->getNvic();

    if ($tradeinValue > 0) {

        $responseData['status'] = 1;
        $responseData['content'] = $amountOwing;

		$this->_tradeinSession->setTradeinSession($amountOwing);
		$this->_tradeinSession->setTradeinOwing($amountOwing);
        $this->_tradeinSession->setTradeinValue($tradeinValue);
        $this->_tradeinSession->setTradeinVehicle($tradeinVehicle);
		$this->_tradeinSession->setTradeinState($tradeinState);
		$this->_tradeinSession->setTradeinLicense($licensePlate);
		$this->_tradeinSession->setCarYear($carYear);
        $this->_tradeinSession->setNvic($nvic);

    }else{

        $responseData['status'] = 0;
        $responseData['content'] = 0;

    }
		$newValuation = $this->_tradeinSession->getTradeinValue();

        $checkoutParams = $this->_catalogSession->getData('qc_checkout_data');

        if (!$checkoutParams) {
            $checkoutParams = [];
        }

        $checkoutParams['trade_in'] = $tradeinValue;
        $checkoutParams['amount_owning'] = $amountOwing;

        $this->_catalogSession->setData('qc_checkout_data', $checkoutParams);

		$responseData['content'] = $newValuation;

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseData);
        return $resultJson;

    }

    
}