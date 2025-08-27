document.addEventListener('DOMContentLoaded', function() {
    const options = Joomla.getOptions('com_bookingmanager');
    let pricingRules = null;

    // Original booking state
    let originalAdults = 0;
    let originalChildren = 0;
    let originalStartDate = '';
    let originalEndDate = '';

    // Current booking state for calculation
    let currentAdults = 0;
    let currentChildren = 0;
    let currentStartDate = '';
    let currentEndDate = '';

    let numberOfNights = 0;
    let seasonRateCounts = {};

    const elements = {
        adultsInput: document.getElementById('adults_modifier'),
        childrenInput: document.getElementById('children_modifier'),
        childAgesContainer: document.getElementById('child-ages-portal-container'),
        datePickerInput: document.getElementById('date_modifier'),
        priceDisplay: document.getElementById('price-estimate-display-portal'),
        unitDisplay: document.getElementById('unit-count-display-portal'),
        summaryDisplay: document.getElementById('modification-summary'),
        saveButton: document.getElementById('save-changes-btn')
    };

    function init() {
        if (!options || !options.booking_id) return;
        logPageView();
        fetchPricingRules();
        convertDatesToLocalTime();

        if (elements.adultsInput) {
            originalAdults = currentAdults = parseInt(elements.adultsInput.value, 10);
            originalChildren = currentChildren = parseInt(elements.childrenInput.value, 10);
            originalStartDate = currentStartDate = options.start_date;
            originalEndDate = currentEndDate = options.end_date;

            elements.adultsInput.addEventListener('input', handleModification);
            elements.childrenInput.addEventListener('input', updateChildAgeInputs);
            elements.saveButton.addEventListener('click', saveChanges);
        }
    }

    function fetchPricingRules() {
        fetch(options.urls.getPricing)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    pricingRules = data.pricingRules;
                    initializeDatePicker();
                    calculateNightsAndSeasons();
                    updateChildAgeInputs();
                } else {
                    console.error('Failed to fetch pricing rules:', data.message);
                }
            })
            .catch(error => console.error('Error fetching pricing rules:', error));
    }

    function initializeDatePicker() {
        if (!elements.datePickerInput) return;
        new Litepicker({
            element: elements.datePickerInput,
            singleMode: false,
            minDate: new Date(),
            startDate: new Date(originalStartDate),
            endDate: new Date(originalEndDate),
            format: 'DD MMM, YYYY',
            setup: (picker) => {
                picker.on('selected', (date1, date2) => {
                    if (date1 && date2) {
                        currentStartDate = date1.format('YYYY-MM-DD');
                        currentEndDate = date2.format('YYYY-MM-DD');
                        calculateNightsAndSeasons();
                        handleModification();
                    }
                });
            }
        });
    }

    function getSeasonForDate(date) {
        if (!pricingRules || !Array.isArray(pricingRules.seasons)) return null;
        const year = date.getFullYear();
        const month = (date.getMonth() + 1).toString().padStart(2, '0');
        const day = date.getDate().toString().padStart(2, '0');
        const dateStr = `${year}-${month}-${day}`;
        for (const season of pricingRules.seasons) {
            if (dateStr >= season.start_date && dateStr <= season.end_date) return season;
        }
        return null;
    }

    function calculateNightsAndSeasons() {
        if (!currentStartDate || !currentEndDate) return;
        seasonRateCounts = {};
        const date1 = new Date(currentStartDate);
        const date2 = new Date(currentEndDate);
        let currentDate = new Date(date1);
        while (currentDate < date2) {
            const season = getSeasonForDate(currentDate);
            if (season) { seasonRateCounts[season.name] = (seasonRateCounts[season.name] || 0) + 1; }
            currentDate.setDate(currentDate.getDate() + 1);
        }
        numberOfNights = Object.values(seasonRateCounts).reduce((a, b) => a + b, 0);
    }

    function updateChildAgeInputs() {
        const childrenCount = parseInt(elements.childrenInput.value, 10);
        elements.childAgesContainer.innerHTML = ''; // Clear previous inputs

        if (childrenCount > 0) {
            // Create a row to hold the inputs for better layout
            const row = document.createElement('div');
            row.className = 'row';

            for (let i = 1; i <= childrenCount; i++) {
                // Create a column for each input
                const col = document.createElement('div');
                col.className = 'col-md-4 col-sm-6 mb-2';

                const input = document.createElement('input');
                input.type = 'number';
                input.min = '0';
                input.max = '17';
                input.name = `child_ages[]`;
                input.className = 'form-control child-age-input';
                input.placeholder = `Child ${i} Age`;
                input.required = true;
                input.addEventListener('input', handleModification);

                col.appendChild(input);
                row.appendChild(col);
            }
            elements.childAgesContainer.appendChild(row);
        }
        // Always call handleModification to update price even if children are set to 0
        handleModification();
    }

    function handleModification() {
        if (!pricingRules) return;
        currentAdults = parseInt(elements.adultsInput.value, 10);
        currentChildren = parseInt(elements.childrenInput.value, 10);
        updateCalculations();
    }

    function updateCalculations() {
        if (!pricingRules) return;

        const childAges = Array.from(document.querySelectorAll('.child-age-input')).map(input => parseInt(input.value, 10)).filter(age => !isNaN(age));
        const infants = childAges.filter(age => age <= pricingRules.infant_max_age);
        const teens = childAges.filter(age => age > pricingRules.child_max_age && age <= pricingRules.teen_max_age);
        const children = childAges.filter(age => age > pricingRules.infant_max_age && age <= pricingRules.child_max_age);

        const totalAdultsAndTeens = currentAdults + teens.length;
        const totalGuestsForCapacity = totalAdultsAndTeens + children.length;

        const baseCapacity = pricingRules.max_guests || 1;
        const requiredUnits = Math.max(1, Math.ceil(totalGuestsForCapacity / baseCapacity));

        let totalCost = 0;
        let currencySymbol = options.currencySymbol || '€';

        for (const [seasonName, nightsInSeason] of Object.entries(seasonRateCounts)) {
            const seasonRates = pricingRules.rates[seasonName];
            if (!seasonRates) continue;

            const marketRateData = seasonRates['Global Rate']; // In portal, we only use Global Rate for simplicity
            if (!marketRateData || !marketRateData.rate) continue;

            let nightlyRate = parseFloat(marketRateData.rate);
            currencySymbol = marketRateData.currency_symbol || currencySymbol;
            const seasonBaseCost = nightlyRate * nightsInSeason * requiredUnits;

            const currentSeason = pricingRules.seasons.find(s => s.name === seasonName);
            let seasonSupplementCost = 0;
            if (pricingRules.pricing_model === 'SupplementPerGuest' && currentSeason) {
                const guestsCoveredByBaseRate = 2 * requiredUnits;
                const extraAdults = Math.max(0, totalAdultsAndTeens - guestsCoveredByBaseRate);
                const extraChildren = Math.max(0, (totalAdultsAndTeens + children.length) - guestsCoveredByBaseRate - extraAdults);
                seasonSupplementCost = (extraAdults * (pricingRules.adult_supplement || 0)) * nightsInSeason;
                if (currentSeason.apply_child_supplement == 1) {
                    seasonSupplementCost += (extraChildren * (pricingRules.child_supplement || 0)) * nightsInSeason;
                }
            } else if (pricingRules.pricing_model === 'CustomCapacity' && currentSeason) {
                const baseGuests = pricingRules.base_guest_number || 2;
                const totalPayingGuests = currentAdults + teens.length + children.length;
                const extraGuests = Math.max(0, totalPayingGuests - baseGuests);
                if (extraGuests > 0) {
                    seasonSupplementCost = extraGuests * (pricingRules.adult_supplement || 0) * nightsInSeason;
                }
            } else if (pricingRules.pricing_model === 'CapacityBased' && pricingRules.allow_extra_mattress && pricingRules.extra_mattress_fee > 0) {
                const mattressesNeeded = Math.max(0, totalGuestsForCapacity - (requiredUnits * baseCapacity));
                if (mattressesNeeded > 0) {
                    const mattressesUsed = Math.min(requiredUnits, mattressesNeeded);
                    seasonSupplementCost = mattressesUsed * pricingRules.extra_mattress_fee * nightsInSeason;
                }
            }

            totalCost += seasonBaseCost + seasonSupplementCost;
        }

        elements.unitDisplay.textContent = `${requiredUnits} Unit${requiredUnits > 1 ? 's' : ''}`;
        if (numberOfNights > 0) {
            const formattedPrice = totalCost.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            elements.priceDisplay.textContent = `New Est. Price: ${currencySymbol}${formattedPrice}`;
        } else {
            elements.priceDisplay.textContent = '';
        }

        const hasChanged = (currentAdults !== originalAdults || currentChildren !== originalChildren || currentStartDate !== originalStartDate || currentEndDate !== originalEndDate);
        if (hasChanged) {
            elements.summaryDisplay.innerHTML = `You are requesting changes to your booking. The new estimated price is shown above. Please review and click "Save Changes" to confirm.`;
            elements.summaryDisplay.style.display = 'block';
            elements.saveButton.style.display = 'inline-block';
        } else {
            elements.summaryDisplay.style.display = 'none';
            elements.saveButton.style.display = 'none';
        }
    }

    function saveChanges() {
        const childAges = Array.from(document.querySelectorAll('.child-age-input')).map(input => parseInt(input.value, 10)).filter(age => !isNaN(age));
        const formData = new FormData();
        formData.append('booking_id', options.booking_id);
        formData.append('adults', currentAdults);
        formData.append('children', currentChildren);
        formData.append('child_ages', childAges.join(', '));
        formData.append('start_date', currentStartDate);
        formData.append('end_date', currentEndDate);
        formData.append('price_estimate', elements.priceDisplay.textContent.replace('New Est. Price: ', ''));
        formData.append('unit_count', elements.unitDisplay.textContent.charAt(0));
        formData.append(options.token, 1);

        elements.saveButton.disabled = true;
        elements.saveButton.textContent = 'Saving...';

        fetch(options.urls.updateBooking, { method: 'POST', body: new URLSearchParams(formData) })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                    elements.saveButton.disabled = false;
                    elements.saveButton.textContent = 'Save Changes & Notify Admin';
                }
            })
            .catch(error => {
                alert('An error occurred while saving.');
                console.error('Save error:', error);
                elements.saveButton.disabled = false;
                elements.saveButton.textContent = 'Save Changes & Notify Admin';
            });
    }

    function logPageView() {
        if (!options || !options.booking_id) return;
        const formData = new FormData();
        formData.append('booking_id', options.booking_id);
        formData.append('action_type', 'Viewed Portal');
        formData.append('screen_size', `${window.screen.width}x${window.screen.height}`);
        formData.append(options.token, 1);

        fetch(options.urls.logActivity, {
            method: 'POST',
            body: new URLSearchParams(formData),
            keepalive: true // a rough equivalent for sendBeacon's purpose
        }).catch(error => console.error('Error logging page view:', error));
    }

    function convertDatesToLocalTime() {
        const dateElements = document.querySelectorAll('[data-utc-date]');
        dateElements.forEach(function(element) {
            const utcDate = element.getAttribute('data-utc-date');
            if (utcDate) {
                const date = new Date(utcDate);
                const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', hour12: false };
                element.textContent = date.toLocaleString(undefined, options);
            }
        });
    }

    init();
});
