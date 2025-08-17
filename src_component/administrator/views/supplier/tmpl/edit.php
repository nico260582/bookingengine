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
            <style>
                .property-assignment-container { display: flex; gap: 20px; }
                .property-list-box { width: 45%; border: 1px solid #ccc; padding: 10px; height: 400px; overflow-y: auto; }
                .property-list-box h5 { margin-top: 0; }
                .property-item { padding: 5px; cursor: pointer; border-bottom: 1px solid #eee; }
                .property-item:hover { background-color: #f0f0f0; }
                .property-item.assigned-other { background-color: #f2f2f2; color: #999; cursor: not-allowed; }
                .property-item.assigned-other .supplier-abbr { font-weight: bold; margin-left: 10px; }
            </style>
            <div class="control-group">
                <div class="control-label">
                    <label>Assign to Properties</label>
                </div>
                <div class="controls">
                    <div class="property-assignment-container">
                        <div class="property-list-box" id="available-properties">
                            <h5>Available Properties</h5>
                            <!-- JS will populate this -->
                        </div>
                        <div class="property-list-box" id="selected-properties">
                            <h5>Assigned to this Supplier</h5>
                            <!-- JS will populate this -->
                        </div>
                    </div>
                    <!-- The hidden field will be managed by JS -->
                    <?php echo $this->form->getInput('properties'); ?>
                </div>
            </div>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const allProperties = <?php echo json_encode(array_values($this->allProperties)); ?>;
                const availableBox = document.getElementById('available-properties');
                const selectedBox = document.getElementById('selected-properties');
                const hiddenInput = document.getElementById('jform_properties');

                function renderLists() {
                    availableBox.innerHTML = '<h5>Available Properties</h5>';
                    selectedBox.innerHTML = '<h5>Assigned to this Supplier</h5>';

                    allProperties.forEach(prop => {
                        const item = document.createElement('div');
                        item.classList.add('property-item');
                        item.dataset.id = prop.id;
                        item.textContent = prop.title;

                        if (prop.assignment) {
                            if (prop.assignment.is_current) {
                                selectedBox.appendChild(item);
                            } else {
                                item.classList.add('assigned-other');
                                item.innerHTML += ` <span class="supplier-abbr">(${prop.assignment.abbreviation})</span>`;
                                availableBox.appendChild(item);
                            }
                        } else {
                            availableBox.appendChild(item);
                        }
                    });
                    updateHiddenInput(); // Set initial value
                }

                function moveItem(item) {
                    if (item.classList.contains('assigned-other')) return;

                    const parent = item.parentElement;
                    const targetBox = (parent.id === 'available-properties') ? selectedBox : availableBox;
                    targetBox.appendChild(item);
                    updateHiddenInput();
                }

                function updateHiddenInput() {
                    if (!hiddenInput) return;
                    const selectedItems = selectedBox.querySelectorAll('.property-item');
                    const ids = Array.from(selectedItems).map(item => item.dataset.id);
                    hiddenInput.value = ids.join(',');
                }

                availableBox.addEventListener('click', e => {
                    if (e.target.classList.contains('property-item')) moveItem(e.target);
                });
                selectedBox.addEventListener('click', e => {
                    if (e.target.classList.contains('property-item')) moveItem(e.target);
                });

                renderLists();
            });
            </script>
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