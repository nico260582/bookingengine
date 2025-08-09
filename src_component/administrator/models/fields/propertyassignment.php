<?php
defined('JPATH_BASE') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Layout\LayoutHelper;

class JFormFieldPropertyAssignment extends FormField
{
    protected $type = 'PropertyAssignment';

    protected function getInput()
    {
        // The real logic is in the layout file.
        // We just need to load the data into the form object.
        $this->form->setFieldAttribute($this->name, 'all_properties', $this->form->getData()->all_properties);

        // Prepare data for the layout
        $displayData = [
            'id'             => $this->id,
            'name'           => $this->name,
            'value'          => $this->value,
            'label'          => $this->label,
            'all_properties' => $this->form->getData()->all_properties,
            'form'           => $this->form
        ];

        // Render the layout
        return LayoutHelper::render('joomla.form.field.propertyassignment', $displayData, JPATH_COMPONENT_ADMINISTRATOR . '/layouts');
    }
}
