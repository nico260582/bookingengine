<?php
defined('_JEXEC') or die;
?>

<div class="terms-and-conditions">
    <h1>Terms and Conditions</h1>
    <p><strong>For Booking Reference:</strong> <?php echo htmlspecialchars($this->booking_ref); ?></p>
    <hr>
    <div class="terms-content">
        <?php echo $this->terms_content; ?>
    </div>
</div>
