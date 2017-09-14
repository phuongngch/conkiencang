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

class Index extends \Magento\Framework\App\Action\Action
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

		$nineYearsAgo = strtotime('-10 years');
		$yearlimited = date('Y', $nineYearsAgo);

        
        $glass = $this->_glassFactory->create();

        $yearsCollection = $glass->getCollection();

        $yearsCollection->addFieldToSelect('glass_year');

		$yearsCollection->addFieldToFilter('glass_year',array('gt' => $yearlimited));

        $yearsCollection->getSelect()->group('glass_year')->order('glass_year ASC');

		//$yearsCollection->getSelect()->setPageSize(9);

        //print_r($years);

        foreach ($yearsCollection->getData() as $year) {
                //print_r($year);
                $years[] = $year;
        }

        //$responseData['status'] = 1;
        //$responseData['content'] = $years;

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($years);
        return $resultJson;

    }

    
}