<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

// The form field now passes the already assigned properties
$assignedProperties = $displayData['assignedProperties'] ?? [];
$currentSupplierId = $displayData['currentSupplierId'] ?? 0;

// We need a hidden select list to store the values for the form
?>
<select name="<?php echo $displayData['name']; ?>[]" id="<?php echo $displayData['id']; ?>_hidden_select" multiple="multiple" style="display:none;">
    <?php if ($assignedProperties) : ?>
        <?php foreach ($assignedProperties as $property) : ?>
            <option value="<?php echo $property->id; ?>" selected="selected"><?php echo htmlspecialchars($property->title); ?></option>
        <?php endforeach; ?>
    <?php endif; ?>
</select>
<?php
?>

<div class="control-group">
    <div class="control-label">
        <label><?php echo Text::_($displayData['label']); ?></label>
    </div>
    <div class="controls">
        <div class="property-assignment-ajax-container"
             id="<?php echo $displayData['id']; ?>_container"
             data-supplier-id="<?php echo $currentSupplierId; ?>"
             data-field-id="<?php echo $displayData['id']; ?>"
             data-field-name="<?php echo $displayData['name']; ?>"
             data-form-token="<?php echo Session::getFormToken(); ?>">

            <div class="property-search-bar">
                <input type="text" id="<?php echo $displayData['id']; ?>_search" placeholder="Search for properties..." class="input-medium">
            </div>

            <div class="property-assignment-boxes">
                <div class="property-list-box" id="<?php echo $displayData['id']; ?>_results">
                    <h5>Search Results</h5>
                    <div class="property-list-results">
                        <!-- AJAX results will be loaded here -->
                        <div class="property-item-placeholder">Type to search for properties.</div>
                    </div>
                </div>

                <div class="property-list-box" id="<?php echo $displayData['id']; ?>_selected">
                    <h5>Assigned to this Supplier</h5>
                    <div class="property-list-selected">
                        <?php if (empty($assignedProperties)) : ?>
                            <div class="property-item-placeholder" id="<?php echo $displayData['id']; ?>_selected_placeholder">No properties assigned.</div>
                        <?php else : ?>
                            <?php foreach ($assignedProperties as $property) : ?>
                                <div class="property-item" data-id="<?php echo $property->id; ?>">
                                    <span><?php echo htmlspecialchars($property->title); ?></span>
                                    <button type="button" class="btn btn-mini btn-danger remove-property">X</button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Adding some styles for the new layout */
    .property-assignment-ajax-container .property-search-bar { margin-bottom: 10px; }
    .property-assignment-ajax-container .property-assignment-boxes { display: flex; gap: 20px; }
    .property-assignment-ajax-container .property-list-box { width: 45%; border: 1px solid #ccc; padding: 10px; height: 400px; overflow-y: auto; }
    .property-assignment-ajax-container .property-list-box h5 { margin-top: 0; }
    .property-assignment-ajax-container .property-item { display: flex; justify-content: space-between; align-items: center; padding: 5px; cursor: pointer; border-bottom: 1px solid #eee; }
    .property-assignment-ajax-container .property-item:hover { background-color: #f0f0f0; }
    .property-assignment-ajax-container .property-item.assigned-other { background-color: #f2f2f2; color: #999; cursor: not-allowed; }
    .property-assignment-ajax-container .property-item.assigned-other .supplier-abbr { font-weight: bold; margin-left: 10px; }
    .property-assignment-ajax-container .remove-property { visibility: hidden; }
    .property-assignment-ajax-container .property-item:hover .remove-property { visibility: visible; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initPropertyAssignment('<?php echo $displayData['id']; ?>_container');
});
</script>
