<?php
defined('_JEXEC') or die;
?>
<?php
// Temporary debug output
if (isset($this->rateData)) {
    echo '<div class="alert alert-info">';
    echo '<strong>RAW DATA DUMP:</strong><br/><pre>';
    var_dump($this->rateData);
    echo '</pre>';
    echo '<strong>Debug Info from Model:</strong><br/>';
    echo 'Seasons Count: ' . ($this->rateData->debug_seasons_count ?? 'N/A') . '<br/>';
    echo 'Rules JSON: ' . ($this->rateData->debug_rules_json ?? 'Not set') . '<br/>';
    if (!empty($this->rateData->error)) {
        echo 'Error: ' . $this->rateData->error;
    }
    echo '</div>';
} else {
    echo '<div class="alert alert-danger">Debug: $this->rateData is NOT SET.</div>';
}
?>

<div id="j-sidebar-container" class="span2">
    <?php echo JHtmlSidebar::render(); ?>
</div>
<div id="j-main-container" class="span10">
    <form action="<?php echo JRoute::_('index.php?option=com_bookingmanager&view=propertyrates'); ?>" method="post" name="adminForm" id="adminForm">
        <div class="form-inline">
            <label for="filter_property_id">Select a Property:</label>
            <select name="filter_property_id" id="filter_property_id" class="chzn-select" onchange="this.form.submit()">
                <option value="">- Select -</option>
                <?php echo JHtml::_('select.options', $this->properties, 'id', 'title', $this->selectedPropertyId); ?>
            </select>
        </div>
        <hr/>
        
        <?php if ($this->selectedPropertyId) : ?>
            <?php if (!empty($this->rateData->error)) : ?>
                <div class="alert alert-warning"><?php echo $this->rateData->error; ?></div>
            <?php elseif (empty($this->rateData->seasons)) : ?>
                <div class="alert">This property's supplier has no seasons defined.</div>
            <?php else : ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.override-commission-checkbox').forEach(function(checkbox) {
        var commissionInput = checkbox.closest('tr').querySelector('.commission-value-input');

        function toggleCommissionInput() {
            commissionInput.disabled = !checkbox.checked;
        }

        checkbox.addEventListener('change', toggleCommissionInput);
        toggleCommissionInput(); // Initial state
    });
});
</script>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Season</th>
                            <th>Base Rate (per night)</th>
                            <th class="nowrap">Override Admin Commission?</th>
                            <th>Admin Commission %</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($this->rateData->seasons as $season) : 
                            $rate = $this->rateData->rates[$season->name] ?? null;
                            $rateValue = ($rate && isset($rate->base_rate)) ? $rate->base_rate : '';
                            $overrideChecked = ($rate && isset($rate->override_admin_commission) && $rate->override_admin_commission) ? 'checked' : '';
                            $commissionValue = ($rate && isset($rate->admin_commission)) ? $rate->admin_commission : '';
                            $supplierCommission = $season->admin_commission ?? 0;
                        ?>
                        <tr>
                            <td><?php echo $this->escape($season->name); ?><br/><small><?php echo $season->start_date . ' to ' . $season->end_date; ?></small></td>
                            <td><input type="number" step="0.01" name="jform[rates][<?php echo $this->escape($season->name); ?>][base_rate]" value="<?php echo $this->escape($rateValue); ?>" class="input-small" /></td>
                            <td><input type="checkbox" name="jform[rates][<?php echo $this->escape($season->name); ?>][override_admin_commission]" value="1" class="override-commission-checkbox" <?php echo $overrideChecked; ?> /></td>
                            <td>
                                <input type="number" step="0.01" name="jform[rates][<?php echo $this->escape($season->name); ?>][admin_commission]" value="<?php echo $this->escape($commissionValue); ?>" class="input-small commission-value-input" />
                                <div class="commission-source-info" style="font-size: 0.9em; color: #666;">
                                    <?php if ($overrideChecked) : ?>
                                        <span style="color: green;">Property Override</span>
                                    <?php else : ?>
                                        Inherited from Supplier (<?php echo $this->escape($supplierCommission); ?>%)
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <input type="hidden" name="jform[property_id]" value="<?php echo $this->selectedPropertyId; ?>" />
            <?php endif; ?>
        <?php else : ?>
            <div class="alert alert-info">Please select a property to manage its rates.</div>
        <?php endif; ?>

        <input type="hidden" name="task" value="" /><?php echo JHtml::_('form.token'); ?>
    </form>
</div>