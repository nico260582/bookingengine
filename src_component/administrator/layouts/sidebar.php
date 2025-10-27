<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$view = JFactory::getApplication()->input->getCmd('view', 'bookingrequests');

$links = [
    'bookingrequests' => Text::_('Booking Requests'),
    'properties' => Text::_('Properties'),
    'complexes' => Text::_('Complexes'),
    'suppliers' => Text::_('Suppliers'),
    'propertyrates' => Text::_('Property Rates'),
    'templates' => Text::_('Email Templates'),
    'diagnostic' => Text::_('Diagnostic'),
];
?>
<div class="sidebar-nav">
    <ul class="nav nav-tabs nav-stacked">
        <?php foreach ($links as $link => $title) : ?>
            <li<?php if ($view === $link) echo ' class="active"'; ?>>
                <a href="index.php?option=com_bookingmanager&view=<?php echo $link; ?>"><?php echo $title; ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
