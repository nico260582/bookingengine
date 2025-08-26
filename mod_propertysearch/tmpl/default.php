<?php defined('_JEXEC') or die; ?>
<div class="trivago-search-wrapper">
    <form id="mod-propertysearch-form" class="trivago-search-form" action="<?php echo JRoute::_('index.php?option=com_bookingmanager&view=searchresults'); ?>" method="get">
        <div class="trivago-form-row">
            <div class="trivago-form-group destination-group">
                <label for="main_region_id" class="trivago-label"><span class="icon-map-marker"></span> Destination</label>
                <select name="main_region_id" id="main_region_id" class="trivago-input">
                    <option value="">Select Main Region</option>
                    <?php foreach ($mainRegions as $region) : ?>
                        <option value="<?php echo $region->id; ?>">
                            <?php echo htmlspecialchars($region->name, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="trivago-form-group dates-group">
                <label for="date-range-picker" class="trivago-label"><span class="icon-calendar"></span> Check-in / Check-out</label>
                <input id="date-range-picker" type="text" name="dates" class="trivago-input" placeholder="Select your dates">
            </div>
            <div class="trivago-form-group occupancy-group">
                <label for="adults" class="trivago-label"><span class="icon-user"></span> Guests</label>
                <div class="trivago-occupancy-inputs">
                    <input type="number" name="adults" id="adults" class="trivago-input" min="1" value="2" title="Adults" placeholder="Adults">
                    <input type="number" name="children" id="children" class="trivago-input" min="0" value="0" title="Children" placeholder="Children">
                </div>
            </div>
            <div class="trivago-form-group button-group">
                <button type="submit" class="trivago-search-button">Search</button>
            </div>
        </div>
        <div id="sub-regions-container" class="sub-regions-container">
            <!-- Sub-regions will be dynamically inserted here -->
        </div>

        <input type="hidden" name="sub_region_ids" id="sub_region_ids" value="">
        <input type="hidden" name="option" value="com_bookingmanager">
        <input type="hidden" name="view" value="searchresults">
    </form>
</div>
