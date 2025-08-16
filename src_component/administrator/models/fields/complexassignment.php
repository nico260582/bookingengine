<?php
defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

class JFormFieldComplexassignment extends FormField
{
    protected $type = 'Complexassignment';

    protected function getInput()
    {
        try {
            $db = Factory::getDbo();
            $doc = Factory::getDocument();

            // Get all complexes
            $query = $db->getQuery(true)
                ->select('id, name')
                ->from('#__bookingmanager_complexes')
                ->order('name');
            $allComplexes = $db->setQuery($query)->loadObjectList();

            // Get assigned complexes for the current property
            $propertyId = $this->form->getValue('id');
            $assignedComplexes = [];
            if ($propertyId) {
                $query = $db->getQuery(true)
                    ->select('complex_id, priority')
                    ->from('#__bookingmanager_complex_property_map')
                    ->where('property_id = ' . (int)$propertyId);
                $assignedComplexes = $db->setQuery($query)->loadObjectList('complex_id');
            }

            $html = '<table class="table table-striped" id="complex-assignment-table">';
            $html .= '<thead><tr><th>' . Text::_('COM_BOOKINGMANAGER_COMPLEX_NAME') . '</th><th>' . Text::_('COM_BOOKINGMANAGER_ASSIGNED') . '</th><th>' . Text::_('COM_BOOKINGMANAGER_PRIORITY') . '</th></tr></thead>';
            $html .= '<tbody>';

            if (empty($allComplexes)) {
                $html .= '<tr><td colspan="3">' . Text::_('COM_BOOKINGMANAGER_NO_COMPLEXES_FOUND') . '</td></tr>';
            } else {
                foreach ($allComplexes as $complex) {
                    $checked = array_key_exists($complex->id, $assignedComplexes) ? ' checked="checked"' : '';
                    $priority = array_key_exists($complex->id, $assignedComplexes) ? $assignedComplexes[$complex->id]->priority : '0';

                    $html .= '<tr class="bm-complex-row">';
                    $html .= '<td>' . $this->escape($complex->name) . '</td>';
                    $html .= '<td><input type="checkbox" class="bm-complex-assign" name="' . $this->name . '[' . $complex->id . '][assign]" value="1"' . $checked . ' /></td>';
                    $html .= '<td><input type="number" name="' . $this->name . '[' . $complex->id . '][priority]" value="' . $priority . '" class="input-mini bm-priority-input" /></td>';
                    $html .= '</tr>';
                }
            }
            $html .= '</tbody></table>';

            $script = "
            document.addEventListener('DOMContentLoaded', function() {
                const priorityTable = document.getElementById('complex-assignment-table');
                if (!priorityTable) return;

                const updatePriorities = () => {
                    const rows = Array.from(priorityTable.querySelectorAll('.bm-complex-row'));

                    let assignedComplexes = rows.map(row => {
                        const checkbox = row.querySelector('.bm-complex-assign');
                        const priorityInput = row.querySelector('.bm-priority-input');
                        return {
                            priorityInput: priorityInput,
                            userPriority: parseInt(priorityInput.value, 10) || 0,
                            isChecked: checkbox.checked
                        };
                    }).filter(c => c.isChecked);

                    assignedComplexes.sort((a, b) => {
                        const prioA = a.userPriority === 0 ? Infinity : a.userPriority;
                        const prioB = b.userPriority === 0 ? Infinity : b.userPriority;
                        if (prioA === prioB) return 0;
                        return prioA - prioB;
                    });

                    assignedComplexes.forEach((complex, index) => {
                        const newPriority = index + 1;
                        complex.priorityInput.value = newPriority;
                    });
                };

                priorityTable.addEventListener('change', function(e) {
                    if (e.target.classList.contains('bm-priority-input') || e.target.classList.contains('bm-complex-assign')) {
                        updatePriorities();
                    }
                });

                // Initial run to set priorities correctly on page load
                updatePriorities();
            });
            ";
            $doc->addScriptDeclaration($script);

            return $html;

        } catch (\Exception $e) {
            return '<div class="alert alert-danger">Error in Complex Assignment field: ' . $e->getMessage() . '</div>';
        }
    }
}
