<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

$app   = Factory::getApplication();
$input = $app->input;
$prefill_email = $input->getString('email', '');
$prefill_pin   = $input->getString('pin', '');
?>

<div class="login-form">
    <p><?php echo Text::_('COM_BOOKINGMANAGER_CLIENT_PORTAL_LOGIN_INSTRUCTIONS'); ?></p>

    <form action="<?php echo Route::_('index.php?option=com_bookingmanager&task=communication.login'); ?>" method="post">
        <div class="form-group">
            <label for="email"><?php echo Text::_('JFIELD_EMAIL_LABEL'); ?></label>
            <input type="email" name="email" id="email" class="form-control" required value="<?php echo htmlspecialchars($prefill_email); ?>">
        </div>
        <div class="form-group">
            <label for="pin"><?php echo Text::_('COM_BOOKINGMANAGER_PIN_LABEL'); ?></label>
            <input type="text" name="pin" id="pin" class="form-control" required value="<?php echo htmlspecialchars($prefill_pin); ?>">
        </div>
        
        <button type="submit" class="btn btn-primary"><?php echo Text::_('COM_BOOKINGMANAGER_LOGIN_BUTTON'); ?></button>
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>