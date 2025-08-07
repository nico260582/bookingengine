<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Date\Date;
?>

<div class="portal-container">
    <div class="booking-summary">
        <div class="summary-header">
            <h3><?php echo Text::sprintf('COM_BOOKINGMANAGER_PORTAL_HEADING', $this->escape($this->request->booking_ref)); ?></h3>
            <a href="<?php echo Route::_('index.php?option=com_bookingmanager&task=communication.logout'); ?>"><?php echo Text::_('COM_BOOKINGMANAGER_LOGOUT_BUTTON'); ?></a>
        </div>
        <p>
            <strong>Property:</strong> <?php echo $this->escape($this->request->property_name); ?><br>
            <strong>Dates:</strong> <?php echo (new Date($this->request->start_date))->format('d M Y'); ?> to <?php echo (new Date($this->request->end_date))->format('d M Y'); ?><br>
            <strong>Guests:</strong> <?php echo $this->escape($this->request->adults); ?> Adults, <?php echo $this->escape($this->request->children); ?> Children (Ages: <?php echo $this->escape($this->request->child_ages); ?>)<br>
            <strong>Estimated Price:</strong> <?php echo $this->escape($this->request->price_estimate); ?><br>
            <?php if (!empty($this->request->discount_note)) : ?>
                <strong>Note:</strong> <?php echo $this->escape($this->request->discount_note); ?>
            <?php endif; ?>
        </p>
    </div>

    <div class="attachments-section">
        <h4><?php echo Text::_('COM_BOOKINGMANAGER_ATTACHMENTS_HEADING'); ?></h4>
        <?php if (empty($this->attachments)) : ?>
            <p><?php echo Text::_('COM_BOOKINGMANAGER_NO_ATTACHMENTS_TEXT'); ?></p>
        <?php else : ?>
            <ul>
                <?php foreach ($this->attachments as $attachment) : ?>
                    <li>
                        <a href="<?php echo JUri::root() . $attachment->file_path; ?>" target="_blank">
                            <?php echo $this->escape($attachment->file_name); ?>
                        </a>
                        <span class="attachment-meta">(Uploaded by <?php echo $this->escape($attachment->uploaded_by); ?> on <?php echo (new Date($attachment->created_at))->format('d M Y'); ?>)</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <div class="conversation-history">
        <h4><?php echo Text::_('COM_BOOKINGMANAGER_CONVERSATION_HEADING'); ?></h4>
        <?php if (empty($this->messages)) : ?>
            <p><?php echo Text::_('COM_BOOKINGMANAGER_NO_MESSAGES_TEXT'); ?></p>
        <?php else : ?>
            <?php foreach ($this->messages as $message) : ?>
                <div class="message <?php echo strpos($message->author, '(Client)') !== false ? 'client' : 'admin'; ?>">
                    <div class="message-header">
                        <span class="message-author"><?php echo $this->escape($message->author); ?></span>
                        <span class="message-date"><?php echo (new Date($message->created_at))->format('d M Y, H:i'); ?></span>
                    </div>
                    <div class="message-body">
                        <?php echo strpos($message->author, '(Client)') !== false ? nl2br($this->escape($message->message)) : $message->message; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="reply-form">
        <h4><?php echo Text::_('COM_BOOKINGMANAGER_SEND_REPLY_HEADING'); ?></h4>
        <form action="<?php echo Route::_('index.php?option=com_bookingmanager&task=communication.addClientMessage'); ?>" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <textarea name="message" id="message" class="form-control" rows="5" required></textarea>
            </div>
            <div class="form-group">
                <label for="attachment"><?php echo Text::_('COM_BOOKINGMANAGER_ATTACHMENT_LABEL'); ?></label>
                <input type="file" name="attachment" id="attachment" class="form-control-file">
            </div>
            <button type="submit" class="btn btn-primary"><?php echo Text::_('COM_BOOKINGMANAGER_SEND_BUTTON'); ?></button>
            <?php echo HTMLHelper::_('form.token'); ?>
        </form>
    </div>
</div>