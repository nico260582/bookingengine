<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;

class BookingmanagerControllerSupplier extends FormController
{
    public function save($key = null, $urlVar = null)
    {
        $model = $this->getModel('Supplier');
        $model_class = get_class($model);

        $log_file = JPATH_ROOT . '/jules_controller_debug_log.txt';
        $log_message = 'Timestamp: ' . date('Y-m-d H:i:s') . "\n";
        $log_message .= "Controller save() method was called.\n";
        $log_message .= "Model class is: " . $model_class . "\n";
        $data  = $this->input->post->get('jform', array(), 'array');
        $log_message .= 'Form data: ' . print_r($data, true) . "\n\n";
        file_put_contents($log_file, $log_message, FILE_APPEND);

        return parent::save($key, $urlVar);
    }
}