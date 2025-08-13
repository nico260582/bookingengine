<?php
defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\HTML\HTMLHelper;

// Load bootstrap tabs
HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'details'));
?>

<form action="<?php echo Route::_('index.php?option=com_bookingmanager&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
    <div class="form-horizontal">
        <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'details', 'Details'); ?>
        <div class="row-fluid">
            <div class="span9">
                <div class="form-vertical">
                    <?php echo $this->form->renderField('article_id'); ?>
                    <?php echo $this->form->renderField('max_guests'); ?>
                    <?php echo $this->form->renderField('complex_id'); ?>
                </div>
            </div>
        </div>
        <?php echo HTMLHelper::_('bootstrap.endTab'); ?>

        <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'rates', 'Rates'); ?>
        <div class="row-fluid">
            <div class="span12">
                <?php if (isset($this->item->ratesData) && !empty($this->item->ratesData->seasons)) : ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Season</th>
                                <th>Base Rate</th>
                                <th>Override Admin Commission</th>
                                <th>Admin Commission (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($this->item->ratesData->seasons as $season) :
                                $seasonName = $season->name;
                                $rateInfo = $this->item->ratesData->rates[$seasonName] ?? null;
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($seasonName, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <input type="text" name="jform[rates][<?php echo htmlspecialchars($seasonName, ENT_QUOTES, 'UTF-8'); ?>][base_rate]" value="<?php echo $rateInfo->base_rate ?? ''; ?>" class="input-small">
                                    </td>
                                    <td>
                                        <?php $checked = ($rateInfo && !empty($rateInfo->override_admin_commission)) ? 'checked' : ''; ?>
                                        <input type="checkbox" name="jform[rates][<?php echo htmlspecialchars($seasonName, ENT_QUOTES, 'UTF-8'); ?>][override_admin_commission]" value="1" <?php echo $checked; ?>>
                                    </td>
                                    <td>
                                        <input type="text" name="jform[rates][<?php echo htmlspecialchars($seasonName, ENT_QUOTES, 'UTF-8'); ?>][admin_commission]" value="<?php echo $rateInfo->admin_commission ?? ''; ?>" class="input-small">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php elseif (isset($this->item->ratesData) && isset($this->item->ratesData->error)) : ?>
                    <div class="alert alert-warning"><?php echo $this->item->ratesData->error; ?></div>
                <?php else : ?>
                    <div class="alert">No seasons found. Please define seasons for the supplier assigned to this property.</div>
                <?php endif; ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
    </div>
    <input type="hidden" name="task" value="" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
<?php
HTMLHelper::_('bootstrap.endTabSet');
?>
