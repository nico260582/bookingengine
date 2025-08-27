<?php
    defined('_JEXEC') or die;
    use Joomla\CMS\Router\Route;
    use Joomla\CMS\Layout\LayoutHelper;
    use Joomla\CMS\HTML\HTMLHelper;
    use Joomla\CMS\Factory;
    use Joomla\CMS\Response\JsonResponse;

    // --- START: Internal AJAX Handler for Sub-Regions ---
    $app = Factory::getApplication();
    if ($app->input->get('fetch_subregions') === '1') {
        ob_end_clean();
        $mainRegionId = $app->input->getInt('main_region_id', 0);
        $data = [];
        if ($mainRegionId > 0 && Factory::getUser()->authorise('core.manage', 'com_bookingmanager')) {
            try {
                $db = Factory::getDbo();
                $query = $db->getQuery(true)
                    ->select($db->quoteName(['id', 'name']))
                    ->from($db->quoteName('#__bookingmanager_sub_regions'))
                    ->where($db->quoteName('main_region_id') . ' = ' . (int) $mainRegionId)
                    ->where($db->quoteName('published') . ' = 1')
                    ->order($db->quoteName('name'));
                $data = $db->setQuery($query)->loadObjectList() ?: [];
            } catch (\Exception $e) {
                echo new JsonResponse(null, $e->getMessage(), true);
                $app->close();
            }
        }
        echo new JsonResponse($data);
        $app->close();
    }
    // --- END: Internal AJAX Handler ---

    HTMLHelper::_('behavior.formvalidator');

?>

<form action="<?php echo Route::_('index.php?option=com_bookingmanager&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

    <fieldset class="form-horizontal">
        <legend>Property Details</legend>
        <?php echo $this->form->renderField('article_id'); ?>
        <?php echo $this->form->renderField('max_guests'); ?>
        <?php if (isset($this->item->pricing_model) && $this->item->pricing_model === 'CustomCapacity') { echo $this->form->renderField('base_guest_number'); } ?>
        <?php echo $this->form->renderField('main_region_id'); ?>
        <?php echo $this->form->renderField('sub_region_id'); ?>
        <?php echo $this->form->renderField('allow_extra_mattress'); ?>
        <?php echo $this->form->renderField('number_of_units'); ?>
        <?php echo $this->form->renderField('complexes'); ?>
    </fieldset>

    <hr>

    <fieldset class="form-horizontal">
        <legend>Property Rates</legend>
        <?php if (isset($this->item->ratesData) && !empty($this->item->ratesData->seasons) && !empty($this->item->ratesData->markets)) : ?>
            <?php
            // Prepare active markets data for easy lookup
            $activeMarkets = [];
            if (isset($this->item->ratesData->active_markets)) {
                $activeMarkets = is_array($this->item->ratesData->active_markets) ? $this->item->ratesData->active_markets : json_decode($this->item->ratesData->active_markets, true);
                if (!is_array($activeMarkets)) $activeMarkets = [];
            }
            ?>
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th rowspan="2" style="vertical-align: middle;">Season</th>
                        <?php foreach ($this->item->ratesData->markets as $market) : ?>
                            <th colspan="3" class="text-center market-header-<?php echo str_replace(' ', '-', $this->escape($market->market_name)); ?>">
                                <?php echo $this->escape($market->market_name); ?>
                                <?php if ($market->market_name !== 'Global Rate') : ?>
                                    <br/>
                                    <label class="small">
                                        <input type="checkbox" class="market-activation-checkbox" data-market-name="<?php echo $this->escape($market->market_name); ?>"
                                               name="jform[active_markets][<?php echo $this->escape($market->market_name); ?>]" value="1"
                                               <?php echo (!empty($activeMarkets) && in_array($market->market_name, $activeMarkets)) ? 'checked' : ''; ?>>
                                        Activate Market
                                    </label>
                                <?php endif; ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <?php foreach ($this->item->ratesData->markets as $market) : ?>
                            <th class="market-col-<?php echo str_replace(' ', '-', $this->escape($market->market_name)); ?>">Rate (<?php echo $this->escape($market->currency); ?>)</th>
                            <th class="market-col-<?php echo str_replace(' ', '-', $this->escape($market->market_name)); ?>">OC</th>
                            <th class="market-col-<?php echo str_replace(' ', '-', $this->escape($market->market_name)); ?>">com %</th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->item->ratesData->seasons as $season) :
                        $seasonRates = $this->item->ratesData->rates[$season->name]->rates ?? [];
                    ?>
                    <tr>
                        <td>
                            <?php echo $this->escape($season->name); ?><br/>
                            <small><?php echo HTMLHelper::_('date', $season->start_date, 'd M Y'); ?> to <?php echo HTMLHelper::_('date', $season->end_date, 'd M Y'); ?></small>
                        </td>
                        <?php foreach ($this->item->ratesData->markets as $market) :
                            $marketName = $market->market_name;
                            $marketRateData = $seasonRates[$marketName] ?? [];
                            $rateValue = $marketRateData['rate'] ?? '';
                            $overrideChecked = !empty($marketRateData['override_commission']) ? 'checked' : '';
                            $commissionValue = $marketRateData['commission'] ?? '';
                            $supplierCommission = $season->admin_commission ?? 0;
                            $isMarketActive = ($marketName === 'Global Rate' || (!empty($activeMarkets) && in_array($marketName, $activeMarkets)));
                        ?>
                            <td class="market-col-<?php echo str_replace(' ', '-', $this->escape($market->market_name)); ?>">
                                <input type="number" step="0.01" max="9999" name="jform[rates][<?php echo $this->escape($season->name); ?>][<?php echo $this->escape($marketName); ?>][rate]" value="<?php echo $this->escape($rateValue); ?>" style="width: 80px;" <?php if (!$isMarketActive) echo 'disabled'; ?> />
                            </td>
                            <td class="text-center market-col-<?php echo str_replace(' ', '-', $this->escape($market->market_name)); ?>">
                                <input type="checkbox" name="jform[rates][<?php echo $this->escape($season->name); ?>][<?php echo $this->escape($marketName); ?>][override_commission]" value="1" class="override-commission-checkbox" <?php echo $overrideChecked; ?> <?php if (!$isMarketActive) echo 'disabled'; ?> />
                            </td>
                            <td class="market-col-<?php echo str_replace(' ', '-', $this->escape($market->market_name)); ?>">
                                <input type="number" step="0.01" max="100" name="jform[rates][<?php echo $this->escape($season->name); ?>][<?php echo $this->escape($marketName); ?>][commission]" value="<?php echo $this->escape($commissionValue); ?>" style="width: 70px;" class="commission-value-input" <?php if (!$isMarketActive || !$overrideChecked) echo 'disabled'; ?> />
                                <div class="commission-source-info small" style="color: #666;">
                                    (Default: <?php echo $this->escape($supplierCommission); ?>%)
                                </div>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif (isset($this->item->ratesData) && isset($this->item->ratesData->error)) : ?>
            <div class="alert alert-warning"><?php echo $this->escape($this->item->ratesData->error); ?></div>
        <?php else : ?>
            <div class="alert">No seasons found. Please define seasons for the supplier assigned to this property.</div>
        <?php endif; ?>
    </fieldset>

    <input type="hidden" name="task" value="" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
<script>
// Self-contained logic for Sub-Region Loading
jQuery(document).ready(function($) {
    'use strict';

    const mainRegionField = $('#jform_main_region_id');
    const subRegionField = $('#jform_sub_region_id');

    if (mainRegionField.length === 0) return;

    const savedSubRegionValue = "<?php echo $this->item->sub_region_id ?? ''; ?>";

    function updateSubRegions(isInitialLoad, callback) {
        const mainRegionId = mainRegionField.val();
        subRegionField.find('option:gt(0)').remove();

        if (mainRegionId && mainRegionId !== '') {
            const url = `index.php?option=com_bookingmanager&view=property&layout=edit&id=<?php echo (int)($this->item->id ?? 0); ?>&fetch_subregions=1&main_region_id=${mainRegionId}`;
            fetch(url)
                .then(response => response.json())
                .then(result => {
                    if (result.success && Array.isArray(result.data)) {
                        $.each(result.data, function(key, value) {
                            subRegionField.append($('<option>', { value: value.id, text: value.name }));
                        });
                    }
                })
                .finally(() => {
                    if (isInitialLoad && savedSubRegionValue) {
                        subRegionField.val(savedSubRegionValue);
                    }
                    if (typeof callback === 'function') callback();
                });
        } else {
            if (typeof callback === 'function') callback();
        }
    }

    mainRegionField.on('change', function() {
        updateSubRegions(false, function() {
            subRegionField.trigger("chosen:updated");
        });
    });

    if (mainRegionField.val()) {
        updateSubRegions(true, function() {
            subRegionField.trigger("chosen:updated");
        });
    }

    // Fix for Chosen validation highlighting
    if (document.formvalidator) {
        document.formvalidator.setHandler('required', function (field) {
            let isValid = false;
            if (field.tagName.toLowerCase() === 'select' && $(field).data('chosen')) {
                const chosenId = '#' + field.id + '_chosen';
                const chosenElement = $(chosenId);
                isValid = (field.value !== '');
                if (isValid) {
                    chosenElement.removeClass('invalid');
                } else {
                    chosenElement.addClass('invalid');
                }
            } else {
                 isValid = (field.value.trim() !== '');
            }
            return isValid;
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('item-form');

    if (container) {
        // Commission checkbox logic
        container.addEventListener('change', function(e) {
            if (e.target.classList.contains('override-commission-checkbox')) {
                const checkbox = e.target;
                const commissionInput = checkbox.closest('td').nextElementSibling.querySelector('.commission-value-input');
                if (commissionInput) {
                    commissionInput.disabled = !checkbox.checked;
                }
            }
        });

        // Market activation logic
        container.addEventListener('change', function(e) {
            if (e.target.classList.contains('market-activation-checkbox')) {
                const activationCheckbox = e.target;
                const marketName = activationCheckbox.dataset.marketName.replace(/ /g, '-');
                const isChecked = activationCheckbox.checked;

                const inputsToToggle = container.querySelectorAll('.market-col-' + marketName + ' input');
                inputsToToggle.forEach(function(input) {
                    input.disabled = !isChecked;
                    // Re-apply commission logic
                    if (input.classList.contains('commission-value-input')) {
                        const overrideCheckbox = input.closest('tr').querySelector('.market-col-' + marketName + ' .override-commission-checkbox');
                        if (overrideCheckbox && !overrideCheckbox.checked) {
                            input.disabled = true;
                        }
                    }
                });
            }
        });

        // Set initial state for market activation on page load
        document.querySelectorAll('.market-activation-checkbox').forEach(function(activationCheckbox) {
            const marketName = activationCheckbox.dataset.marketName.replace(/ /g, '-');
            const isChecked = activationCheckbox.checked;

            const inputsToToggle = container.querySelectorAll('.market-col-' + marketName + ' input');
            inputsToToggle.forEach(function(input) {
                input.disabled = !isChecked;
                // Re-apply commission logic
                if (input.classList.contains('commission-value-input')) {
                    const overrideCheckbox = input.closest('tr').querySelector('.market-col-' + marketName + ' .override-commission-checkbox');
                    if (overrideCheckbox && !overrideCheckbox.checked) {
                        input.disabled = true;
                    }
                }
            });
        });
    }
});
</script>
