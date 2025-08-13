<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;

class BookingmanagerControllerComplexes extends FormController
{
    public function getModel($name = 'Complex', $prefix = 'BookingmanagerModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }
	public function delete()
	{
		$this->checkToken();
		$model = $this->getModel();
		$pks   = $this->input->get('cid', [], 'array');

		if (empty($pks))
		{
			$this->setMessage(Text::_('COM_CONTENT_ERROR_NO_ITEM_SELECTED'), 'warning');
		}
		else
		{
			// Attempt to delete the records.
			if ($model->delete($pks))
			{
				$this->setMessage(Text::plural('COM_BOOKINGMANAGER_N_ITEMS_DELETED', count($pks)));
			}
			else
			{
				$this->setMessage($model->getError(), 'error');
			}
		}

		$this->setRedirect(Route::_('index.php?option=com_bookingmanager&view=complexes', false));
	}
}
