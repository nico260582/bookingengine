<?php 
defined('_JEXEC') or die; 
?>
<style>
    .placeholder-wrapper { margin-top: 15px; border-top: 1px solid #eee; padding-top: 15px; }
    .placeholder-heading { font-weight: bold; margin-bottom: 5px; }
    .placeholder-tag {
        display: inline-block;
        padding: 4px 8px;
        background-color: #f0f0f0;
        border: 1px solid #ccc;
        border-radius: 4px;
        margin-right: 5px;
        margin-bottom: 5px;
        cursor: pointer;
        font-family: monospace;
        font-size: 0.9em;
    }
    .placeholder-tag:hover { background-color: #e0e0e0; border-color: #999; }
</style>

<form action="<?php echo JRoute::_('index.php?option=com_bookingmanager&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
    <div class="form-horizontal">
        <?php echo $this->form->renderFieldset('details'); ?>
    </div>

    <div class="placeholder-wrapper">
        <h4>Available Placeholders</h4>
        <p>Click on a tag to insert it into the editor at the current cursor position.</p>
        <?php foreach ($this->placeholders as $group => $tags) : ?>
            <div class="placeholder-group">
                <p class="placeholder-heading"><?php echo $this->escape($group); ?></p>
                <?php foreach ($tags as $tag) : ?>
                    <span class="placeholder-tag"><?php echo $this->escape($tag); ?></span>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
</form>

<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        const editorId = 'jform_body';
        const onEditorReady = function() {
            document.querySelectorAll('.placeholder-tag').forEach(function(tag) {
                tag.addEventListener('click', function() {
                    if (Joomla.editors.instances[editorId]) {
                        Joomla.editors.instances[editorId].replaceSelection(this.textContent);
                    }
                });
            });
        };

        if (typeof Joomla !== 'undefined' && Joomla.editors && Joomla.editors.instances[editorId]) {
            onEditorReady();
        } else {
            const editorCheck = setInterval(function() {
                if (typeof Joomla !== 'undefined' && Joomla.editors && Joomla.editors.instances[editorId]) {
                    clearInterval(editorCheck);
                    onEditorReady();
                }
            }, 100);
        }
    });
</script>