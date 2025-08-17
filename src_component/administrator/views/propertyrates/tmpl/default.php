<?php
    defined('_JEXEC') or die;
    use Joomla\CMS\HTML\HTMLHelper;
    HTMLHelper::_('behavior.formvalidator');
?>

<div id="j-sidebar-container" class="span2">
    <?php echo JHtmlSidebar::render(); ?>
</div>
<div id="j-main-container" class="span10">
    <form action="<?php echo JRoute::_('index.php?option=com_bookingmanager&view=propertyrates'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
        <div class="form-inline">
            <label for="filter_property_id">Select a Property:</label>
            <select name="filter_property_id" id="filter_property_id" class="chzn-select" onchange="this.form.submit()">
                <option value="">- Select -</option>
                <?php echo HTMLHelper::_('select.options', $this->properties, 'id', 'title', $this->selectedPropertyId); ?>
            </select>
        </div>
        <hr/>

        <?php if ($this->selectedPropertyId) : ?>
            <?php if (!empty($this->rateData->error)) : ?>
                <div class="alert alert-warning"><?php echo $this->escape($this->rateData->error); ?></div>
            <?php elseif (empty($this->rateData->seasons)) : ?>
                <div class="alert">This property's supplier has no seasons defined.</div>
            <?php else : ?>
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th rowspan="2" style="vertical-align: middle;">Season</th>
                            <?php foreach ($this->rateData->markets as $market) : ?>
                                <th colspan="3" class="text-center"><?php echo $this->escape($market->market_name); ?></th>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <?php foreach ($this->rateData->markets as $market) : ?>
                                <th>Rate (<?php echo $this->escape($market->currency); ?>)</th>
                                <th>Override Commission</th>
                                <th>Commission %</th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($this->rateData->seasons as $season) :
                            $seasonRates = $this->rateData->rates[$season->name]->rates ?? [];
                        ?>
                        <tr>
                            <td>
                                <?php echo $this->escape($season->name); ?><br/>
                                <small><?php echo HTMLHelper::_('date', $season->start_date, 'd M Y'); ?> to <?php echo HTMLHelper::_('date', $season->end_date, 'd M Y'); ?></small>
                            </td>
                            <?php foreach ($this->rateData->markets as $market) :
                                $marketName = $market->market_name;
                                $marketRateData = $seasonRates[$marketName] ?? [];
                                $rateValue = $marketRateData['rate'] ?? '';
                                $overrideChecked = !empty($marketRateData['override_commission']) ? 'checked' : '';
                                $commissionValue = $marketRateData['commission'] ?? '';
                                $supplierCommission = $season->admin_commission ?? 0;
                            ?>
                                <td>
                                    <input type="number" step="0.01" name="jform[rates][<?php echo $this->escape($season->name); ?>][<?php echo $this->escape($marketName); ?>][rate]" value="<?php echo $this->escape($rateValue); ?>" class="input-small" />
                                </td>
                                <td class="text-center">
                                    <input type="checkbox" name="jform[rates][<?php echo $this->escape($season->name); ?>][<?php echo $this->escape($marketName); ?>][override_commission]" value="1" class="override-commission-checkbox" <?php echo $overrideChecked; ?> />
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="jform[rates][<?php echo $this->escape($season->name); ?>][<?php echo $this->escape($marketName); ?>][commission]" value="<?php echo $this->escape($commissionValue); ?>" class="input-small commission-value-input" />
                                    <div class="commission-source-info small" style="color: #666;">
                                        (Default: <?php echo $this->escape($supplierCommission); ?>%)
                                    </div>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <input type="hidden" name="jform[property_id]" value="<?php echo $this->selectedPropertyId; ?>" />
                <button type="submit" class="btn btn-primary" onclick="Joomla.submitbutton('propertyrates.save')">Save Rates</button>
            <?php endif; ?>
        <?php else : ?>
            <div class="alert alert-info">Please select a property to manage its rates.</div>
        <?php endif; ?>

        <input type="hidden" name="task" value="" /><?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.override-commission-checkbox').forEach(function(checkbox) {
        var commissionInput = checkbox.closest('td').nextElementSibling.querySelector('.commission-value-input');

        function toggleCommissionInput() {
            commissionInput.disabled = !checkbox.checked;
        }

        checkbox.addEventListener('change', toggleCommissionInput);
        toggleCommissionInput();
    });
});
</script>