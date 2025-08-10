<?php defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
?>
<div class="booking-form-container" id="booking-form-wrapper">
    <div class="booking-form-header">
        <div id="starting-from-price" style="font-size: 1.5rem; font-weight: bold; color: #333; text-align: center; margin-bottom: 10px;"></div>
        <h5><?php echo htmlspecialchars($articleTitle, ENT_QUOTES, 'UTF-8'); ?></h5>
    </div>
    <form name="bookingForm" id="bookingForm" method="post" class="form-validate">
        <input type="hidden" name="accommodation" value="<?php echo htmlspecialchars($articleTitle, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="accommodation_url" value="<?php echo htmlspecialchars(JUri::current(), ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="article_id" value="<?php echo $articleId; ?>">
        <input type="hidden" id="start-date" name="start_date">
        <input type="hidden" id="end-date" name="end_date">
        <input type="hidden" id="price-estimate-input" name="price_estimate">
        <input type="hidden" id="unit-count-input" name="unit_count" value="1">
        <input type="hidden" id="discount-note-input" name="discount_note" value="">

        <div id="quote-step-1">
            <div class="row">
                <div class="col-12 mb-2">
                    <input id="date-range-picker" type="text" class="form-control" placeholder="Check-in / Check-out">
                    <div id="min-stay-alert" class="alert alert-warning mt-2" style="display: none;"></div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-2">
                    <select id="guest-count" name="guest_count" class="form-select" aria-label="Adults">
                        <?php for ($i = 1; $i <= 10; $i++) : ?>
                            <option value="<?php echo $i; ?>" <?php echo ($i == 2) ? 'selected' : ''; ?>><?php echo $i; ?> Adults</option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-2">
                    <select id="children-count" name="children_count" class="form-select" aria-label="Children">
                        <?php for ($i = 0; $i <= 5; $i++) : ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?> Children</option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <div class="row" id="child-ages-label-row" style="display: none;">
                <div class="col-12 mb-2">
                    <label class="form-label">Age of Children</label>
                    <div id="child-age-notification-area" class="alert alert-info" style="display: none;"></div>
                </div>
            </div>
            <div class="row" id="child-ages-container"></div>
             <div class="row">
                <div class="col-12 mb-2">
                    <input type="text" id="coupon-code" name="coupon_code" class="form-control" placeholder="Coupon Code">
                </div>
            </div>
        </div>

        <div class="price-summary" id="price-summary-container" style="display: none;">
            <div id="price-estimate-display" class="price-total">Est. Price: -</div>
            <div id="discount-applied-alert" class="alert alert-success" style="display: none;"></div>
            <div id="nights-count-display" class="nights-count"></div>
            <div id="unit-count-display" class="units-count">1 Unit</div>
        </div>

        <button type="button" id="get-quote-button" class="btn btn-secondary w-100 mb-2">Calculate Price</button>

        <div id="quote-step-2" style="display: none;">
            <hr>
            <div class="row">
                <div class="col-md-6 mb-2">
                    <input type="text" id="first-name" name="first_name" class="form-control" placeholder="First Name" required>
                </div>
                <div class="col-md-6 mb-2">
                    <input type="text" id="last-name" name="last_name" class="form-control" placeholder="Last Name" required>
                </div>
            </div>
            <div class="row">
                 <div class="col-12 mb-2">
                    <input type="email" id="email" name="email" class="form-control" placeholder="Email" required>
                </div>
            </div>
            <div class="row">
                <div class="col-12 mb-2">
                     <input type="tel" id="telephone" name="telephone" class="form-control" placeholder="Telephone" required>
                     <div class="whatsapp-consent-container">
                        <input type="checkbox" id="whatsapp_consent" name="whatsapp_consent" value="1" checked>
                        <label for="whatsapp_consent">Contact me on WhatsApp <img src="<?php echo JUri::root(true); ?>/modules/mod_bookingform/media/images/whatsapp.svg" alt="WhatsApp" class="whatsapp-icon"></label>
                     </div>
                </div>
            </div>
            <div class="row">
                 <div class="col-12 mb-2">
                    <select id="country-residence" name="country" class="form-select" required>
                        <option value="">Country of Residence...</option>
                        <?php foreach ($countries as $country) : ?>
                            <option value="<?php echo htmlspecialchars($country['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-dial-code="<?php echo htmlspecialchars($country['dial_code'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-iso-code="<?php echo strtolower(htmlspecialchars($country['code'], ENT_QUOTES, 'UTF-8')); ?>"
                                    <?php if ($country['name'] === 'Mauritius') echo 'selected'; ?>>
                                <?php echo htmlspecialchars($country['flag'] . ' ' . $country['name'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-12 mb-2">
                    <textarea id="message" name="message" class="form-control" rows="3" placeholder="Your Message"></textarea>
                </div>
            </div>
            <button type="submit" id="submit-button" class="btn btn-primary w-100">
                <span class="button-text">Send Booking Request</span>
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
            </button>
            <div id="price-disclaimer" style="display: none;">This is an estimate. Final price will be confirmed by our team.</div>
        </div>
        <div class="row">
            <div class="col-12 mb-2">
                <textarea id="message" name="message" class="form-control" rows="3" placeholder="Your Message"></textarea>
            </div>
        </div>

        <?php if ($showCouponField) : ?>
        <div class="row">
            <div class="col-12 mb-2">
                <input type="text" id="coupon-code" name="coupon_code" class="form-control" placeholder="Coupon Code">
            </div>
        </div>
        <?php endif; ?>

        <div class="price-summary">
            <div id="price-estimate-display" class="price-total">Est. Price: -</div>
            <div id="discount-applied-alert" class="alert alert-success" style="display: none;"></div>
            <div id="nights-count-display" class="nights-count"></div>
            <div id="unit-count-display" class="units-count">1 Unit</div>
        </div>

        <button type="submit" id="submit-button" class="btn btn-primary w-100">
            <span class="button-text">Send Booking Request</span>
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
        </button>

        <div id="price-disclaimer" style="display: none;">This is an estimate. Final price will be confirmed by our team.</div>
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
<div id="thank-you-message" style="display: none;">
    <h3>Thank you for your request!</h3>
    <p>We have received your booking request and our team will get back to you shortly.</p>
    <p>Your reference is: <strong id="booking-ref-display"></strong></p>
</div>