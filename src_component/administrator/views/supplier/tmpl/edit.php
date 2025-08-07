<?php
defined('_JEXEC') or die;
JHtml::_('jquery.framework');
JHtml::_('behavior.formvalidator');
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    (function($) {
        var pricingModelSelect = $('#jform_pricing_model');
        if (!pricingModelSelect.length) return;
        function toggleFields() {
            var selectedModel = pricingModelSelect.val();
            $('.bm-showon-supplement').closest('.control-group').toggle(selectedModel === 'SupplementPerGuest');
            $('.bm-showon-capacity').closest('.control-group').toggle(selectedModel === 'CapacityBased');
        }
        pricingModelSelect.on('change', toggleFields).trigger('change');
    })(jQuery);
});
</script>
<form action="<?php echo JRoute::_('index.php?option=com_bookingmanager&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
    <div class="form-horizontal">
        <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>
        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', 'Details'); ?>
            <?php echo $this->form->renderFieldset('details'); ?>
        <?php echo JHtml::_('bootstrap.endTab'); ?>
        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'pricing', 'Pricing Rules'); ?>
            <?php echo $this->form->renderFieldset('pricing_model_rules'); ?>
        <?php echo JHtml::_('bootstrap.endTab'); ?>
        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'ages', 'Guest Age Rules'); ?>
            <?php echo $this->form->renderFieldset('guest_age_rules'); ?>
        <?php echo JHtml::_('bootstrap.endTab'); ?>
        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'seasons', 'Seasons'); ?>
            <div class="table-responsive" style="overflow: visible;"><?php echo $this->form->renderFieldset('seasons_rules'); ?></div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>
        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'discounts', 'Discount Rules'); ?>
            <div class="table-responsive" style="overflow: visible;"><?php echo $this->form->renderFieldset('discounts'); ?></div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>
        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'assignments', 'Property Assignments'); ?>
            <?php echo $this->form->renderFieldset('assignments'); ?>
        <?php echo JHtml::_('bootstrap.endTab'); ?>
        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'logs', 'Change Log'); ?>
            <table class="table table-striped">
                <thead><tr><th>Date</th><th>Admin User</th><th>Field</th><th style="width: 30%;">Old Value</th><th style="width: 30%;">New Value</th></tr></thead>
                <tbody>
                    <?php if ($this->logs) : foreach ($this->logs as $log) : ?>
                    <tr>
                        <td><?php echo JHtml::_('date', $log->created_at, 'Y-m-d H:i:s'); ?></td>
                        <td><?php echo $this->escape($log->user_name); ?></td>
                        <td><strong><?php echo $this->escape($log->field_name); ?></strong></td>
                        <td style="word-wrap: break-word; max-width: 300px;"><?php echo $this->escape($log->old_value); ?></td>
                        <td style="word-wrap: break-word; max-width: 300px;"><?php echo $this->escape($log->new_value); ?></td>
                    </tr>
                    <?php endforeach; else : ?>
                    <tr><td colspan="5">No changes have been logged.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php echo JHtml::_('bootstrap.endTab'); ?>
        <?php echo JHtml::_('bootstrap.endTabSet'); ?>
    </div>
    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
</form>