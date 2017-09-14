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
namespace Gos\Tradein\Controller\Request;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * Tradein Factory
     * 
     * @var \Gos\Tradein\Model\TradeinFactory
     */
    protected $_tradeinFactory;
    protected $resultJsonFactory;
    protected $_glassFactory;

    /**
     * Helper Factory
     * 
     * @var \Gos\Tradein\Model\TradeinFactory
     */
    protected $_tradeinHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Gos\Tradein\Helper\Api $tradeinHelper,
        \Gos\Tradein\Model\TradeinFactory $tradeinFactory,
        \Gos\Tradein\Model\GlassFactory $glassFactory
    ) {
        parent::__construct($context);
        $this->_tradeinFactory        = $tradeinFactory;
        $this->_tradeinHelper        = $tradeinHelper;
        $this->_glassFactory        = $glassFactory;
    }

    public function execute()
    {
        // get license data
        $license = $this->getRequest()->getPost('license');
		$state = $this->getRequest()->getPost('state');

		if ($this->getRequest()->getPost('license') == '') {
			$license = 'VZAG99';
		}

		if ($this->getRequest()->getPost('state') == '') {
			$state = 'VIC';
		}

        // save post data to model
        // $post = array(
        //     'state' => 'test', 
        //     'license' => 'test', 
        //     'number_kms' => 'test', 
        //     'year' => 'test', 
        //     'vehicle_make' => 'test', 
        //     'vehicle_model' => 'test', 
        //     'condition' => 'test', 
        //     'one_owner' => '1', 
        //     'never_written_off' => '1', 
        //     'commercially' => '1', 
        //     'vin' => 'test', 
        //     'nvic' => 'test', 
        //     'valuatation' => 'test',
        // );
        $tradein    = $this->_tradeinFactory->create();
        $glass      = $this->_glassFactory->create();

		$vehicleData = array();
        
        $tradein->load($license,'license');

        if(!$tradein->getId()){
            $vehicleData = $this->_tradeinHelper->getVehicleData($state,$license);
            $valuation = 0;
            $nvic = $this->_tradeinHelper->getNvic($vehicleData['vin'],$vehicleData['year']);
            // $data = array_merge($vehicleData,$valuation);
            $data = $vehicleData;
            $data['valuatation'] = $valuation;
            $data['license'] = $license;
            $data['nvic'] = $nvic;
            $tradein->setData($data);

			//print_r($data);
            $glass->load($nvic,'glass_nvic'); // Load vehicle from glass 

			$responseData['status'] = 1;
			$responseData['content'] = $data['year'].' '.$glass->getGlassMake().' '.$data['vehicle_model'];
			$responseData['tradein_year'] = $data['year'];
            $vehicle_information = trim($glass->getGlassVariant())." ".trim($glass->getGlassStyle())."<br />".trim($glass->getGlassEngine())." ".trim($glass->getGlassSize())." ".trim($glass->getGlassTransmission());
            $responseData['vehicleInformation'] = trim($vehicle_information);
        }  
        else{
            //echo $tradein->getLicense();
            $glass->load($tradein->getNvic(),'glass_nvic'); // Load vehicle from glass 

            $responseData['status'] = 1;
            $responseData['content'] = trim($tradein->getYear()).' '.trim($glass->getGlassMake()).' '.trim($tradein->getVehicleModel());
            
            $vehicle_information = trim($glass->getGlassVariant())." ".trim($glass->getGlassSeries())." ".trim($glass->getGlassStyle())." - ".trim($glass->getGlassEngine())." ".trim($glass->getGlassSize())." ".trim($glass->getGlassTransmission());
			$responseData['vehicleInformation'] = trim($vehicle_information);
            
            $responseData['tradein_year'] = $tradein->getYear();
            
        } 

        try {

            if (is_array($vehicleData)) {
                $tradein->save();           
            }else{
                 $responseData['status'] = 0;
            }    
            
		if ($tradein->getVin() == '') {
			$responseData['status'] = 0;
		}

        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            echo $e->getMessage();
        } catch (\RuntimeException $e) {
            echo $e->getMessage();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseData);
        return $resultJson;

    }

    
}