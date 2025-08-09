<?php
defined('_JEXEC') or die;

$allProperties = $displayData['all_properties'] ?? [];
$selectedIds = $displayData['value'] ?? [];
$currentSupplierId = $displayData['form']->getData()->id ?? 0;
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
                        if ($isAssigned && !$isAssignedToCurrent) : ?>
                            <div class="property-item assigned-other" title="Assigned to <?php echo htmlspecialchars($property->assignment['abbreviation']); ?>">
                                <?php echo htmlspecialchars($property->title); ?>
                                <span class="supplier-abbr">(<?php echo htmlspecialchars($property->assignment['abbreviation']); ?>)</span>
                            </div>
                        <?php elseif (!$isAssigned) : ?>
                            <div class="property-item" data-id="<?php echo $property->id; ?>">
                                <?php echo htmlspecialchars($property->title); ?>
                            </div>
                        <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <div class="property-list-box" id="selected-properties">
                <h5>Assigned to this Supplier</h5>
                <?php foreach ($allProperties as $property) : ?>
                    <?php if (!empty($property->assignment) && $property->assignment['is_current']) : ?>
                        <div class="property-item" data-id="<?php echo $property->id; ?>">
                            <?php echo htmlspecialchars($property->title); ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <div id="hidden-inputs-container">
            <?php foreach ($selectedIds as $id) : ?>
                <input type="hidden" name="<?php echo $displayData['name']; ?>[]" value="<?php echo $id; ?>">
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const availableBox = document.getElementById('available-properties');
    const selectedBox = document.getElementById('selected-properties');
    const hiddenInputsContainer = document.getElementById('hidden-inputs-container');

    function moveItem(item, toBox) {
        if (item.classList.contains('assigned-other')) {
            return;
        }
        toBox.appendChild(item);
        updateHiddenInputs();
    }

    availableBox.addEventListener('click', function(e) {
        if (e.target.classList.contains('property-item')) {
            moveItem(e.target, selectedBox);
        }
    });

    selectedBox.addEventListener('click', function(e) {
        if (e.target.classList.contains('property-item')) {
            moveItem(e.target, availableBox);
        }
    });

    function updateHiddenInputs() {
        hiddenInputsContainer.innerHTML = '';
        const selectedItems = selectedBox.querySelectorAll('.property-item');
        selectedItems.forEach(function(item) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = '<?php echo $displayData['name']; ?>[]';
            input.value = item.dataset.id;
            hiddenInputsContainer.appendChild(input);
        });
    }
});
</script>
