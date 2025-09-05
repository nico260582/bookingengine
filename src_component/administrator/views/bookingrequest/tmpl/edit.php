<?php
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
?>

<form action="<?php echo Route::_('index.php?option=com_bookingmanager&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate" enctype="multipart/form-data">
    <div class="main-card">
        <?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>
        <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'details', Text::_('Booking Details')); ?>
            <div class="row-fluid">
                <div class="span6">
                    <h4><?php echo Text::_('Client Details'); ?></h4>
                    <?php echo $this->form->renderFieldset('client'); ?>
                    <hr>
                    <h4><?php echo Text::_('Booking Information'); ?></h4>
                    <?php echo $this->form->renderFieldset('details'); ?>
                </div>
                <div class="span6">
                    <h4><?php echo Text::_('Pricing & Notes'); ?></h4>
                    <?php echo $this->form->renderFieldset('pricing'); ?>
                </div>
            </div>
        <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
        <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'communication', Text::_('Communication')); ?>
            <div class="row-fluid">
                <div class="span7">
                    <h4>Conversation History</h4>
                    <div class="conversation-history-admin">
                        <div class="timeline-admin">
                            <?php if (empty($this->messages)) : ?>
                                <p>No messages yet.</p>
                            <?php else : ?>
                                <?php foreach ($this->messages as $message) : ?>
                                    <div class="timeline-item-admin <?php echo strpos($message->author, '(Client)') !== false ? 'client' : 'admin'; ?>">
                                        <div class="timeline-content-admin">
                                            <div class="message-header-admin">
                                                <span class="message-author-admin"><?php echo $this->escape($message->author); ?></span>
                                                <span class="message-date-admin"><?php echo HTMLHelper::_('date', $message->created_at, 'Y-m-d H:i'); ?></span>
                                            </div>
                                            <div class="message-body-admin">
                                                <?php echo $message->message; ?>
                                                <?php if (!empty($message->attachments)) : ?>
                                                    <div class="message-attachments-admin">
                                                        <strong>Attachments:</strong>
                                                        <ul>
                                                            <?php foreach ($message->attachments as $attachment) : ?>
                                                                <li>
                                                                    <a href="<?php echo JUri::root() . $attachment->file_path; ?>" target="_blank">
                                                                        <?php echo $this->escape($attachment->file_name); ?>
                                                                    </a>
                                                                </li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
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
                        <div class="control-label"><label for="attachments">Attachments</label></div>
                        <div class="controls">
                            <input type="file" name="attachments[]" id="attachments" multiple>
                            <div id="attachment-list"></div>
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

        <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'supplier_communication', Text::_('Supplier Communication')); ?>
            <div class="row-fluid">
                <div class="span7">
                    <h4>Conversation History with Supplier</h4>
                    <div class="conversation-history-admin">
                        <div class="timeline-admin">
                            <?php if (empty($this->supplierMessages)) : ?>
                                <p>No messages sent to the supplier yet.</p>
                            <?php else : ?>
                                <?php foreach ($this->supplierMessages as $message) : ?>
                                    <div class="timeline-item-admin admin">
                                        <div class="timeline-content-admin">
                                            <div class="message-header-admin">
                                                <span class="message-author-admin"><?php echo $this->escape($message->author_name); ?> (Admin)</span>
                                                <span class="message-date-admin"><?php echo HTMLHelper::_('date', $message->sent_at, 'Y-m-d H:i'); ?></span>
                                            </div>
                                            <div class="message-body-admin">
                                                <?php echo $message->message; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="span5">
                    <h4>Send Availability Request</h4>
                    <div class="control-group">
                        <div class="controls">
                            <button type="button" class="btn" id="load-supplier-template-btn">Load Template</button>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
                            <?php echo $this->form->getField('supplier_message')->renderField(); ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
                            <?php echo $this->form->getField('whatsapp_sent')->renderField(); ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
                            <button type="button" class="btn btn-primary" id="send-supplier-email-btn">Send Email</button>
                            <button type="button" class="btn" id="send-whatsapp-supplier-btn">Send via WhatsApp</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php echo HTMLHelper::_('bootstrap.endTab'); ?>

        <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'terms', Text::_('Terms & Conditions'), empty($this->item->terms_log_id)); ?>
        <?php if (!empty($this->item->terms_log_id)) : ?>
            <div class="row-fluid">
                <div class="span12">
                    <h4>Agreement Status</h4>
                    <p>
                        <?php if ($this->item->terms_agreed) : ?>
                            <strong>Agreed on:</strong> <?php echo HTMLHelper::_('date', $this->item->terms_agreed_at, 'Y-m-d H:i:s'); ?>
                        <?php else : ?>
                            <strong>Not yet agreed.</strong>
                        <?php endif; ?>
                    </p>
                    <hr>
                    <h4>Terms Content</h4>
                    <div><?php echo $this->item->terms_content; ?></div>
                </div>
            </div>
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

        <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'activity', Text::_('Client Activity')); ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>IP Address</th>
                        <th>User Agent</th>
                        <th>Screen Size</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($this->activityLogs) : foreach ($this->activityLogs as $log) : ?>
                    <tr>
                        <td><?php echo HTMLHelper::_('date', $log->created_at, 'Y-m-d H:i:s'); ?></td>
                        <td><strong><?php echo $this->escape($log->action_type); ?></strong></td>
                        <td><?php echo $this->escape($log->action_details); ?></td>
                        <td><?php echo $this->escape($log->ip_address); ?></td>
                        <td><?php echo $this->escape($log->user_agent); ?></td>
                        <td><?php echo $this->escape($log->screen_size); ?></td>
                    </tr>
                    <?php endforeach; else : ?>
                    <tr><td colspan="6">No client activity has been logged.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
        <?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
    </div>
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
<?php
$doc = Factory::getDocument();
$ajaxUrls = [
    'upload' => Route::_('index.php?option=com_bookingmanager&task=bookingrequest.upload', false),
    'deleteAttachment' => Route::_('index.php?option=com_bookingmanager&task=bookingrequest.deleteAttachment', false),
    'getSupplierTemplate' => Route::_('index.php?option=com_bookingmanager&task=bookingrequest.getSupplierTemplate', false),
    'sendSupplierMessage' => Route::_('index.php?option=com_bookingmanager&task=bookingrequest.sendSupplierMessage', false),
    'supplier_phone' => $this->item->supplier_contact_phone ?? ''
];
$doc->addScriptOptions('com_bookingmanager.admin', $ajaxUrls);
$doc->addScript(Uri::root(true) . '/media/com_bookingmanager/js/bookingrequest-admin.js');
?>