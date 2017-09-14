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
namespace Gos\Tradein\Block\Adminhtml\Tradein;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     * 
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * constructor
     * 
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize Tradein edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'tradein_id';
        $this->_blockGroup = 'Gos_Tradein';
        $this->_controller = 'adminhtml_tradein';
        parent::_construct();
        $this->buttonList->update('save', 'label', __('Save Tradein'));
        $this->buttonList->add(
            'save-and-continue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'saveAndContinueEdit',
                            'target' => '#edit_form'
                        ]
                    ]
                ]
            ],
            -100
        );
        $this->buttonList->update('delete', 'label', __('Delete Tradein'));
    }
    /**
     * Retrieve text for header element depending on loaded Tradein
     *
     * @return string
     */
    public function getHeaderText()
    {
        /** @var \Gos\Tradein\Model\Tradein $tradein */
        $tradein = $this->_coreRegistry->registry('gos_tradein_tradein');
        if ($tradein->getId()) {
            return __("Edit Tradein '%1'", $this->escapeHtml($tradein->getState()));
        }
        return __('New Tradein');
    }
}
