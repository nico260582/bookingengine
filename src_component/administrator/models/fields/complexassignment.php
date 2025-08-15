<?php
defined('JPATH_BASE') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

class JFormFieldComplexassignment extends FormField
{
    protected $type = 'Complexassignment';

    protected function getInput()
    {
        $db = Factory::getDbo();

        // Get all complexes
        $query = $db->getQuery(true)
            ->select('id, name')
            ->from('#__bookingmanager_complexes')
            ->order('name');
        $db->setQuery($query);
        $allComplexes = $db->loadObjectList();

        // Get assigned complexes for the current property
        $propertyId = $this->form->getValue('id');
        $assignedComplexes = [];
        if ($propertyId) {
            $query = $db->getQuery(true)
                ->select('complex_id, priority')
                ->from('#__bookingmanager_complex_property_map')
                ->where('property_id = ' . (int)$propertyId);
            $db->setQuery($query);
            $assignedComplexes = $db->loadObjectList('complex_id');
        }

        $html = '<table class="table table-striped">';
        $html .= '<thead><tr><th>' . JText::_('COM_BOOKINGMANAGER_COMPLEX_NAME') . '</th><th>' . JText::_('COM_BOOKINGMANAGER_ASSIGNED') . '</th><th>' . JText::_('COM_BOOKINGMANAGER_PRIORITY') . '</th></tr></thead>';
        $html .= '<tbody>';

        foreach ($allComplexes as $complex) {
            $checked = array_key_exists($complex->id, $assignedComplexes) ? ' checked="checked"' : '';
            $priority = array_key_exists($complex->id, $assignedComplexes) ? $assignedComplexes[$complex->id]->priority : '0';

            $html .= '<tr>';
            $html .= '<td>' . $complex->name . '</td>';
            $html .= '<td><input type="checkbox" name="' . $this->name . '[' . $complex->id . '][assign]" value="1"' . $checked . ' /></td>';
            $html .= '<td><input type="number" name="' . $this->name . '[' . $complex->id . '][priority]" value="' . $priority . '" class="input-mini" /></td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }
}
