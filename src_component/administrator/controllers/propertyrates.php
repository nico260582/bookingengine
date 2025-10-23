<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;

class BookingmanagerControllerPropertyrates extends AdminController
{
    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    public function getModel($name = 'Propertyrates', $prefix = 'BookingmanagerModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function save()
    {
        Session::checkToken() or die(Text::_('JINVALID_TOKEN'));
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
