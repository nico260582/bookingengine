document.addEventListener('DOMContentLoaded', function() {
    const mainRegionField = document.querySelector('#jform_main_region_id');
    const subRegionField = document.querySelector('#jform_sub_region_id');

    if (subRegionField) {
        let originalSubRegionValue = subRegionField.value;

        function updateSubRegions(callback) {
            const mainRegionId = mainRegionField.value;
            const url = `index.php?option=com_bookingmanager&task=subregions.getSubRegions&format=json&main_region_id=${mainRegionId}`;

            // Clear existing options but keep the first one
            while (subRegionField.options.length > 1) {
                subRegionField.remove(1);
            }

            if (mainRegionId) {
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.data) {
                            data.data.forEach(item => {
                                const option = new Option(item.name, item.id);
                                subRegionField.add(option);
                            });
                        }
                    })
                    .finally(() => {
                        if (callback) callback();
                    });
            } else {
                if (callback) callback();
            }
        }

        mainRegionField.addEventListener('change', function() {
            originalSubRegionValue = ''; // When main region changes, clear old sub-region value
            updateSubRegions(() => {
                // Re-initialize chosen after updating options
                jQuery(subRegionField).trigger('chosen:updated');
            });
        });

        // Initial load
        if (mainRegionField.value) {
            updateSubRegions(() => {
                // Set the original value after the options are loaded
                if (originalSubRegionValue) {
                    subRegionField.value = originalSubRegionValue;
                }
                // Re-initialize chosen after updating options
                jQuery(subRegionField).trigger('chosen:updated');
            });
        }
    }
});
