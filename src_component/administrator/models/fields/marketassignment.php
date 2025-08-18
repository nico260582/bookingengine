<?php
    defined('JPATH_BASE') or die;

    use Joomla\CMS\Factory;
    use Joomla\CMS\Form\FormField;
    use Joomla\CMS\Language\Text;
    use Joomla\CMS\HTML\HTMLHelper;

    class JFormFieldMarketassignment extends FormField
    {
        protected $type = 'Marketassignment';

        protected function getInput()
        {
            JLoader::register('BookingmanagerHelper', JPATH_ADMINISTRATOR . '/components/com_bookingmanager/helpers/bookingmanager.php');
            $countries = BookingmanagerHelper::getCountries();
            $supplierId = $this->form->getData()->get('id', 0);

            $db = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select('market_name, currency, state, currency_symbol')
                ->from('#__bookingmanager_supplier_markets')
                ->where('supplier_id = ' . (int)$supplierId);
            $assignedMarkets = $db->setQuery($query)->loadObjectList('market_name');

            $html = '<div id="market-assignment-container">';
            $html .= '<div class="market-selector-wrapper">';
            $html .= '<select id="country-market-selector" class="chzn-select" data-placeholder="Choose a country...">';
            $html .= '<option value=""></option>';
            foreach ($countries as $country) {
                $html .= '<option value="' . htmlspecialchars($country['name'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($country['name'], ENT_QUOTES, 'UTF-8') . '</option>';
            }
            $html .= '</select>';
            $html .= '<button type="button" id="add-market-btn" class="btn">Add Market</button>';
            $html .= '</div>';

            $html .= '<table class="table table-striped" id="assigned-markets-table">';
            $html .= '<thead><tr><th>Country / Market</th><th>Currency Code</th><th>Currency Symbol</th><th>Active</th><th style="width:5%;"></th></tr></thead>';
            $html .= '<tbody>';

            if (!empty($assignedMarkets)) {
                $i = 0;
                foreach ($assignedMarkets as $name => $market) {
                    $checked = (isset($market->state) && $market->state == 1) ? 'checked' : '';
                    $html .= '<tr>';
                    $html .= '<td><input type="hidden" name="jform[markets][' . $i . '][market_name]" value="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</td>';
                    $html .= '<td><input type="text" name="jform[markets][' . $i . '][currency]" value="' . htmlspecialchars($market->currency, ENT_QUOTES, 'UTF-8') . '" style="width: 50px;" maxlength="3" required></td>';
                    $html .= '<td><input type="text" name="jform[markets][' . $i . '][currency_symbol]" value="' . htmlspecialchars($market->currency_symbol ?? '', ENT_QUOTES, 'UTF-8') . '" style="width: 50px;" maxlength="5"></td>';
                    $html .= '<td><input type="checkbox" name="jform[markets][' . $i . '][state]" value="1" ' . $checked . '></td>';
                    $html .= '<td><button type="button" class="btn btn-danger btn-small remove-market-btn"><span class="icon-minus"></span></button></td>';
                    $html .= '</tr>';
                    $i++;
                }
            }

            $html .= '</tbody></table></div>';

            $script = <<<'JS'
            document.addEventListener('DOMContentLoaded', function() {
                const addBtn = document.getElementById('add-market-btn');
                const selector = document.getElementById('country-market-selector');
                const tableBody = document.querySelector('#assigned-markets-table tbody');

                addBtn.addEventListener('click', function() {
                    const selectedCountry = selector.value;
                    if (!selectedCountry) return;

                    // Prevent adding duplicates
                    const existing = Array.from(tableBody.querySelectorAll('input[type=hidden]')).find(input => input.value === selectedCountry);
                    if (existing) {
                        alert('This market has already been added.');
                        return;
                    }

                    const index = tableBody.rows.length;
                    const newRow = tableBody.insertRow();
                    newRow.innerHTML = `
                        <td><input type="hidden" name="jform[markets][${index}][market_name]" value="${selectedCountry}">${selectedCountry}</td>
                        <td><input type="text" name="jform[markets][${index}][currency]" value="EUR" style="width: 50px;" maxlength="3" required></td>
                        <td><input type="text" name="jform[markets][${index}][currency_symbol]" value="" style="width: 50px;" maxlength="5"></td>
                        <td><input type="checkbox" name="jform[markets][${index}][state]" value="1" checked></td>
                        <td><button type="button" class="btn btn-danger btn-small remove-market-btn"><span class="icon-minus"></span></button></td>
                    `;
                });

                tableBody.addEventListener('click', function(e) {
                    if (e.target && (e.target.matches('.remove-market-btn') || e.target.closest('.remove-market-btn'))) {
                        const row = e.target.closest('tr');
                        row.remove();
                    }
                });
            });
JS;
            Factory::getDocument()->addScriptDeclaration($script);

            return $html;
        }
    }
