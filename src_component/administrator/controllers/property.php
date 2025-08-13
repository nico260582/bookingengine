<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;

class BookingmanagerControllerProperty extends FormController
{
    public function save($key = null, $urlVar = null)
    {
        // Check for request forgeries.
        $this->checkToken();

        $app   = Factory::getApplication();
        $data  = $this->input->post->get('jform', array(), 'array');

        // First, save the main property data using the parent controller's save method
        $saved = parent::save($key, $urlVar);

        if ($saved) {
            // Get the property ID
            $propertyId = $this->getModel()->getState($this->context . '.id');

            // Now, save the rates data
            if (isset($data['rates'])) {
                AdminModel::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/models');
                $ratesModel = AdminModel::getInstance('Propertyrates', 'BookingmanagerModel');

                if ($ratesModel) {
                    $ratesData = [
                        'property_id' => $propertyId,
                        'rates' => $data['rates']
                    ];
                    if (!$ratesModel->save($ratesData)) {
                        $app->enqueueMessage('Failed to save property rates: ' . $ratesModel->getError(), 'error');
                        // Even if rates fail, the main property was saved, so we don't return false here.
                        // We just show an error. Depending on desired behavior, this could be changed.
                    }
                }
            }
        }

        return $saved;
    }
}
