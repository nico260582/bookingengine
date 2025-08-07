<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class BookingmanagerControllerPropertyrates extends JControllerAdmin
{
    public function save()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        $app   = Factory::getApplication();
        $model = $this->getModel('Propertyrates', 'BookingmanagerModel');
        $data  = $this->input->post->get('jform', array(), 'array');
        
        if ($model->save($data)) {
            $app->enqueueMessage('Rates saved successfully.');
        } else {
            $app->enqueueMessage($model->getError(), 'error');
        }
        $this->setRedirect('index.php?option=com_bookingmanager&view=propertyrates&filter_property_id=' . (int)($data['property_id'] ?? 0));
    }
}