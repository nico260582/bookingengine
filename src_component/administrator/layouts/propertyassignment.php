<?php
defined('_JEXEC') or die;

$allProperties = $displayData['all_properties'] ?? [];
$selectedIds = $displayData['value'] ?? [];
$currentSupplierId = $displayData['form']->getData()->get('id', 0);
?>

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
        <label><?php echo $displayData['label']; ?></label>
    </div>
    <div class="controls">
        <div class="property-assignment-container">
            <div class="property-list-box" id="available-properties">
                <h5>Available Properties</h5>
                <?php foreach ($allProperties as $property) : ?>
                    <?php
                        $isAssigned = !empty($property->assignment);
                        $isAssignedToCurrent = $isAssigned && $property->assignment['is_current'];
                        // Show in 'Available' if it's not assigned to ANYONE
                        if (!$isAssigned) : ?>
                            <div class="property-item" data-id="<?php echo $property->id; ?>">
                                <?php echo htmlspecialchars($property->title); ?>
                            </div>
                        <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <div class="property-list-box" id="selected-properties">
                <h5>Assigned to this Supplier</h5>
                <?php foreach ($allProperties as $property) : ?>
                     <?php
                        $isAssigned = !empty($property->assignment);
                        $isAssignedToCurrent = $isAssigned && $property->assignment['is_current'];
                        // Show in 'Selected' if it's assigned to THIS supplier
                        if ($isAssignedToCurrent) : ?>
                        <div class="property-item" data-id="<?php echo $property->id; ?>">
                            <?php echo htmlspecialchars($property->title); ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <!-- This hidden select will be populated by the javascript -->
        <select name="<?php echo $displayData['name']; ?>[]" id="<?php echo $displayData['id']; ?>_hidden_select" multiple="multiple" style="display:none;">
             <?php foreach ($selectedIds as $id) : ?>
                <option value="<?php echo $id; ?>" selected="selected"></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const availableBox = document.getElementById('available-properties');
    const selectedBox = document.getElementById('selected-properties');
    const hiddenSelect = document.getElementById('<?php echo $displayData['id']; ?>_hidden_select');

    function moveItem(item, toBox) {
        if (item.classList.contains('assigned-other')) {
            return;
        }
        toBox.appendChild(item);
        updateHiddenSelect();
    }

    // Move from Available to Selected
    availableBox.addEventListener('click', function(e) {
        if (e.target.classList.contains('property-item')) {
            moveItem(e.target, selectedBox);
        }
    });

    // Move from Selected to Available
    selectedBox.addEventListener('click', function(e) {
        if (e.target.classList.contains('property-item')) {
            moveItem(e.target, availableBox);
        }
    });

    function updateHiddenSelect() {
        // Clear all options
        hiddenSelect.innerHTML = '';
        const selectedItems = selectedBox.querySelectorAll('.property-item');
        selectedItems.forEach(function(item) {
            const option = document.createElement('option');
            option.value = item.dataset.id;
            option.selected = true;
            hiddenSelect.appendChild(option);
        });
    }
});
</script>
