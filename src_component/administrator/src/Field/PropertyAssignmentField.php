<?php
namespace RTHolidays\Component\BookingManager\Administrator\Field;

defined('JPATH_BASE') or die;

use Joomla\CMS\Form\FormField;
use RTHolidays\Component\BookingManager\Administrator\Model\SupplierModel;

class PropertyAssignmentField extends FormField
{
    protected $type = 'PropertyAssignment';

    protected function getInput()
    {
        $model = new SupplierModel();
        $currentSupplierId = $this->form->getData()->get('id', 0);

        // This now correctly fetches only the properties from the selected categories
        $allProperties = $model->getAllPropertiesWithAssignments($currentSupplierId);

        $displayData = [
            'id'             => $this->id,
            'name'           => $this->name,
            'value'          => $this->value,
            'label'          => $this->label,
            'all_properties' => $allProperties,
            'form'           => $this->form,
        ];

        // Manually render the layout from the simpler path
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
