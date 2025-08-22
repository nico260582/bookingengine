<?php
    defined('_JEXEC') or die;
    use Joomla\CMS\Router\Route;
    use Joomla\CMS\Layout\LayoutHelper;
    use Joomla\CMS\HTML\HTMLHelper;
    use Joomla\CMS\Factory;

    HTMLHelper::_('behavior.formvalidator');
    HTMLHelper::_('jquery.framework');
?>

<form action="<?php echo Route::_('index.php?option=com_bookingmanager&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

    <fieldset class="form-horizontal">
        <legend>Property Details</legend>
        <?php echo $this->form->renderField('article_id'); ?>
        <?php echo $this->form->renderField('max_guests'); ?>
        <?php echo $this->form->renderField('allow_extra_mattress'); ?>
        <?php echo $this->form->renderField('number_of_units'); ?>
        <?php echo $this->form->renderField('complexes'); ?>
        <?php echo $this->form->renderField('main_region_id'); ?>
        <?php echo $this->form->renderField('sub_region_id'); ?>
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
jQuery(document).ready(function($) {
    var container = $('#item-form');

    if (container.length) {
        // Commission checkbox logic
        container.on('change', '.override-commission-checkbox', function() {
            var checkbox = $(this);
            var commissionInput = checkbox.closest('td').next().find('.commission-value-input');
            if (commissionInput.length) {
                commissionInput.prop('disabled', !checkbox.prop('checked'));
            }
        });

        // Market activation logic
        container.on('change', '.market-activation-checkbox', function() {
            var activationCheckbox = $(this);
            var marketName = activationCheckbox.data('marketName').replace(/ /g, '-');
            var isChecked = activationCheckbox.prop('checked');

            var inputsToToggle = container.find('.market-col-' + marketName + ' input');
            inputsToToggle.prop('disabled', !isChecked);

            // Re-apply commission logic
            inputsToToggle.filter('.commission-value-input').each(function() {
                var overrideCheckbox = $(this).closest('tr').find('.market-col-' + marketName + ' .override-commission-checkbox');
                if (overrideCheckbox.length && !overrideCheckbox.prop('checked')) {
                    $(this).prop('disabled', true);
                }
            });
        });

        // Sub-region dynamic population
        var mainRegionSelect = $('#jform_main_region_id');
        var subRegionSelect = $('#jform_sub_region_id');
        var currentSubRegionId = '<?php echo $this->item->sub_region_id; ?>';

        function fetchSubRegions(parentId, selectedSubRegionId) {
            if (!parentId) {
                subRegionSelect.html('<option value="">Select a main region first</option>');
                subRegionSelect.trigger("chosen:updated");
                return;
            }

            $.ajax({
                url: 'index.php?option=com_bookingmanager&task=properties.getSubRegions&format=raw',
                type: 'GET',
                data: { 'parent_id': parentId },
                dataType: 'json',
                success: function(response) {
                    subRegionSelect.html('<option value="">Select a Sub Region</option>');
                    if (response && response.length > 0) {
                        $.each(response, function(index, subRegion) {
                            subRegionSelect.append(new Option(subRegion.name, subRegion.id));
                        });
                    }
                    if (selectedSubRegionId) {
                        subRegionSelect.val(selectedSubRegionId);
                    }
                    subRegionSelect.trigger("chosen:updated");
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching sub-regions:', error, xhr.responseText);
                }
            });
        }

        mainRegionSelect.on('change', function() {
            fetchSubRegions($(this).val(), null);
        });

        // On page load, if a main region is selected, fetch its sub-regions
        if (mainRegionSelect.val()) {
            fetchSubRegions(mainRegionSelect.val(), currentSubRegionId);
        }
    }
});
</script>
