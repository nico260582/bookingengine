document.addEventListener('DOMContentLoaded', function() {
    const mainRegionField = document.querySelector('#jform_main_region_id');
    const subRegionField = document.querySelector('#jform_sub_region_id');

    console.log('Property Edit JS Loaded. Main Region Field:', mainRegionField, 'Sub Region Field:', subRegionField);

    if (mainRegionField && subRegionField) {
        let originalSubRegionValue = subRegionField.value;

        function updateSubRegions(callback) {
            const mainRegionId = mainRegionField.value;
            console.log('Updating sub-regions for main_region_id:', mainRegionId);

            while (subRegionField.options.length > 1) {
                subRegionField.remove(1);
            }

            if (mainRegionId && mainRegionId !== '') {
                const url = `index.php?option=com_bookingmanager&task=subregions.getSubRegions&format=json&main_region_id=${mainRegionId}`;
                console.log('Fetching URL:', url);

                fetch(url)
                    .then(response => {
                        console.log('Fetch response received:', response);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('JSON data received:', data);
                        if (data && data.success && data.data) {
                            console.log('Data is valid, populating dropdown.');
                            data.data.forEach(item => {
                                const option = new Option(item.name, item.id);
                                subRegionField.add(option);
                            });
                        } else {
                            console.log('Data received but not in expected format or success is false. Data:', data);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching or parsing sub-regions:', error);
                        alert('An error occurred while fetching sub-regions. Please check the browser console for details.');
                    })
                    .finally(() => {
                        if (callback) {
                            callback();
                        }
                    });
            } else {
                console.log('No Main Region selected, skipping fetch.');
                if (callback) {
                    callback();
                }
            }
        }

        mainRegionField.addEventListener('change', function() {
            console.log('Main region changed.');
            originalSubRegionValue = '';
            updateSubRegions(() => {
                if (typeof jQuery !== 'undefined' && typeof jQuery.fn.chosen !== 'undefined') {
                    jQuery(subRegionField).trigger('chosen:updated');
                }
            });
        });

        if (mainRegionField.value) {
            console.log('Initial load with main region selected.');
            updateSubRegions(() => {
                if (originalSubRegionValue) {
                    subRegionField.value = originalSubRegionValue;
                }
                if (typeof jQuery !== 'undefined' && typeof jQuery.fn.chosen !== 'undefined') {
                    jQuery(subRegionField).trigger('chosen:updated');
                }
            });
        }
    }
});
