document.addEventListener('DOMContentLoaded', function () {
    const options = Joomla.getOptions('mod_bookingform');
    if (!options || !options.pricingRules) { return; }

    const elements = {
        bookingForm: document.getElementById('bookingForm'),
        guestSelect: document.getElementById('guest-count'),
        childrenSelect: document.getElementById('children-count'),
        childAgesContainer: document.getElementById('child-ages-container'),
        childAgesLabelRow: document.getElementById('child-ages-label-row'),
        childAgeNotificationArea: document.getElementById('child-age-notification-area'),
        unitCountInput: document.getElementById('unit-count-input'),
        unitCountDisplay: document.getElementById('unit-count-display'),
        priceDisplay: document.getElementById('price-estimate-display'),
        priceInput: document.getElementById('price-estimate-input'),
        priceDisclaimer: document.getElementById('price-disclaimer'),
        startDateInput: document.getElementById('start-date'),
        endDateInput: document.getElementById('end-date'),
        datePickerEl: document.getElementById('date-range-picker'),
        nightsDisplay: document.getElementById('nights-count-display'),
        countryResidenceSelect: document.getElementById('country-residence'),
        telephoneInput: document.getElementById('telephone'),
        submitButton: document.getElementById('submit-button'),
        minStayAlert: document.getElementById('min-stay-alert'),
        discountAlert: document.getElementById('discount-applied-alert'),
        discountNoteInput: document.getElementById('discount-note-input'),
    };

    let numberOfNights = 0;
    let seasonRateCounts = {};
    let iti = null;

    if (elements.telephoneInput) {
        iti = window.intlTelInput(elements.telephoneInput, {
            initialCountry: "auto",
            geoIpLookup: (callback) => { fetch("https://ipapi.co/json").then(res => res.json()).then(data => callback(data.country_code)).catch(() => callback("mu")); },
            separateDialCode: true,
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/utils.js",
        });
    }

    function getSeasonForDate(date) {
        const rules = options.pricingRules;
        if (!rules || !Array.isArray(rules.seasons)) return null;

        const year = date.getFullYear();
        const month = (date.getMonth() + 1).toString().padStart(2, '0');
        const day = date.getDate().toString().padStart(2, '0');
        const dateStr = `${year}-${month}-${day}`;

        for (const season of rules.seasons) {
            if (dateStr >= season.start_date && dateStr <= season.end_date) return season;
        }
        return null;
    }

    if (typeof Litepicker !== 'undefined') {
        new Litepicker({
            element: elements.datePickerEl,
            singleMode: false,
            minDate: new Date(),
            format: 'DD MMM, YYYY',
            tooltipText: { 'one': 'day', 'other': 'days' },
            setup: (picker) => {
                picker.on('selected', (date1, date2) => {
                    if (date1 && date2) {
                        elements.startDateInput.value = date1.format('YYYY-MM-DD');
                        elements.endDateInput.value = date2.format('YYYY-MM-DD');

                        seasonRateCounts = {};
                        let currentDate = date1.toJSDate();
                        while(currentDate < date2.toJSDate()){
                            const season = getSeasonForDate(currentDate);
                            if (season) { seasonRateCounts[season.name] = (seasonRateCounts[season.name] || 0) + 1; }
                            currentDate.setDate(currentDate.getDate() + 1);
                        }
                        numberOfNights = Object.values(seasonRateCounts).reduce((a, b) => a + b, 0);

                        let minStay = 0;
                        let minStaySeason = '';
                        if (options.pricingRules && Array.isArray(options.pricingRules.seasons)) {
                            for (const season of options.pricingRules.seasons) {
                                if (seasonRateCounts[season.name] && season.min_stay > minStay) { minStay = season.min_stay; minStaySeason = season.name; }
                            }
                        }
                        if (elements.minStayAlert) {
                            if (numberOfNights > 0 && numberOfNights < minStay) {
                                elements.minStayAlert.textContent = `A minimum stay of ${minStay} nights is required for the selected period (${minStaySeason} season).`;
                                elements.minStayAlert.style.display = 'block';
                            } else { elements.minStayAlert.style.display = 'none'; }
                        }
                        updateCalculations();
                    }
                });
            }
        });
    }

    function updateChildAgeInputs() {
        const childrenCount = parseInt(elements.childrenSelect.value, 10);
        elements.childAgesContainer.innerHTML = '';

        if (childrenCount > 0) {
            elements.childAgesLabelRow.style.display = 'flex';

            for (let i = 1; i <= childrenCount; i++) {
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

                input.addEventListener('input', updateCalculations);

                col.appendChild(input);
                elements.childAgesContainer.appendChild(col);
            }
        } else {
            elements.childAgesLabelRow.style.display = 'none';
        }
        updateCalculations();
    }

    function updateCalculations() {
        const adults = parseInt(elements.guestSelect.value, 10);
        const rules = options.pricingRules;
        if (!rules) return;

        const childAges = Array.from(document.querySelectorAll('.child-age-input')).map(input => parseInt(input.value, 10)).filter(age => !isNaN(age));

        if (elements.childAgeNotificationArea) {
            let messages = [];
            const hasInfant = childAges.some(age => age <= rules.infant_max_age);
            const hasOlderChild = childAges.some(age => age > rules.infant_max_age);

            if (hasInfant) {
                messages.push(`Free baby cot provided (age ${rules.infant_max_age} or less).`);
            }
            if (hasOlderChild) {
                messages.push(`Children older than ${rules.infant_max_age} are counted as guests.`);
            }

            if (messages.length > 0) {
                elements.childAgeNotificationArea.textContent = messages.join(' ');
                elements.childAgeNotificationArea.style.display = 'block';
            } else {
                elements.childAgeNotificationArea.style.display = 'none';
            }
        }

        let totalGuestsForCapacity = adults;
        let mattressCost = 0;
        let requiredUnits = 1;
        const baseCapacity = options.totalAccommodationGuests || 2;

        if (rules.pricing_model === 'CapacityBased') {
            let childrenCounting = childAges.filter(age => age > rules.free_with_parents_age).length;
            totalGuestsForCapacity += childrenCounting;
            const extraGuests = totalGuestsForCapacity - baseCapacity;
            if (extraGuests === 1) { mattressCost = (rules.extra_mattress_fee || 0) * numberOfNights; } 
            else if (extraGuests > 1) { requiredUnits = Math.ceil(totalGuestsForCapacity / baseCapacity); }
        } else {
             let infantsNotCounting = childAges.filter(age => age <= rules.infant_max_age).length;
             totalGuestsForCapacity += (childAges.length - infantsNotCounting);
             if (baseCapacity > 0) { requiredUnits = Math.max(1, Math.ceil(totalGuestsForCapacity / baseCapacity)); }
        }

        let roomCost = 0;
        for (const [seasonName, nightsInSeason] of Object.entries(seasonRateCounts)) {
            let nightlyRate = rules.rates[seasonName] || 0;
            const currentSeason = rules.seasons.find(s => s.name === seasonName);

            if (rules.pricing_model === 'SupplementPerGuest' && currentSeason) {
                let chargeableAdults = adults;
                let chargeableChildren = 0;
                childAges.forEach(age => {
                    if (age > rules.child_max_age) chargeableAdults++;
                    else if (age > rules.infant_max_age && currentSeason.apply_child_supplement === "1") { chargeableChildren++; }
                });
                const extraAdults = Math.max(0, chargeableAdults - 2);
                nightlyRate += (extraAdults * (rules.adult_supplement || 0)) + (chargeableChildren * (rules.child_supplement || 0));
            }
            roomCost += nightlyRate * nightsInSeason;
        }

        let totalCost = (roomCost * requiredUnits) + mattressCost;

        const selectedCountry = elements.countryResidenceSelect.value;
        let discountPercent = 0;
        let discountNote = '';
        if (rules.country_discounts && selectedCountry) {
            const countryRule = rules.country_discounts.find(d => d.country === selectedCountry);
            if (countryRule && countryRule.discount_percent) {
                discountPercent = parseFloat(countryRule.discount_percent);
                discountNote = countryRule.note || `A ${discountPercent}% discount has been applied!`;
            }
        }

        if (discountPercent > 0) {
            totalCost *= (1 - (discountPercent / 100));
        }

        if (elements.discountAlert) {
            if (discountPercent > 0 && totalCost > 0) {
                elements.discountAlert.textContent = discountNote;
                elements.discountAlert.style.display = 'block';
                elements.discountNoteInput.value = discountNote;
            } else {
                elements.discountAlert.style.display = 'none';
                elements.discountNoteInput.value = '';
            }
        }

        elements.unitCountInput.value = requiredUnits;
        elements.unitCountDisplay.textContent = `${requiredUnits} Unit${requiredUnits > 1 ? 's' : ''}`;

        if (numberOfNights > 0) {
            const formattedPrice = totalCost.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            elements.priceDisplay.textContent = `Est. Price: ${options.currencySymbol}${formattedPrice}`;
            elements.priceInput.value = `${options.currencySymbol}${formattedPrice}`;
            elements.nightsDisplay.textContent = `(${numberOfNights} ${numberOfNights > 1 ? 'Nights' : 'Night'})`;
            elements.priceDisclaimer.style.display = 'block';
        } else {
            elements.priceDisplay.textContent = 'Est. Price: -';
            elements.priceInput.value = 'N/A';
            elements.nightsDisplay.textContent = '';
            elements.priceDisclaimer.style.display = 'none';
        }
    }

    elements.bookingForm.addEventListener('submit', function (event) {
        event.preventDefault(); 
        const formData = new FormData(elements.bookingForm);
        const spinner = elements.submitButton.querySelector('.spinner-border');
        const buttonText = elements.submitButton.querySelector('.button-text');
        elements.submitButton.disabled = true;
        if (buttonText) buttonText.textContent = 'Sending...';
        if (spinner) spinner.style.display = 'inline-block';

        fetch(options.submissionUrl, { method: 'POST', body: new URLSearchParams(formData) })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('booking-form-wrapper').style.display = 'none';
                const thankYouEl = document.getElementById('thank-you-message');
                thankYouEl.style.display = 'block';
                document.getElementById('booking-ref-display').textContent = data.bookingRef;
            } else { alert('An error occurred: ' + (data.message || 'Please try again.')); }
        })
        .catch(error => { console.error('Submission Error:', error); alert('A network error occurred.'); })
        .finally(() => {
            elements.submitButton.disabled = false;
            if(buttonText) buttonText.textContent = 'Send Booking Request';
            if (spinner) spinner.style.display = 'none';
        });
    });

    if (elements.countryResidenceSelect) {
        elements.countryResidenceSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const isoCode = selectedOption.getAttribute('data-iso-code');
            if (iti && isoCode) { iti.setCountry(isoCode.toLowerCase()); }
            updateCalculations();
        });
    }

    elements.guestSelect.addEventListener('change', updateCalculations);
    elements.childrenSelect.addEventListener('change', updateChildAgeInputs);

    updateChildAgeInputs();
    setTimeout(updateCalculations, 500);
});