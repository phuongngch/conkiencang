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
namespace Gos\Tradein\Controller\Glass;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;

class Vehicle extends \Magento\Framework\App\Action\Action
{
    /**
     * Glass Factory
     * 
     * @var \Gos\Tradein\Model\GlassFactory
     */
    protected $_glassFactory;
    protected $resultJsonFactory;

    /**
     * Helper Factory
     * 
     * @var \Gos\Tradein\Model\GlassFactory
     */
    protected $_tradeinHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Gos\Tradein\Helper\Api $tradeinHelper,
        \Gos\Tradein\Model\GlassFactory $glassFactory
    ) {
        parent::__construct($context);
        $this->_glassFactory        = $glassFactory;
        $this->_tradeinHelper        = $tradeinHelper;
    }

    public function execute()
    {

        $car_year = $this->getRequest()->getPost('car_year');
        $car_make = $this->getRequest()->getPost('car_make');
        $car_model = $this->getRequest()->getPost('car_model');
        $car_variant = $this->getRequest()->getPost('car_variant');

        $car_style = $this->getRequest()->getPost('car_style');
        $car_size = $this->getRequest()->getPost('car_size');
        $car_transmission = $this->getRequest()->getPost('car_transmission');
        $car_engine = $this->getRequest()->getPost('car_engine');
        $car_month = $this->getRequest()->getPost('car_month');

        if ($car_year == 0) {

            $car_year = 2015;
            $car_make = 'BMW';
            $car_model = 3;
            $car_variant = '16i';

        }
		
        $glass = $this->_glassFactory->create();

        $modelsCollection = $glass->getCollection();

        $modelsCollection->addFieldToSelect('glass_code');

        $modelsCollection->addFieldToFilter('glass_year',$car_year);
        $modelsCollection->addFieldToFilter('glass_make',$car_make);
        $modelsCollection->addFieldToFilter('glass_model',$car_model);
        $modelsCollection->addFieldToFilter('glass_variant',$car_variant);

        if ($car_style !='' ) {
            $modelsCollection->addFieldToFilter('glass_style',$car_style);
        }

        if ($car_size !='' ) {
            $modelsCollection->addFieldToFilter('glass_size',$car_size);
        }

        if ($car_transmission !='' ) {
            $modelsCollection->addFieldToFilter('glass_transmission',$car_transmission);
        }

        if ($car_engine !='' ) {
            $modelsCollection->addFieldToFilter('glass_engine',$car_engine);
        }

        if ($car_month !='' ) {
            $modelsCollection->addFieldToFilter('glass_mth',$car_month);
        }
		

        //$modelsCollection->getSelect()->group('glass_model')->order('glass_model ASC');
        //print_r($modelsCollection->getData());

		$models = array();
        $models['glass_code'] = '';
		if ($modelsCollection) {
			foreach ($modelsCollection->getData() as $model) {
                //print_r($model);
                $models['glass_code'] = trim($model['glass_code']);
        	}
		}

        //print_r($models['glass_code']);
        // $responseData['status'] = 1;
        //$responseData['content'] = $models;

        if (is_array($models) != '' && $models['glass_code'] !='') {

            $glass->load($models['glass_code'],'glass_code');

            $responseData['glass_code'] = $models['glass_code'];

			$vechicle = $car_year." ".trim($glass->getGlassMake())." ".trim($glass->getGlassModel());
                       
            $responseData['vechicle'] = $vechicle;

            $vehicle_information = trim($glass->getGlassVariant())." ".trim($glass->getGlassStyle())." ".trim($glass->getGlassSeries())."<br />".trim($glass->getGlassEngine())." ".trim($glass->getGlassSize())." ".trim($glass->getGlassTransmission());
            $responseData['vehicleInformation'] = trim($vehicle_information);
            $responseData['tradein_year'] = $car_year;
            $responseData['status'] = 1;

        }else{

            $responseData['glass_code'] = '';
            $responseData['vechicle'] = 'Sorry, We could not find your vehicle, please try again.';
            $responseData['vehicleInformation'] = '';
            $responseData['tradein_year'] = 0;
            $responseData['status'] = 1;


        }
        
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseData);
        return $resultJson;

    }

    
}