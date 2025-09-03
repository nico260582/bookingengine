<?php
defined('_JEXEC') or die;
?>

<div class="terms-and-conditions">
    <?php if ($this->terms) : ?>
        <h1>Terms and Conditions for Booking #<?php echo $this->escape($this->terms->booking_ref); ?></h1>
        <h2>Property: <?php echo $this->escape($this->terms->property_name); ?></h2>
        <hr>
        <div class="terms-content">
            <?php echo $this->terms->terms_content; ?>
        </div>
    <?php else : ?>
        <div class="alert alert-warning">
            The requested terms and conditions could not be found.
        </div>
    <?php endif; ?>
</div>
