<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class BookingmanagerControllerRegions extends AdminController
{
    public function getModel($name = 'Regions', $prefix = 'BookingmanagerModel', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function delete()
    {
        $this->checkToken();
        $cid = Factory::getApplication()->input->get('cid', [], 'array');

        if (empty($cid)) {
            $this->setMessage(Text::_('JSELECT_ITEM_TO_DELETE'), 'error');
            return false;
        }

        $model = $this->getModel();
        if ($model->delete($cid)) {
            $this->setMessage(Text::plural('COM_BOOKINGMANAGER_N_ITEMS_DELETED', count($cid)));
        } else {
            $this->setMessage($model->getError(), 'error');
        }

        $this->setRedirect('index.php?option=com_bookingmanager&view=regions');
    }

    public function publish()
    {
        $this->checkToken();
        $cid = Factory::getApplication()->input->get('cid', [], 'array');

        if (empty($cid)) {
            $this->setMessage(Text::_('JSELECT_ITEM_TO_PUBLISH'), 'error');
            return false;
        }

        $model = $this->getModel();
        if ($model->publish($cid, 1)) {
            $this->setMessage(Text::plural('COM_BOOKINGMANAGER_N_ITEMS_PUBLISHED', count($cid)));
        } else {
            $this->setMessage($model->getError(), 'error');
        }

        $this->setRedirect('index.php?option=com_bookingmanager&view=regions');
    }

    public function unpublish()
    {
        $this->checkToken();
        $cid = Factory::getApplication()->input->get('cid', [], 'array');

        if (empty($cid)) {
            $this->setMessage(Text::_('JSELECT_ITEM_TO_UNPUBLISH'), 'error');
            return false;
        }

        $model = $this->getModel();
        if ($model->publish($cid, 0)) {
            $this->setMessage(Text::plural('COM_BOOKINGMANAGER_N_ITEMS_UNPUBLISHED', count($cid)));
        } else {
            $this->setMessage($model->getError(), 'error');
        }

        $this->setRedirect('index.php?option=com_bookingmanager&view=regions');
    }
}
