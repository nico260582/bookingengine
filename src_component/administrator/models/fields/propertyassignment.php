<?php
defined('JPATH_BASE') or die;

use Joomla\CMS\Form\FormField;

class JFormFieldPropertyAssignment extends FormField
{
    protected $type = 'PropertyAssignment';

    protected function getInput()
    {
        // The real logic is in the layout file.
        // We just need to load the data into the form object.
        $this->form->setFieldAttribute($this->name, 'all_properties', $this->form->getData()->all_properties);
        return $this->getLayout($this->layout);
    }

    protected function getLayoutPaths()
    {
        $paths = parent::getLayoutPaths();
        $paths[] = JPATH_COMPONENT_ADMINISTRATOR . '/layouts';
        return $paths;
    }
}
