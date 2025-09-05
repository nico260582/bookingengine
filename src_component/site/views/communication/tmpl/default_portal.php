<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Date\Date;

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
                $rawStatus = $this->request->status ?? 'Unknown';
                // Construct the language key from the raw status value, e.g., 'waiting_availability' becomes 'COM_BOOKINGMANAGER_STATUS_WAITING_AVAILABILITY'
                $langKey = 'COM_BOOKINGMANAGER_STATUS_' . strtoupper($rawStatus);
                $displayStatus = Text::_($langKey);

                // If a translation doesn't exist, Text::_ returns the key itself. In that case, fall back to the raw status.
                if ($displayStatus === $langKey) {
                    $displayStatus = $rawStatus;
                }

                // The class should be based on the raw status value for consistency
                $statusClass = 'status-badge status-' . strtolower(preg_replace('/[^a-z0-9]/i', '', $rawStatus));
            ?>
            <span class="<?php echo $statusClass; ?>"><?php echo $this->escape($displayStatus); ?></span>
            <a href="<?php echo Route::_('index.php?option=com_bookingmanager&task=communication.logout'); ?>"><?php echo Text::_('COM_BOOKINGMANAGER_LOGOUT_BUTTON'); ?></a>
        </div>
        <p>
            <strong>Property:</strong> <?php echo $this->escape($this->request->property_name); ?><br>
            <strong>Dates:</strong> <?php echo (new Date($this->request->start_date))->format('d M Y'); ?> to <?php echo (new Date($this->request->end_date))->format('d M Y'); ?><br>
            <strong>Guests:</strong> <?php echo $this->escape($this->request->adults); ?> Adults, <?php echo $this->escape($this->request->children); ?> Children (Ages: <?php echo $this->escape($this->request->child_ages); ?>)<br>
            <strong>Estimated Price:</strong> <?php echo $this->escape($this->request->price_estimate); ?><br>
            <?php if (!empty($this->request->final_price) && $this->request->final_price > 0) : ?>
                <strong>Final Price:</strong> <?php echo $this->escape($this->request->final_price); ?><br>
            <?php endif; ?>
            <?php if (!empty($this->request->unit_count)) : ?>
                <strong>Units:</strong> <?php echo $this->escape($this->request->unit_count); ?><br>
            <?php endif; ?>
            <?php if (!empty($this->request->payment_link)) : ?>
                <a href="<?php echo $this->escape($this->request->payment_link); ?>" class="btn btn-success" target="_blank">Pay Now</a><br>
            <?php endif; ?>
            <?php if (!empty($this->request->discount_note)) : ?>
                <strong>Note:</strong> <?php echo $this->escape($this->request->discount_note); ?>
            <?php endif; ?>
        </p>
        <div class="booking-modifiers">
            <h4>Modify Request</h4>
            <div class="modifier-controls">
                <div class="form-group">
                    <label for="date_modifier">Dates</label>
                    <input type="text" id="date_modifier" class="form-control">
                </div>
                <div class="form-group">
                    <label for="adults_modifier">Adults</label>
                    <input type="number" id="adults_modifier" name="adults" value="<?php echo $this->request->adults; ?>" min="1" class="form-control">
                </div>
                <div class="form-group">
                    <label for="children_modifier">Children</label>
                    <input type="number" id="children_modifier" name="children" value="<?php echo $this->request->children; ?>" min="0" class="form-control">
                </div>
            </div>
            <div id="child-ages-portal-container" class="modifier-controls">
                <!-- Child age inputs will be dynamically inserted here -->
            </div>
            <div class="price-recalculation">
                <div id="price-estimate-display-portal" class="price-total"></div>
                <div id="unit-count-display-portal" class="units-count"></div>
            </div>
            <div class="update-actions">
                <div id="modification-summary" class="alert alert-warning" style="display: none;"></div>
                <button id="save-changes-btn" class="btn btn-secondary" style="display: none;">Save Changes & Notify Admin</button>
            </div>
        </div>
    </div>

    <div class="booking-terms-agreement">
        <?php if (!empty($this->request->terms_log_id)) : ?>
            <div class="form-group">
                <input type="checkbox" id="terms_agreed" name="terms_agreed" value="1" <?php echo ($this->request->terms_agreed) ? 'checked disabled' : ''; ?>>
                <label for="terms_agreed">
                    I have read and agree to the <a href="<?php echo Route::_('index.php?option=com_bookingmanager&view=terms&id=' . $this->request->terms_log_id); ?>" target="_blank">Terms & Conditions</a>.
                </label>
                <?php if ($this->request->terms_agreed && !empty($this->request->terms_agreed_at)) : ?>
                    <span class="terms-agreed-date" style="font-style: italic; color: #666;">
                        (Agreed on <span data-utc-date="<?php echo (new Joomla\CMS\Date\Date($this->request->terms_agreed_at))->format('c'); ?>"><?php echo (new Joomla\CMS\Date\Date($this->request->terms_agreed_at))->format('d M Y, H:i'); ?></span>)
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
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
        <form action="<?php echo Route::_('index.php?option=com_bookingmanager&task=addClientMessage'); ?>" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <textarea name="message" id="message" class="form-control" rows="5" required></textarea>
            </div>
            <div class="form-group">
                <label for="attachments"><?php echo Text::_('COM_BOOKINGMANAGER_ATTACHMENT_LABEL'); ?></label>
                <input type="file" name="attachments[]" id="attachments" class="form-control-file" multiple>
                <div id="attachment-list"></div>
            </div>
            <button type="submit" class="btn btn-primary"><?php echo Text::_('COM_BOOKINGMANAGER_SEND_BUTTON'); ?></button>
            <?php echo HTMLHelper::_('form.token'); ?>
        </form>
    </div>
</div>
<script src="modules/mod_bookingform/media/js/communication-portal.js"></script>