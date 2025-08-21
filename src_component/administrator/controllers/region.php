<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;

class BookingmanagerControllerRegion extends FormController
{
    protected function postSaveHook(\Joomla\CMS\MVC\Model\BaseDatabaseModel $model, $validData = [])
    {
        $this->setRedirect('index.php?option=com_bookingmanager&view=complexes&active=regions');
    }
}
