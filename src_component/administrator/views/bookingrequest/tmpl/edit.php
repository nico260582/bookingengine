<?php
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
?>

<form action="<?php echo Route::_('index.php?option=com_bookingmanager&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate" enctype="multipart/form-data">
    <div class="main-card">
        <?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>
        <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'details', Text::_('Booking')); ?>
            <div class="row-fluid">
                <div class="span6"><?php echo $this->form->renderFieldset('details'); ?></div>
                <div class="span6"><?php echo $this->form->renderFieldset('pricing'); ?></div>
            </div>
        <?php echo HTMLHelper::_('bootstrap.endTab'); ?>

        <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'client', Text::_('Client')); ?>
            <?php echo $this->form->renderFieldset('client'); ?>
        <?php echo HTMLHelper::_('bootstrap.endTab'); ?>

        <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'communication', Text::_('Communication')); ?>
            <div class="row-fluid">
                <div class="span7">
                    <h4>Conversation History</h4>
                    <div class="well" style="height: 300px; overflow-y: scroll; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                    <?php if ($this->messages) : foreach ($this->messages as $message) : ?>
                        <p><strong><?php echo $this->escape($message->author); ?></strong><small class="pull-right text-muted"><?php echo HTMLHelper::_('date', $message->created_at, 'Y-m-d H:i'); ?></small></p>
                        <div><?php echo $message->message; ?></div><hr>
                    <?php endforeach; else : ?><p>No messages yet.</p><?php endif; ?>
                    </div>
                </div>
                <div class="span5">
                    <h4>Send Message</h4>
                    <div class="control-group">
                        <div class="controls">
                            <?php echo $this->form->getField('admin_message')->renderField(); ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
                            <button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('bookingrequest.addmessage');">Send Message</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php echo HTMLHelper::_('bootstrap.endTab'); ?>

        <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'attachments', Text::_('Attachments')); ?>
            <h4>Upload New File</h4>
            <div class="control-group">
                <div class="control-label"><label for="jform_attachment">New Attachment</label></div>
                <div class="controls"><input type="file" name="jform[attachment]" id="jform_attachment"></div>
            </div>
            <hr>
            <h4>Uploaded Files</h4>
            <?php if (empty($this->attachments)) : ?>
                <p>There are no attachments for this booking request.</p>
            <?php else : ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>File Name</th>
                            <th>Uploaded By</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($this->attachments as $attachment) : ?>
                            <tr>
                                <td>
                                    <a href="<?php echo JUri::root() . $attachment->file_path; ?>" target="_blank">
                                        <?php echo $this->escape($attachment->file_name); ?>
                                    </a>
                                </td>
                                <td><?php echo $this->escape($attachment->uploaded_by); ?></td>
                                <td><?php echo HTMLHelper::_('date', $attachment->created_at, 'Y-m-d H:i'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
        
        <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'logs', Text::_('Change Log')); ?>
            <table class="table table-striped">
                <thead><tr><th>Date</th><th>Admin User</th><th>Field</th><th>Old Value</th><th>New Value</th></tr></thead>
                <tbody>
                    <?php if ($this->logs) : foreach ($this->logs as $log) : ?>
                    <tr>
                        <td><?php echo HTMLHelper::_('date', $log->created_at, 'Y-m-d H:i:s'); ?></td>
                        <td><?php echo $this->escape($log->user_name); ?></td>
                        <td><strong><?php echo $this->escape($log->field_name); ?></strong></td>
                        <td><?php echo $this->escape($log->old_value); ?></td>
                        <td><?php echo $this->escape($log->new_value); ?></td>
                    </tr>
                    <?php endforeach; else : ?>
                    <tr><td colspan="5">No changes have been logged.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
        <?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
    </div>
    <input type="hidden" name="task" value="" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>