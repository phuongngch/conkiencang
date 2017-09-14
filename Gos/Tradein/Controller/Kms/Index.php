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
namespace Gos\Tradein\Controller\Kms;
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

	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

	$checkoutSession = $objectManager->get('\Magento\Checkout\Model\Session');

        // get license data
    $license = $this->getRequest()->getPost('license');

	//$this->_tradeinSession->getTradeinState();
	//$this->_tradeinSession->getTradeinLicense();

    if ($license == '') {

        $license = $checkoutSession->getTradeinLicense()!=''?$checkoutSession->getTradeinLicense():'1GF8AK';
    }
        

    $tradein    = $this->_tradeinFactory->create();
    $glass      = $this->_glassFactory->create();
    $tradein->load($license,'license');
    $glass->load($tradein->getNvic(),'glass_nvic');

    if ($tradein->getNumberKms() != '') {

        $responseData['status'] = 1;
        $responseData['content'] = $tradein->getNumberKms();

    }else{

        $responseData['status'] = 1;
        $responseData['content'] = 0;

    }

        $vehicle_information = trim($glass->getGlassVariant())." ".trim($glass->getGlassStyle())." ".trim($glass->getGlassSeries())."<br />".trim($glass->getGlassEngine())." ".trim($glass->getGlassSize())." ".trim($glass->getGlassTransmission());
        $responseData['vehicle_information'] = trim($vehicle_information);

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseData);
        return $resultJson;

    }

    
}