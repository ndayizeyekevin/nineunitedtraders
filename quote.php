<?php
require_once __DIR__ . '/includes/site.php';
[$formValues, $formErrors, $formStatus] = process_inquiry('quote');
$serviceOptions = [
    'Cargo transportation',
    'Cement and commodity importation',
    'Warehousing and stores',
    'Garage services',
    'Clearing agency',
    'Training center',
    'Other',
];
$selectedService = $formValues['service'] ?? (isset($_GET['service']) && is_string($_GET['service']) ? $_GET['service'] : '');
$pageTitle = 'Request a Quote';
$pageDescription = 'Request a logistics, cargo transportation, importation, warehousing or clearing quote from Nine United Traders Ltd.';
$activePage = '';
include __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
    <div class="container page-hero-inner">
        <div><div class="breadcrumb"><a href="index.php">Home</a> / Request a quote</div><span class="eyebrow">Tell us what you need</span><h1>Request a service quote</h1><p>Share a few details about your shipment or service requirement. The more you can tell us, the better we can respond.</p></div>
    </div>
</section>
<section class="page-content">
    <div class="container quote-layout">
        <aside class="quote-aside">
            <span class="eyebrow">A useful starting point</span>
            <h2>Help us understand your requirement</h2>
            <p>Include the service, route, cargo and timing you have in mind. We’ll follow up to discuss any details needed for a quote.</p>
            <ul class="check-list">
                <li>Choose the service you need</li>
                <li>Tell us the origin and destination</li>
                <li>Describe the cargo or commodity</li>
                <li>Provide a phone number and email</li>
            </ul>
            <p class="privacy-note">Your contact and shipment details are used to respond to this request. Prefer to speak directly? Call <a class="text-link" href="tel:+250788302388">+250 788 302 388</a>.</p>
        </aside>
        <section class="form-card">
            <div class="form-heading">
                <span class="form-heading-icon" aria-hidden="true"><i class="fa-solid fa-clipboard-list"></i></span>
                <div><span class="form-kicker">Your requirements</span><h2>Quote details</h2></div>
            </div>
            <p class="form-intro">Fields marked * are required. We’ll use these details to prepare a useful response.</p>
            <?php if ($formErrors !== []): ?><div class="form-alert form-error" role="alert"><p><strong>Please check the following:</strong></p><ul><?php foreach ($formErrors as $error): ?><li><?= escape($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
            <?php if ($formStatus !== ''): ?><div class="form-alert form-success" role="status"><p><?= escape($formStatus) ?></p></div><?php endif; ?>
            <form method="post" action="quote.php">
                <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                <div class="honeypot" aria-hidden="true"><label for="website">Leave this field empty</label><input id="website" name="website" type="text" tabindex="-1" autocomplete="off"></div>
                <div class="form-grid">
                    <div class="field field-full"><label for="service">Service needed *</label><select class="form-select" id="service" name="service" required><option value="">Choose a service</option><?php foreach ($serviceOptions as $option): ?><option value="<?= escape($option) ?>"<?= $selectedService === $option ? ' selected' : '' ?>><?= escape($option) ?></option><?php endforeach; ?></select></div>
                    <div class="field"><label for="name">Your name *</label><input class="form-control" id="name" name="name" type="text" maxlength="100" autocomplete="name" required value="<?= escape($formValues['name'] ?? '') ?>"></div>
                    <div class="field"><label for="phone">Phone number *</label><input class="form-control" id="phone" name="phone" type="tel" maxlength="40" autocomplete="tel" required placeholder="+250 ..." value="<?= escape($formValues['phone'] ?? '') ?>"></div>
                    <div class="field field-full"><label for="email">Email address *</label><input class="form-control" id="email" name="email" type="email" maxlength="254" autocomplete="email" required placeholder="you@example.com" value="<?= escape($formValues['email'] ?? '') ?>"></div>
                    <div class="field"><label for="origin">Origin *</label><input class="form-control" id="origin" name="origin" type="text" maxlength="120" required placeholder="Town, city or country" value="<?= escape($formValues['origin'] ?? '') ?>"></div>
                    <div class="field"><label for="destination">Destination *</label><input class="form-control" id="destination" name="destination" type="text" maxlength="120" required placeholder="Town, city or country" value="<?= escape($formValues['destination'] ?? '') ?>"></div>
                    <div class="field"><label for="cargo">Cargo or commodity</label><input class="form-control" id="cargo" name="cargo" type="text" maxlength="180" placeholder="What needs to be moved or handled?" value="<?= escape($formValues['cargo'] ?? '') ?>"></div>
                    <div class="field"><label for="date">Requested date</label><input class="form-control" id="date" name="date" type="date" value="<?= escape($formValues['date'] ?? '') ?>"></div>
                    <div class="field field-full"><label for="notes">Additional details</label><textarea class="form-control" id="notes" name="notes" maxlength="2500" placeholder="Quantity, timing, or anything else we should know"><?= escape($formValues['notes'] ?? '') ?></textarea></div>
                </div>
                <button class="button form-submit" type="submit">Send quote request <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
            </form>
        </section>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
