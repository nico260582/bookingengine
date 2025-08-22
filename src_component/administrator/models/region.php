<?php
namespace Rtholidays\Component\Bookingmanager\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Factory;

class RegionModel extends AdminModel
{

    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm(
            'com_bookingmanager.region',
            'region',
            array(
                'control' => 'jform',
                'load_data' => $loadData
            )
        );

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_bookingmanager.edit.region.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

}
