<?php
namespace RTHolidays\Component\BookingManager\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;

class PropertyratesController extends AdminController
{
    public function save()
    {
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
        $app   = Factory::getApplication();
        $model = $this->getModel('Propertyrates');
        $data  = $this->input->post->get('jform', array(), 'array');
        
        if ($model->save($data)) {
            $app->enqueueMessage('Rates saved successfully.');
        } else {
            $app->enqueueMessage($model->getError(), 'error');
        }
        $this->setRedirect('index.php?option=com_bookingmanager&view=propertyrates&filter_property_id=' . (int)($data['property_id'] ?? 0));
    }
}