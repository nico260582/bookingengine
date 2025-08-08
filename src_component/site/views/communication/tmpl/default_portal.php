<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Date\Date;

HTMLHelper::_('behavior.editor');
?>

<div class="portal-container">
    <?php if (count($this->userRequests) > 1) : ?>
    <div class="booking-switcher">
        <form action="<?php echo Route::_('index.php?option=com_bookingmanager&view=communication'); ?>" method="post" name="bookingSwitcherForm" id="bookingSwitcherForm">
            <label for="booking_request_id">Your Bookings:</label>
            <select name="request_id" id="booking_request_id" onchange="this.form.submit()">
                <?php foreach ($this->userRequests as $userRequest) : ?>
                    <option value="<?php echo $userRequest->id; ?>" <?php echo ($this->request->id == $userRequest->id) ? 'selected' : ''; ?>>
                        <?php echo $this->escape($userRequest->booking_ref . ' - ' . $userRequest->property_name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
             <input type="hidden" name="task" value="communication.switchBooking" />
             <?php echo HTMLHelper::_('form.token'); ?>
        </form>
    </div>
    <?php endif; ?>

    <div class="booking-summary">
        <div class="summary-header">
            <h3><?php echo Text::sprintf('COM_BOOKINGMANAGER_PORTAL_HEADING', $this->escape($this->request->booking_ref)); ?></h3>
            <?php
                $status = $this->request->status ?? 'Unknown';
                $statusClass = 'status-badge status-' . strtolower(preg_replace('/[^a-z0-9]/i', '', $status));
            ?>
            <span class="<?php echo $statusClass; ?>"><?php echo $this->escape($status); ?></span>
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


    <div class="conversation-history">
        <h4><?php echo Text::_('COM_BOOKINGMANAGER_CONVERSATION_HEADING'); ?></h4>
        <div class="timeline">
            <?php if (empty($this->messages)) : ?>
                <p><?php echo Text::_('COM_BOOKINGMANAGER_NO_MESSAGES_TEXT'); ?></p>
            <?php else : ?>
                <?php foreach ($this->messages as $message) : ?>
                    <div class="timeline-item <?php echo strpos($message->author, '(Client)') !== false ? 'client' : 'admin'; ?>">
                        <div class="timeline-content">
                            <div class="message-header">
                                <span class="message-author"><?php echo $this->escape($message->author); ?></span>
                                <span class="message-date" data-utc-date="<?php echo (new Date($message->created_at))->format('c'); ?>"><?php echo (new Date($message->created_at))->format('d M Y, H:i'); ?></span>
                            </div>
                            <div class="message-body">
                                <?php echo strpos($message->author, '(Client)') !== false ? nl2br($this->escape(trim($message->message))) : $message->message; ?>
                                <?php if (!empty($message->attachments)) : ?>
                                    <div class="message-attachments">
                                        <strong><?php echo Text::_('COM_BOOKINGMANAGER_ATTACHMENTS_HEADING'); ?>:</strong>
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

    <div class="reply-form">
        <h4><?php echo Text::_('COM_BOOKINGMANAGER_SEND_REPLY_HEADING'); ?></h4>
        <form action="<?php echo Route::_('index.php?option=com_bookingmanager&task=communication.addClientMessage'); ?>" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <?php
                $editor = JEditor::getInstance(Factory::getConfig()->get('editor'));
                echo $editor->display('message', '', '100%', '250', '60', '20', false);
                ?>
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