document.addEventListener('DOMContentLoaded', function () {
    const options = Joomla.getOptions('mod_bookingform');
    if (!options || !options.pricingRules) { return; }

    const elements = {
        startingFromPrice: document.getElementById('starting-from-price'),
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
        couponCodeInput: document.getElementById('coupon-code'),
        getQuoteButton: document.getElementById('get-quote-button'),
        bookingStep2: document.getElementById('booking-step-2'),
        priceSummaryContainer: document.getElementById('price-summary-container'),
        dateRangeError: document.getElementById('date-range-error'),
        childAgesError: document.getElementById('child-ages-error'),
        countryError: document.getElementById('country-error'),
        propertySuggestionAlert: document.getElementById('property-suggestion-alert'),
    };

    function displayStartingPrice(countryName = null) {
        const rules = options.pricingRules;
        if (!rules.rates || Object.keys(rules.rates).length === 0) return;

        const marketName = (countryName && rules.active_markets && rules.active_markets.includes(countryName)) ? countryName : 'Global Rate';

        const marketRates = Object.values(rules.rates).map(seasonRates => {
            const marketData = seasonRates[marketName] || seasonRates['Global Rate'];
            return marketData ? marketData.rate : null;
        }).filter(rate => rate && rate > 0);

        if (marketRates.length === 0) return;

        const lowestMarketRate = Math.min(...marketRates);
        const firstSeasonName = Object.keys(rules.rates)[0];
        const currencySymbol = (rules.rates[firstSeasonName][marketName]?.currency || rules.rates[firstSeasonName]['Global Rate']?.currency) || '€';

        if (elements.startingFromPrice && lowestMarketRate > 0 && isFinite(lowestMarketRate)) {
            elements.startingFromPrice.textContent = `From ${currencySymbol}${Math.ceil(lowestMarketRate)} / night`;
        }
    }

    displayStartingPrice(); // Initial display with Global Rate

    if (elements.couponCodeInput && (!options.pricingRules.coupon_codes || options.pricingRules.coupon_codes.length === 0)) {
        elements.couponCodeInput.closest('.row').style.display = 'none';
    }

    let numberOfNights = 0;
    let seasonRateCounts = {};
    let iti = null;
    let couponDiscount = { percent: 0, message: '' };
    let isBookingPossible = true;

    if (elements.telephoneInput) {
        iti = window.intlTelInput(elements.telephoneInput, {
            initialCountry: "auto",
            geoIpLookup: (callback) => {
                fetch("https://ipapi.co/json")
                    .then(res => res.json())
                    .then(data => {
                        callback(data.country_code);
                        // Directly update the price after the lookup
                        displayStartingPrice(data.country_name);
                    })
                    .catch(() => {
                        callback("mu");
                        displayStartingPrice("Mauritius"); // Fallback
                    });
            },
            separateDialCode: true,
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/utils.js",
        });

        // Add a listener for manual changes as well
        elements.telephoneInput.addEventListener('countrychange', function() {
            const selectedCountry = iti.getSelectedCountryData();
            if (selectedCountry.name) {
                displayStartingPrice(selectedCountry.name);
            }
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
                            const seasonsInBooking = options.pricingRules.seasons.filter(s => seasonRateCounts[s.name] && s.min_stay > 0);
                            if (seasonsInBooking.length > 0) {
                                minStay = Math.min(...seasonsInBooking.map(s => s.min_stay));
                                const minStaySeasonObject = seasonsInBooking.find(s => s.min_stay == minStay);
                                if (minStaySeasonObject) {
                                    minStaySeason = minStaySeasonObject.name;
                                }
                            }
                        }
                        if (elements.minStayAlert) {
                            if (numberOfNights > 0 && numberOfNights < minStay) {
                                elements.minStayAlert.textContent = `A minimum stay of ${minStay} nights is required for the selected period (${minStaySeason} season).`;
                                elements.minStayAlert.style.display = 'block';
                            } else { elements.minStayAlert.style.display = 'none'; }
                        }
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
                col.appendChild(input);
                elements.childAgesContainer.appendChild(col);
            }
        } else {
            elements.childAgesLabelRow.style.display = 'none';
        }
        updateChildAgeNotification();
    }

    function updateChildAgeNotification() {
        const rules = options.pricingRules;
        if (!rules || !elements.childAgeNotificationArea) return;
        const childAges = Array.from(document.querySelectorAll('.child-age-input')).map(input => parseInt(input.value, 10)).filter(age => !isNaN(age));
        let messages = [];
        const infants = childAges.filter(age => age <= rules.infant_max_age);
        const teens = childAges.filter(age => age > rules.child_max_age && age <= rules.teen_max_age);
        const children = childAges.filter(age => age > rules.infant_max_age && age <= rules.child_max_age);

        if (infants.length > 0) {
            messages.push(`Free baby cot available for child up to age ${rules.infant_max_age}.`);
        }
        if (teens.length > 0) {
            messages.push(`Child aged ${rules.child_max_age + 1}-${rules.teen_max_age} are considered guest adults for pricing.`);
        }

        const selectedSeasonNames = Object.keys(seasonRateCounts);
        if (children.length > 0 && selectedSeasonNames.length > 0) {
            const seasonsInBooking = rules.seasons.filter(s => selectedSeasonNames.includes(s.name));
            const payableSeasons = seasonsInBooking.filter(s => s.apply_child_supplement == 1).map(s => s.name);
            const freeSeasons = seasonsInBooking.filter(s => s.apply_child_supplement != 1).map(s => s.name);
            let supplementMsg = '';
            if (payableSeasons.length > 0) {
                supplementMsg = 'A child supplement is payable for this season(s).';
            } else if (freeSeasons.length > 0) {
                supplementMsg = 'Children stay free of charge during this season(s).';
            }
            if (supplementMsg) {
                messages.push(supplementMsg);
            }
        } else if (children.length > 0) {
            messages.push('For children, a supplement may apply depending on the seasons selected.');
        }

        if (messages.length > 0) {
            elements.childAgeNotificationArea.innerHTML = messages.join('<br>');
            elements.childAgeNotificationArea.style.display = 'block';
        } else {
            elements.childAgeNotificationArea.style.display = 'none';
        }
    }

    function validateCouponCode(callback) {
        const couponCode = elements.couponCodeInput.value.trim();
        if (!couponCode) {
            couponDiscount = { percent: 0, message: '' };
            if (callback) callback();
            return;
        }
        const url = options.submissionUrl.replace('task=submitBooking', 'task=validateCoupon') + `&${Joomla.getFormToken()}=1`;
        const formData = new FormData();
        formData.append('coupon_code', couponCode);
        formData.append('article_id', options.articleId);
        fetch(url, {
            method: 'POST',
            body: new URLSearchParams(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                couponDiscount = { percent: data.discount, message: data.message };
            } else {
                couponDiscount = { percent: 0, message: data.message || 'Invalid coupon.' };
                alert(couponDiscount.message);
            }
            if (callback) callback();
        })
        .catch(error => {
            console.error('Coupon validation error:', error);
            couponDiscount = { percent: 0, message: 'Error validating coupon.' };
            if (callback) callback();
        });
    }

    function updateCalculations() {
        const adults = parseInt(elements.guestSelect.value, 10);
        const rules = options.pricingRules;
        if (!rules) return;

        elements.propertySuggestionAlert.style.display = 'none';
        const alternativesContainer = document.getElementById('alternative-properties-container');
        if(alternativesContainer) alternativesContainer.innerHTML = '';
        elements.unitCountDisplay.style.display = 'block';
        elements.priceSummaryContainer.style.display = 'block';

        updateChildAgeNotification();
        const childAges = Array.from(document.querySelectorAll('.child-age-input')).map(input => parseInt(input.value, 10)).filter(age => !isNaN(age));
        const infants = childAges.filter(age => age <= rules.infant_max_age);
        const teens = childAges.filter(age => age > rules.child_max_age && age <= rules.teen_max_age);
        const children = childAges.filter(age => age > rules.infant_max_age && age <= rules.child_max_age);
        const totalAdultsAndTeens = adults + teens.length;
        let totalGuestsForCapacity = adults + teens.length + children.length;
        let requiredUnits = 1;
        const baseCapacity = options.totalAccommodationGuests || 1;
        const availableUnits = rules.number_of_units || 1;
        isBookingPossible = true; // Reset flag on each calculation

        if (totalGuestsForCapacity > baseCapacity) {
            let suitableAlternatives = (rules.alternative_properties || []).filter(p => parseInt(p.max_guests, 10) >= totalGuestsForCapacity);

            if (suitableAlternatives.length > 0) {
                suitableAlternatives.sort((a, b) => parseInt(a.max_guests, 10) - parseInt(b.max_guests, 10));

                // Get only the single best-fit alternative
                const bestFitAlternative = suitableAlternatives[0];

                const altHtml = `<div class="col-12 mb-2"><div class="card"><a href="${bestFitAlternative.url}" target="_blank">${bestFitAlternative.intro_image ? `<img src="${options.rootUrl}${bestFitAlternative.intro_image}" class="card-img-top" alt="${bestFitAlternative.title}">` : ''}<div class="card-body"><h6 class="card-title">${bestFitAlternative.title}<small class="text-muted">(Max Guests: ${bestFitAlternative.max_guests})</small></h6></div></a></div></div>`;
                if (alternativesContainer) alternativesContainer.innerHTML = altHtml; // Use '=' to show only one

                elements.propertySuggestionAlert.style.display = 'block';
            }

            requiredUnits = Math.ceil(totalGuestsForCapacity / baseCapacity);
            if (requiredUnits > availableUnits) {
                elements.unitCountDisplay.textContent = `This property has a limit of ${availableUnits} unit(s), but your group requires ${requiredUnits}. Please consider an alternative property.`;
                isBookingPossible = false;
            } else if (totalGuestsForCapacity > baseCapacity) {
                elements.unitCountDisplay.textContent = 'Guest number exceeds the maximum capacity for this property.';
                isBookingPossible = false;
            }
        }

        const selectedCountry = elements.countryResidenceSelect.value;
        const marketName = (rules.active_markets && rules.active_markets.includes(selectedCountry)) ? selectedCountry : 'Global Rate';

        let currencySymbol = '€';
        let totalBaseCost = 0;
        let totalSupplementCost = 0;

        for (const [seasonName, nightsInSeason] of Object.entries(seasonRateCounts)) {
            const seasonRates = rules.rates[seasonName];
            if (!seasonRates) continue;

            const marketRateData = seasonRates[marketName] || seasonRates['Global Rate'];
            if (!marketRateData || !marketRateData.rate) continue;

            const nightlyRate = parseFloat(marketRateData.rate);
            currencySymbol = marketRateData.currency || currencySymbol;
            const seasonBaseCost = nightlyRate * nightsInSeason * requiredUnits;
            totalBaseCost += seasonBaseCost;

            const currentSeason = rules.seasons.find(s => s.name === seasonName);
            if (rules.pricing_model === 'SupplementPerGuest' && currentSeason) {
                const guestsCoveredByBaseRate = 2 * requiredUnits;
                const extraAdults = Math.max(0, totalAdultsAndTeens - guestsCoveredByBaseRate);
                const extraChildren = Math.max(0, (totalAdultsAndTeens + children.length) - guestsCoveredByBaseRate - extraAdults);
                let seasonSupplementCost = (extraAdults * (rules.adult_supplement || 0)) * nightsInSeason;
                if (currentSeason.apply_child_supplement == 1) {
                    seasonSupplementCost += (extraChildren * (rules.child_supplement || 0)) * nightsInSeason;
                }
                totalSupplementCost += seasonSupplementCost;
            }
        }

        let totalCost = totalBaseCost + totalSupplementCost;
        let discountPercent = 0;
        let discountNote = '';

        if (couponDiscount.percent > 0) {
            discountPercent = couponDiscount.percent;
            discountNote = couponDiscount.message;
        } else if (rules.country_discounts && selectedCountry) {
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
        elements.unitCountDisplay.textContent = requiredUnits > 1 ? `${requiredUnits} Units` : '1 Unit';

        if (numberOfNights > 0) {
            const formattedPrice = totalCost.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            elements.priceDisplay.textContent = `Est. Price: ${currencySymbol}${formattedPrice}`;
            elements.priceInput.value = `${currencySymbol}${formattedPrice}`;
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

    if (elements.getQuoteButton) {
        elements.getQuoteButton.addEventListener('click', function() {
            // Clear previous validation errors
            ['dateRangeError', 'childAgesError', 'countryError'].forEach(err => {
                if (elements[err]) {
                    elements[err].style.display = 'none';
                    elements[err].textContent = '';
                }
            });
            elements.datePickerEl.classList.remove('is-invalid');
            elements.countryResidenceSelect.classList.remove('is-invalid');
            elements.childAgesContainer.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

            let isValid = true;

            // Validate dates
            let minStay = 0;
            let minStaySeason = '';
            if (options.pricingRules && Array.isArray(options.pricingRules.seasons)) {
                const seasonsInBooking = options.pricingRules.seasons.filter(s => seasonRateCounts[s.name] && s.min_stay > 0);
                if (seasonsInBooking.length > 0) {
                    minStay = Math.max(...seasonsInBooking.map(s => s.min_stay)); // Use Math.max to enforce the strictest policy
                    const minStaySeasonObject = seasonsInBooking.find(s => s.min_stay == minStay);
                    if (minStaySeasonObject) {
                        minStaySeason = minStaySeasonObject.name;
                    }
                }
            }

            if (numberOfNights === 0) {
                elements.dateRangeError.textContent = 'Please select your check-in and check-out dates.';
                elements.dateRangeError.style.display = 'block';
                elements.datePickerEl.classList.add('is-invalid');
                isValid = false;
            } else if (numberOfNights < minStay) {
                elements.dateRangeError.textContent = `A minimum stay of ${minStay} nights is required for the selected period (${minStaySeason} season).`;
                elements.dateRangeError.style.display = 'block';
                elements.datePickerEl.classList.add('is-invalid');
                isValid = false;
            }

            // Validate country
            if (!elements.countryResidenceSelect.value) {
                elements.countryError.textContent = 'Please select your country of residence.';
                elements.countryError.style.display = 'block';
                elements.countryResidenceSelect.classList.add('is-invalid');
                isValid = false;
            }

            // Validate child ages
            const childrenCount = parseInt(elements.childrenSelect.value, 10);
            const childAgeInputs = elements.childAgesContainer.querySelectorAll('.child-age-input');
            if (childrenCount > 0) {
                let allAgesEntered = true;
                childAgeInputs.forEach(input => {
                    if (input.value === '') {
                        input.classList.add('is-invalid');
                        allAgesEntered = false;
                    }
                });
                if (!allAgesEntered) {
                    elements.childAgesError.textContent = 'Please enter the age for all children.';
                    elements.childAgesError.style.display = 'block';
                    isValid = false;
                }
            }

            if (!isValid) {
                return;
            }

            // If valid, proceed with calculation
            validateCouponCode(function() {
                updateCalculations();
                elements.priceSummaryContainer.style.display = 'block';
                elements.startingFromPrice.style.display = 'none';
                elements.getQuoteButton.textContent = 'Recalculate Price';

                // Only show the final booking step if the booking is possible
                if (isBookingPossible) {
                    elements.bookingStep2.style.display = 'block';
                } else {
                    elements.bookingStep2.style.display = 'none';
                }
            });
        });
    }

    elements.childrenSelect.addEventListener('change', updateChildAgeInputs);

    if (elements.childAgesContainer) {
        elements.childAgesContainer.addEventListener('input', function(e) {
            if (e.target && e.target.classList.contains('child-age-input')) {
                updateChildAgeNotification();
            }
        });
    }

    updateChildAgeInputs();
});