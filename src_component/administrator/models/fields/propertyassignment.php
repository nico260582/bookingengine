<?php
defined('JPATH_BASE') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\MVC\Model\AdminModel;

class JFormFieldPropertyAssignment extends FormField
{
    protected $type = 'PropertyAssignment';

    protected function getInput()
    {
        // Attach JS. The file will be created in a later step.
        // This assumes the component manifest is set up to copy a 'media' folder.
        $wa = \Joomla\CMS\Factory::getApplication()->getWebAssetManager();
        $wa->registerAndUseScript('com_bookingmanager.property-assignment', 'media/com_bookingmanager/js/admin-property-assignment.js', [], ['defer' => true], []);

        $model = AdminModel::getInstance('Supplier', 'BookingmanagerModel');

        // Get currently assigned properties to display them
        $assignedIds = is_array($this->value) ? $this->value : [];
        $assignedProperties = empty($assignedIds) ? [] : $model->getAssignedProperties($assignedIds);

        $displayData = [
            'id'                 => $this->id,
            'name'               => $this->name,
            'value'              => $this->value,
            'label'              => $this->label,
            'assignedProperties' => $assignedProperties,
            'currentSupplierId'  => $this->form->getData()->get('id', 0),
        ];

        // Manually render the layout
        $layoutPath = JPATH_COMPONENT_ADMINISTRATOR . '/layouts/propertyassignment.php';

        if (file_exists($layoutPath)) {
            ob_start();
            include $layoutPath;
            return ob_get_clean();
        } else {
            return '<div>Layout file not found at: ' . $layoutPath . '</div>';
        }
    }
}
