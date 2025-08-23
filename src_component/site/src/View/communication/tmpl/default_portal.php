<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Date\Date;

?>

<div class="portal-container container mt-4">
    <?php if (count($this->userRequests) > 1) : ?>
    <div class="booking-switcher mb-3">
        <form action="<?php echo Route::_('index.php?option=com_bookingmanager&view=communication'); ?>" method="post" name="bookingSwitcherForm" id="bookingSwitcherForm" class="row align-items-center">
            <div class="col-auto">
                <label for="booking_request_id" class="col-form-label">Your Bookings:</label>
            </div>
            <div class="col">
                <select name="request_id" id="booking_request_id" onchange="this.form.submit()" class="form-select">
                    <?php foreach ($this->userRequests as $userRequest) : ?>
                        <option value="<?php echo $userRequest->id; ?>" <?php echo ($this->request->id == $userRequest->id) ? 'selected' : ''; ?>>
                            <?php echo $this->escape($userRequest->booking_ref . ' - ' . $userRequest->property_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
             <input type="hidden" name="task" value="communication.switchBooking" />
             <?php echo HTMLHelper::_('form.token'); ?>
        </form>
    </div>
    <?php endif; ?>

    <div class="booking-summary card mb-4">
        <div class="card-header summary-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0"><?php echo Text::sprintf('COM_BOOKINGMANAGER_PORTAL_HEADING', $this->escape($this->request->booking_ref)); ?></h3>
                <?php
                    $status = $this->request->status ?? 'Unknown';
                    $statusClass = 'status-badge status-' . strtolower(preg_replace('/[^a-z0-9]/i', '', $status));
                ?>
                <span class="<?php echo $statusClass; ?>"><?php echo $this->escape($status); ?></span>
                <a href="<?php echo Route::_('index.php?option=com_bookingmanager&task=communication.logout'); ?>" class="btn btn-sm btn-outline-secondary"><?php echo Text::_('COM_BOOKINGMANAGER_LOGOUT_BUTTON'); ?></a>
            </div>
        </div>
        <div class="card-body">
            <p>
                <strong>Property:</strong> <?php echo $this->escape($this->request->property_name); ?><br>
                <strong>Dates:</strong> <?php echo (new Date($this->request->start_date))->format('d M Y'); ?> to <?php echo (new Date($this->request->end_date))->format('d M Y'); ?><br>
                <strong>Guests:</strong> <?php echo $this->escape($this->request->adults); ?> Adults, <?php echo $this->escape($this->request->children); ?> Children (Ages: <?php echo $this->escape($this->request->child_ages); ?>)<br>
                <strong>Estimated Price:</strong> <?php echo $this->escape($this->request->price_estimate); ?><br>
                <?php if (!empty($this->request->discount_note)) : ?>
                    <strong>Note:</strong> <?php echo $this->escape($this->request->discount_note); ?>
                <?php endif; ?>
            </p>
            <div class="booking-modifiers mt-4">
                <h4>Modify Request</h4>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="date_modifier" class="form-label">Dates</label>
                        <input type="text" id="date_modifier" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label for="adults_modifier" class="form-label">Adults</label>
                        <input type="number" id="adults_modifier" name="adults" value="<?php echo $this->request->adults; ?>" min="1" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label for="children_modifier" class="form-label">Children</label>
                        <input type="number" id="children_modifier" name="children" value="<?php echo $this->request->children; ?>" min="0" class="form-control">
                    </div>
                </div>
                <div id="child-ages-portal-container" class="row mt-3">
                    <!-- Child age inputs will be dynamically inserted here -->
                </div>
                <div class="price-recalculation mt-3">
                    <div id="price-estimate-display-portal" class="price-total"></div>
                    <div id="unit-count-display-portal" class="units-count"></div>
                </div>
                <div class="update-actions mt-3">
                    <div id="modification-summary" class="alert alert-warning" style="display: none;"></div>
                    <button id="save-changes-btn" class="btn btn-secondary" style="display: none;">Save Changes & Notify Admin</button>
                </div>
            </div>
        </div>
    </div>


    <div class="conversation-history card mb-4">
        <div class="card-header">
            <h4 class="mb-0"><?php echo Text::_('COM_BOOKINGMANAGER_CONVERSATION_HEADING'); ?></h4>
        </div>
        <div class="card-body timeline">
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
                                    <div class="message-attachments mt-2">
                                        <strong><?php echo Text::_('COM_BOOKINGMANAGER_ATTACHMENTS_HEADING'); ?>:</strong>
                                        <ul class="list-unstyled">
                                            <?php foreach ($message->attachments as $attachment) : ?>
                                                <li>
                                                    <a href="<?php echo JUri::root() . $attachment->file_path; ?>" target="_blank">
                                                        <i class="fas fa-paperclip"></i> <?php echo $this->escape($attachment->file_name); ?>
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

    <div class="reply-form card">
        <div class="card-header">
            <h4 class="mb-0"><?php echo Text::_('COM_BOOKINGMANAGER_SEND_REPLY_HEADING'); ?></h4>
        </div>
        <div class="card-body">
            <form action="<?php echo Route::_('index.php?option=com_bookingmanager&task=addClientMessage'); ?>" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <textarea name="message" id="message" class="form-control" rows="5" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="attachments" class="form-label"><?php echo Text::_('COM_BOOKINGMANAGER_ATTACHMENT_LABEL'); ?></label>
                    <input type="file" name="attachments[]" id="attachments" class="form-control" multiple>
                    <div id="attachment-list" class="mt-2"></div>
                </div>
                <button type="submit" class="btn btn-primary"><?php echo Text::_('COM_BOOKINGMANAGER_SEND_BUTTON'); ?></button>
                <?php echo HTMLHelper::_('form.token'); ?>
            </form>
        </div>
    </div>
</div>
<script src="modules/mod_bookingform/media/js/communication-portal.js"></script>