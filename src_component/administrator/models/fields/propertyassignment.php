<?php
defined('JPATH_BASE') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\MVC\Model\AdminModel;

class JFormFieldPropertyAssignment extends FormField
{
    protected $type = 'PropertyAssignment';

    protected function getInput()
    {
        // Get the model
        $model = AdminModel::getInstance('Supplier', 'BookingmanagerModel');

        // Get the current supplier's ID from the form data
        $currentSupplierId = $this->form->getData()->get('id', 0);

        // Get all properties data
        $allProperties = $model->getAllPropertiesWithAssignments($currentSupplierId);

        // Prepare data for the layout
        $displayData = [
            'id'             => $this->id,
            'name'           => $this->name,
            'value'          => $this->value,
            'label'          => $this->label,
            'all_properties' => $allProperties,
            'form'           => $this->form
        ];

        // Render the layout
        return LayoutHelper::render('joomla.form.field.propertyassignment', $displayData, JPATH_COMPONENT_ADMINISTRATOR . '/layouts');
    }
}
