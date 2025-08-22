<?php
defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

// Bind the form to the item data from the view.
$this->form->bind($this->item);

?>

<form action="<?php echo Route::_('index.php?option=com_bookingmanager&view=regions&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
    <div class="row-fluid">
        <div class="span4">
            <div class="card">
                <h5 class="card-header"><?php echo JText::_('COM_BOOKINGMANAGER_ADD_OR_EDIT_REGION'); ?></h5>
                <div class="card-body">
                    <fieldset class="form-horizontal">
                        <input type="hidden" name="jform[id]" id="jform_id" value="<?php echo $this->item->id; ?>">
                        <?php echo $this->form->renderField('name'); ?>
                        <?php echo $this->form->renderField('parent_id'); ?>
                        <?php echo $this->form->renderField('state'); ?>
                    </fieldset>
                </div>
            </div>
        </div>

        <div class="span8">
            <?php
            function buildNestedList($items, $level = 0) {
                if (empty($items)) return;
                echo '<ul class="list-group list-group-nested level-' . $level . '">';
                foreach ($items as $item) {
                    $deleteUrl = 'index.php?option=com_bookingmanager&task=regions.delete&cid[]=' . (int) $item->id;
                    ?>
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><?php echo $item->name; ?></span>
                            <span class="actions">
                                <a href="javascript:void(0);" onclick="populateForm(<?php echo htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8'); ?>);" class="btn btn-mini"><i class="icon-edit"></i> Edit</a>
                                <a href="<?php echo $deleteUrl; ?>" class="btn btn-mini btn-danger" onclick="return confirm('Are you sure you want to delete this region?');"><i class="icon-trash"></i> Delete</a>
                            </span>
                        </div>
                        <?php
                        if (!empty($item->children)) {
                            buildNestedList($item->children, $level + 1);
                        }
                        ?>
                    </li>
                    <?php
                }
                echo '</ul>';
            }
            buildNestedList($this->nestedItems);
            ?>
        </div>
    </div>

    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>

<script type="text/javascript">
    Joomla.submitbutton = function(task) {
        if (task == 'region.cancel' || document.formvalidator.isValid(document.getElementById('adminForm'))) {
            Joomla.submitform(task, document.getElementById('adminForm'));
        }
    };

    function populateForm(data) {
        // Reset the form for new item
        Joomla.submitbutton('region.cancel');

        // Populate the form with the data of the selected region
        document.getElementById('jform_id').value = data.id;
        document.getElementById('jform_name').value = data.name;
        document.getElementById('jform_parent_id').value = data.parent_id;
        document.getElementById('jform_state').value = data.state;
    }
</script>
