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
namespace Gos\Tradein\Controller\Adminhtml\Tradein;

class Delete extends \Gos\Tradein\Controller\Adminhtml\Tradein
{
    /**
     * execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->_resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('tradein_id');
        if ($id) {
            $state = "";
            try {
                /** @var \Gos\Tradein\Model\Tradein $tradein */
                $tradein = $this->_tradeinFactory->create();
                $tradein->load($id);
                $state = $tradein->getState();
                $tradein->delete();
                $this->messageManager->addSuccess(__('The Tradein has been deleted.'));
                $this->_eventManager->dispatch(
                    'adminhtml_gos_tradein_tradein_on_delete',
                    ['state' => $state, 'status' => 'success']
                );
                $resultRedirect->setPath('gos_tradein/*/');
                return $resultRedirect;
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_gos_tradein_tradein_on_delete',
                    ['state' => $state, 'status' => 'fail']
                );
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                $resultRedirect->setPath('gos_tradein/*/edit', ['tradein_id' => $id]);
                return $resultRedirect;
            }
        }
        // display error message
        $this->messageManager->addError(__('Tradein to delete was not found.'));
        // go to grid
        $resultRedirect->setPath('gos_tradein/*/');
        return $resultRedirect;
    }
}
