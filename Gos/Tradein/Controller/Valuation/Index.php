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
use \Datetime;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * Tradein Factory
     * 
     * @var \Gos\Tradein\Model\TradeinFactory
     */
    protected $_tradeinFactory;
    protected $_glassFactory;
    protected $resultJsonFactory;
    protected $_scopeConfig;

    /**
     * Helper Factory
     * 
     * @var \Gos\Tradein\Model\TradeinFactory
     */
    protected $_tradeinHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Gos\Tradein\Helper\Api $tradeinHelper,
        \Gos\Tradein\Model\TradeinFactory $tradeinFactory,
        \Gos\Tradein\Model\GlassFactory $glassFactory
    ) {
        parent::__construct($context);
        $this->_tradeinFactory        = $tradeinFactory;
        $this->_tradeinHelper        = $tradeinHelper;
        $this->_glassFactory        = $glassFactory;
        $this->_scopeConfig        = $scopeConfig;
    }

    
    public function getConfigValue($key = null, $defaultValue = null)
    {
        $value = $this->_scopeConfig->getValue(
            'gostradein/manheim_settings/'. $key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if (empty($value)) {
            $value = $defaultValue;
        }

        return $value;
    }
    

    public function execute()
    {
    
    $manheim_admin          = $this->getConfigValue('manheim_admin',false);
    $manheim_variation      = $this->getConfigValue('manheim_variation',false); 

    $manheim_condition      = $this->getConfigValue('manheim_condition',false)>0?$this->getConfigValue('manheim_condition',false):50; 
    $manheim_owner          = $this->getConfigValue('manheim_owner',false)>0?$this->getConfigValue('manheim_owner',false):10; 
    $manheim_key            = $this->getConfigValue('manheim_key',false)>0?$this->getConfigValue('manheim_key',false):500; 
    $manheim_plates         = $this->getConfigValue('manheim_plates',false)>0?$this->getConfigValue('manheim_plates',false):150; 
    $manheim_history        = $this->getConfigValue('manheim_history',false)>0?$this->getConfigValue('manheim_history',false):15; 
    $manheim_transmision    = $this->getConfigValue('manheim_transmision',false)>0?$this->getConfigValue('manheim_transmision',false):5; 
    $manheim_engine         = $this->getConfigValue('manheim_engine',false)>0?$this->getConfigValue('manheim_engine',false):5; 
	$manheim_kms         	= $this->getConfigValue('manheim_kms',false)>0?$this->getConfigValue('manheim_kms',false):15000; 
    // get license data

    $license = $this->getRequest()->getPost('license');
	$car_year = $this->getRequest()->getPost('car_year');
    $glass_code = $this->getRequest()->getPost('glass_code');
    $valuation_kms = $this->getRequest()->getPost('valuation_kms');
    $exellent_condition = $this->getRequest()->getPost('exellent_condition');
    $two_key = $this->getRequest()->getPost('two_key');
    $private_import = $this->getRequest()->getPost('private_import');
    $personalised_plates = $this->getRequest()->getPost('personalised_plates'); 
    $one_owner = $this->getRequest()->getPost('one_owner'); 
    $registered = $this->getRequest()->getPost('registered'); 
    $service_history = $this->getRequest()->getPost('service_history');
    $written_off = $this->getRequest()->getPost('written_off');
    $commerially = $this->getRequest()->getPost('commerially');
    $transmission = $this->getRequest()->getPost('transmission');
    $engine = $this->getRequest()->getPost('engine');

    $transmission = 1;
    $engine = 1;
    
	/*
    $license = 'ZRX119';
    $car_year = 2013;
    $glass_code = 'MAZ--6GT1725V6V2016I';
    $valuation_kms = 1200;
    $exellent_condition = 1;
    $two_key = 1;
    $private_import = 1;
    $personalised_plates = 0; 
    $one_owner = 1; 
    $registered = 1; 
    $service_history = 1;
    $written_off = 1;
    $commerially = 1;
    $transmission = 1;
    $engine = 1;
	*/

    $tradein    = $this->_tradeinFactory->create();
    $glass    = $this->_glassFactory->create();

    $tradein->load($license,'license');

    $lastUpdated = new DateTime($tradein->getLastUpdated());
    $now = new DateTime(date("Y-m-d H:i:s"));
    $interval = (int)$lastUpdated->diff($now)->format('%a');

    $current_valuation  = $tradein->getValuatation();
    $current_kms        = $tradein->getNumberKms();

    // first check car year and break

	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	$checkoutSession = $objectManager->get('\Magento\Checkout\Model\Session');

	if ($car_year == '') {
		$car_year = $checkoutSession->getCarYear()>0?$checkoutSession->getCarYear():0;
	}

    $current_year = date("Y");
    $number_year = $current_year-$car_year;

    if ($number_year == 0) {
        $number_year = 1;
    }

    if ($current_year-$car_year > 9) {
            
            $message = "Sorry we cannot complete your trade in from our website as your vehicle is greater than 9 years old. Please arrange a manual trade-in with one of our dealers.";
            $responseData['status'] = 1;
            $responseData['content'] = '$0.00';
            $responseData['valuation'] = '$0.00';
            $responseData['message'] = $message; 

            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData($responseData);
            return $resultJson;

            exit();

    }


    // Check if valuation is longer than 4 days OR equal $0 , it should be updated

    // Do not check the manual case
    if ($glass_code == '') {

        if ($interval >= 4 || $current_valuation == 0 || $valuation_kms != $current_kms) {

				$glass->load($tradein->getNvic(),'glass_nvic');
                $glass_code = $glass->getGlassCode();

				$newPrice = $this->_tradeinHelper->getValuation($valuation_kms,$glass_code);

                $tradein->setValuatation($newPrice);
                $tradein->setNumberKms($valuation_kms);

            try {

                $tradein->save();  

            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                echo $e->getMessage();
            } catch (\RuntimeException $e) {
                echo $e->getMessage();
            } catch (\Exception $e) {
                echo $e->getMessage();
            }

            
        }else{

			$newPrice = $tradein->getValuatation();

		}

    }else{

        try {  
            // Get price from Maheim API
            $newPrice = $this->_tradeinHelper->getValuation($valuation_kms,$glass_code);

        } catch (SoapFault $E) {  
            
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/manheim-api.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info('Error: '.$E->faultstring.'; derivativeCode: '.$derivativeCode);

        }  

    }


    if ($newPrice > 0) {

        $responseData['status'] = 1;

		$message = '';

        $tradeinprice = $newPrice;

        if (!isset($two_key) || $two_key == 0) {
            $newPrice = $newPrice-$manheim_key;
            $message .= '($'.$manheim_key.' keys)';
        }

        if (isset($personalised_plates) && $personalised_plates == 1) {
            $newPrice = $newPrice-$manheim_plates;
            $message .= '($'.$manheim_plates.' personalised plate)';
        }

        if (!isset($service_history) || $service_history == 0) {
            $newPrice = $newPrice - ($tradeinprice/100)*$manheim_history;
            $message .= '($'.(($tradeinprice/100)*$manheim_history).' service history)';
        }

        if (!isset($transmission) || $transmission == 0) {
            $newPrice = $newPrice - ($tradeinprice/100)*$manheim_transmision;
            $message .= '($'.(($tradeinprice/100)*$manheim_transmision).' manual transmission)';
        }

        if (!isset($engine) || $engine == 0) {
            $newPrice = $newPrice - ($tradeinprice/100)*$manheim_engine;
            $message .= '($'.(($tradeinprice/100)*$manheim_engine).' diesel engine)';
        }

        if (!isset($one_owner) || $one_owner == 0) {
            $newPrice = $newPrice - ($tradeinprice/100)*$manheim_owner;
            $message .= '($'.(($tradeinprice/100)*$manheim_owner).' One Owner)';
        }

        if (isset($exellent_condition) && $exellent_condition == 2) {

            $newPrice = $newPrice - ($tradeinprice/100)*$manheim_condition;
            
            $message .= '($'.(($tradeinprice/100)*$manheim_condition).' Condition problem)';
        }

        if ($manheim_variation > 0 && $manheim_admin > 0) {
            $newPrice = $newPrice*$manheim_variation - $manheim_admin;
        }

        setlocale(LC_MONETARY, 'en_US.UTF-8');
        $valuation = money_format('%.2n', $newPrice);

        if ($exellent_condition == 3) {

            $message = "Sorry we cannot complete your trade in from our website as your vehicle condition is poor. Please arrange a manual trade-in with one of our dealers.";
            $newPrice = 0;  
            $valuation = money_format('%.2n', $newPrice);

        }

        if ($registered == 0) {

            $message = "Sorry we cannot complete your trade in from our website as your vehicle is not registered. Please arrange a manual trade-in with one of our dealers.";
            $newPrice = 0;  
            $valuation = money_format('%.2n', $newPrice);

        }

        if ($commerially == 0) {

            $message = "Sorry we cannot complete your trade in from our website as your vehicle is not used commerially. Please arrange a manual trade-in with one of our dealers.";
            $newPrice = 0;  
            $valuation = money_format('%.2n', $newPrice);

        }

        if ($written_off == 0) {

            $message = "Sorry we cannot complete your trade in from our website as your vehicle is never written off. Please arrange a manual trade-in with one of our dealers.";
            $newPrice = 0;  
            $valuation = money_format('%.2n', $newPrice);

        }

		if (($valuation_kms/$number_year) > $manheim_kms) {
			
			$message = "Sorry we cannot complete your trade in from our website as your vehicle has travelled too many kilometres. Please arrange a manual trade-in with one of our dealers.";
			$newPrice = 0;	
			$valuation = money_format('%.2n', $newPrice);

		}


        $responseData['content'] = $valuation;
		$responseData['valuation'] = $newPrice;
		$responseData['message'] = 'Trade in price: '.$tradeinprice.' - '.$message;	

    }else{

        $responseData['status'] = 1;
        $responseData['content'] = '$0.00';
		$responseData['valuation'] = '0';
		$responseData['message'] = 'Sorry we cannot complete your trade in from our website. There is no information for your vehicle, please contact us for more details.';

    }

    

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseData);
        return $resultJson;

    }

	

    
}