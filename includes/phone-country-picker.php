<?php
$selectedPhoneCountry = $phoneCountries[$selectedCountry];
?>
<div class="phone-country-picker" data-country-picker>
    <input type="hidden" name="country_code" value="<?= escape($selectedCountry) ?>" data-country-value>
    <input type="hidden" name="country_dial" value="<?= escape($selectedPhoneCountry['dial']) ?>" data-country-dial-value>
    <button class="phone-country-button" type="button" aria-label="<?= escape('Phone country: ' . $selectedPhoneCountry['name'] . ' ' . $selectedPhoneCountry['dial']) ?>" aria-haspopup="listbox" aria-expanded="false" aria-controls="phone-country-menu" data-country-button>
        <img src="assets/images/flags/<?= escape(strtolower($selectedCountry)) ?>.svg" alt="" width="24" height="18" data-country-flag>
        <span class="phone-country-dial" data-country-dial><?= escape($selectedPhoneCountry['dial'] !== '' ? $selectedPhoneCountry['dial'] : 'N/A') ?></span>
        <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
    </button>
    <div class="phone-country-menu" id="phone-country-menu" data-country-menu hidden>
        <label class="phone-country-search-label" for="country-search">Search countries</label>
        <input class="phone-country-search" id="country-search" type="search" autocomplete="off" aria-controls="phone-country-options" placeholder="Type a country name" data-country-search>
        <div class="phone-country-options" id="phone-country-options" role="listbox" aria-label="Phone country" data-country-options>
            <?php foreach ($phoneCountries as $code => $country): ?>
                <button class="phone-country-option" type="button" role="option" aria-selected="<?= $selectedCountry === $code ? 'true' : 'false' ?>" data-country-option data-country-code="<?= escape($code) ?>" data-country-name="<?= escape($country['name']) ?>" data-country-dial="<?= escape($country['dial']) ?>" data-country-flag-src="assets/images/flags/<?= escape(strtolower($code)) ?>.svg">
                    <img src="assets/images/flags/<?= escape(strtolower($code)) ?>.svg" alt="" width="24" height="18" loading="lazy">
                    <span><?= escape($country['name']) ?></span>
                    <span class="phone-country-option-dial"><?= escape($country['dial'] !== '' ? $country['dial'] : 'No calling code') ?></span>
                </button>
            <?php endforeach; ?>
        </div>
        <p class="phone-country-no-results" data-country-empty hidden>No countries found.</p>
    </div>
</div>
