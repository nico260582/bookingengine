<?php
    defined('_JEXEC') or die;
    use Joomla\CMS\Router\Route;
    use Joomla\CMS\Layout\LayoutHelper;
    use Joomla\CMS\HTML\HTMLHelper;
    use Joomla\CMS\Factory;

    HTMLHelper::_('behavior.formvalidator');
?>

<form action="<?php echo Route::_('index.php?option=com_bookingmanager&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

    <fieldset class="form-horizontal">
        <legend>Property Details</legend>
        <?php echo $this->form->renderField('article_id'); ?>
        <?php echo $this->form->renderField('max_guests'); ?>
        <?php echo $this->form->renderField('number_of_units'); ?>
        <?php echo $this->form->renderField('complexes'); ?>
    </fieldset>

    <hr>

    <fieldset class="form-horizontal">
        <legend>Property Rates</legend>
        <?php if (isset($this->item->ratesData) && !empty($this->item->ratesData->seasons) && !empty($this->item->ratesData->markets)) : ?>
            <?php
            // Prepare active markets data for easy lookup
            $activeMarkets = [];
            if (isset($this->item->ratesData->active_markets)) {
                $activeMarkets = is_array($this->item->ratesData->active_markets) ? $this->item->ratesData->active_markets : json_decode($this->item->ratesData->active_markets, true);
                if (!is_array($activeMarkets)) $activeMarkets = [];
            }
            ?>
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th rowspan="2" style="vertical-align: middle;">Season</th>
                        <?php foreach ($this->item->ratesData->markets as $market) : ?>
                            <th colspan="3" class="text-center market-header-<?php echo str_replace(' ', '-', $this->escape($market->market_name)); ?>">
                                <?php echo $this->escape($market->market_name); ?>
                                <?php if ($market->market_name !== 'Global Rate') : ?>
                                    <br/>
                                    <label class="small">
                                        <input type="checkbox" class="market-activation-checkbox" data-market-name="<?php echo $this->escape($market->market_name); ?>"
                                               name="jform[active_markets][<?php echo $this->escape($market->market_name); ?>]" value="1"
                                               <?php echo (!empty($activeMarkets) && in_array($market->market_name, $activeMarkets)) ? 'checked' : ''; ?>>
                                        Activate Market
                                    </label>
                                <?php endif; ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <?php foreach ($this->item->ratesData->markets as $market) : ?>
                            <th class="market-col-<?php echo str_replace(' ', '-', $this->escape($market->market_name)); ?>">Rate (<?php echo $this->escape($market->currency); ?>)</th>
                            <th class="market-col-<?php echo str_replace(' ', '-', $this->escape($market->market_name)); ?>">Override Commission</th>
                            <th class="market-col-<?php echo str_replace(' ', '-', $this->escape($market->market_name)); ?>">Commission %</th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->item->ratesData->seasons as $season) :
                        $seasonRates = $this->item->ratesData->rates[$season->name]->rates ?? [];
                    ?>
                    <tr>
                        <td>
                            <?php echo $this->escape($season->name); ?><br/>
                            <small><?php echo HTMLHelper::_('date', $season->start_date, 'd M Y'); ?> to <?php echo HTMLHelper::_('date', $season->end_date, 'd M Y'); ?></small>
                        </td>
                        <?php foreach ($this->item->ratesData->markets as $market) :
                            $marketName = $market->market_name;
                            $marketRateData = $seasonRates[$marketName] ?? [];
                            $rateValue = $marketRateData['rate'] ?? '';
                            $overrideChecked = !empty($marketRateData['override_commission']) ? 'checked' : '';
                            $commissionValue = $marketRateData['commission'] ?? '';
                            $supplierCommission = $season->admin_commission ?? 0;
                            $isMarketActive = ($marketName === 'Global Rate' || (!empty($activeMarkets) && in_array($marketName, $activeMarkets)));
                        ?>
                            <td class="market-col-<?php echo str_replace(' ', '-', $this->escape($market->market_name)); ?>">
                                <input type="number" step="0.01" name="jform[rates][<?php echo $this->escape($season->name); ?>][<?php echo $this->escape($marketName); ?>][rate]" value="<?php echo $this->escape($rateValue); ?>" class="input-small" <?php if (!$isMarketActive) echo 'disabled'; ?> />
                            </td>
                            <td class="text-center market-col-<?php echo str_replace(' ', '-', $this->escape($market->market_name)); ?>">
                                <input type="checkbox" name="jform[rates][<?php echo $this->escape($season->name); ?>][<?php echo $this->escape($marketName); ?>][override_commission]" value="1" class="override-commission-checkbox" <?php echo $overrideChecked; ?> <?php if (!$isMarketActive) echo 'disabled'; ?> />
                            </td>
                            <td class="market-col-<?php echo str_replace(' ', '-', $this->escape($market->market_name)); ?>">
                                <input type="number" step="0.01" name="jform[rates][<?php echo $this->escape($season->name); ?>][<?php echo $this->escape($marketName); ?>][commission]" value="<?php echo $this->escape($commissionValue); ?>" class="input-small commission-value-input" <?php if (!$isMarketActive || !$overrideChecked) echo 'disabled'; ?> />
                                <div class="commission-source-info small" style="color: #666;">
                                    (Default: <?php echo $this->escape($supplierCommission); ?>%)
                                </div>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif (isset($this->item->ratesData) && isset($this->item->ratesData->error)) : ?>
            <div class="alert alert-warning"><?php echo $this->escape($this->item->ratesData->error); ?></div>
        <?php else : ?>
            <div class="alert">No seasons found. Please define seasons for the supplier assigned to this property.</div>
        <?php endif; ?>
    </fieldset>

    <input type="hidden" name="task" value="" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('item-form');

    if (container) {
        // Commission checkbox logic
        container.addEventListener('change', function(e) {
            if (e.target.classList.contains('override-commission-checkbox')) {
                const checkbox = e.target;
                const commissionInput = checkbox.closest('td').nextElementSibling.querySelector('.commission-value-input');
                if (commissionInput) {
                    commissionInput.disabled = !checkbox.checked;
                }
            }
        });

        // Market activation logic
        container.addEventListener('change', function(e) {
            if (e.target.classList.contains('market-activation-checkbox')) {
                const activationCheckbox = e.target;
                const marketName = activationCheckbox.dataset.marketName.replace(/ /g, '-');
                const isChecked = activationCheckbox.checked;

                const inputsToToggle = container.querySelectorAll('.market-col-' + marketName + ' input');
                inputsToToggle.forEach(function(input) {
                    input.disabled = !isChecked;
                    // Re-apply commission logic
                    if (input.classList.contains('commission-value-input')) {
                        const overrideCheckbox = input.closest('tr').querySelector('.market-col-' + marketName + ' .override-commission-checkbox');
                        if (overrideCheckbox && !overrideCheckbox.checked) {
                            input.disabled = true;
                        }
                    }
                });
            }
        });

        // Set initial state for market activation on page load
        document.querySelectorAll('.market-activation-checkbox').forEach(function(activationCheckbox) {
            const marketName = activationCheckbox.dataset.marketName.replace(/ /g, '-');
            const isChecked = activationCheckbox.checked;

            const inputsToToggle = container.querySelectorAll('.market-col-' + marketName + ' input');
            inputsToToggle.forEach(function(input) {
                input.disabled = !isChecked;
                // Re-apply commission logic
                if (input.classList.contains('commission-value-input')) {
                    const overrideCheckbox = input.closest('tr').querySelector('.market-col-' + marketName + ' .override-commission-checkbox');
                    if (overrideCheckbox && !overrideCheckbox.checked) {
                        input.disabled = true;
                    }
                }
            });
        });
    }
});
</script>
