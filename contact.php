<?php
require_once __DIR__ . '/includes/site.php';
[$formValues, $formErrors, $formStatus] = process_inquiry('contact');
$pageTitle = 'Contact';
$pageDescription = 'Contact Nine United Traders Ltd in Kigali by phone, email or inquiry form.';
$activePage = 'contact';
$phoneCountries = phone_countries();
$selectedCountry = $formValues['country_code'] ?? 'RW';
if (!isset($phoneCountries[$selectedCountry])) {
    $selectedCountry = 'RW';
}
include __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
    <div class="container page-hero-inner">
        <div><div class="breadcrumb"><a href="index.php">Home</a> / Contact</div><span class="eyebrow">We’re ready to listen</span><h1>Let’s talk about what you need</h1><p>Contact us about our services, a partnership, or any question you have for Nine United Traders.</p></div>
    </div>
</section>
<section class="page-content page-content-dark">
    <div class="container contact-layout">
        <aside class="contact-card">
            <h2>Get in touch</h2>
            <p>Reach our Kigali office using the details below.</p>
            <div class="contact-method"><span class="contact-method-icon" aria-hidden="true"><i class="fa-solid fa-location-dot"></i></span><div><strong>Visit us</strong><span>CHIC Building, Kigali City Center<br>P.O. Box 5855, Kigali, Rwanda</span><a class="map-link" href="https://maps.google.com/?q=CHIC+Building+Kigali+Rwanda" target="_blank" rel="noopener noreferrer">Get directions ↗</a></div></div>
            <div class="contact-method"><span class="contact-method-icon" aria-hidden="true"><i class="fa-solid fa-phone"></i></span><div><strong>Call us</strong><a href="tel:+250788302388">+250 788 302 388</a><br><a href="tel:+250788304751">+250 788 304 751</a><br><a href="tel:+250788319013">+250 788 319 013</a></div></div>
            <div class="contact-method"><span class="contact-method-icon" aria-hidden="true"><i class="fa-solid fa-envelope"></i></span><div><strong>Email</strong><a href="mailto:nineunited2050@gmail.com">nineunited2050@gmail.com</a></div></div>
        </aside>
        <section class="form-card">
            <div class="form-heading">
                <span class="form-heading-icon" aria-hidden="true"><i class="fa-solid fa-paper-plane"></i></span>
                <div><span class="form-kicker">Contact our team</span><h2>Send an inquiry</h2></div>
            </div>
            <p class="form-intro">Leave your details and a message. We’ll get back to you using your preferred contact method.</p>
            <?php if ($formErrors !== []): ?><div class="form-alert form-error" role="alert"><p><strong>Please check the following:</strong></p><ul><?php foreach ($formErrors as $error): ?><li><?= escape($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
            <?php if ($formStatus !== ''): ?><div class="form-alert form-success" role="status"><p><?= escape($formStatus) ?></p></div><?php endif; ?>
            <form method="post" action="contact.php">
                <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                <div class="honeypot" aria-hidden="true"><label for="website">Leave this field empty</label><input id="website" name="website" type="text" tabindex="-1" autocomplete="off"></div>
                <div class="form-grid">
                    <div class="field field-full"><label for="name">Your name *</label><input class="form-control" placeholder="John Doe" id="name" name="name" type="text" maxlength="100" autocomplete="name" required value="<?= escape($formValues['name'] ?? '') ?>"></div>
                    <div class="field field-full"><label for="phone">Phone</label><div class="phone-input-group"><?php include __DIR__ . '/includes/phone-country-picker.php'; ?><input class="form-control phone-number" id="phone" name="phone" type="tel" maxlength="40" autocomplete="tel-national" placeholder="780123456" value="<?= escape($formValues['phone'] ?? '') ?>"></div></div>
                    <div class="field field-full"><label for="email">Email</label><input class="form-control" id="email" name="email" type="email" maxlength="254" autocomplete="email" placeholder="you@example.com" value="<?= escape($formValues['email'] ?? '') ?>"></div>
                    <div class="field field-full"><label for="message">How can we help? *</label><textarea class="form-control" id="message" name="message" maxlength="5000" required placeholder="Tell us a little about your inquiry"><?= escape($formValues['message'] ?? '') ?></textarea><p class="field-help">Please provide a phone number or email address so we can reply.</p></div>
                </div>
                <button class="button form-submit" type="submit">Send inquiry <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
            </form>
        </section>
    </div>
    <div class="container contact-map-section">
        <div class="contact-map-heading">
            <span class="contact-map-icon" aria-hidden="true"><i class="fa-solid fa-map-location-dot"></i></span>
            <div>
                <span class="form-kicker">Find our office</span>
                <h2>CHIC Building, Kigali City Center</h2>
                <p>Kigali City Center, Kigali, Rwanda</p>
            </div>
            <a class="button button-small contact-map-directions" href="https://maps.google.com/?q=CHIC+Building%2C+Kigali+City+Center%2C+Kigali%2C+Rwanda" target="_blank" rel="noopener noreferrer">Get directions <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
        </div>
        <div class="contact-map-frame">
            <iframe
                title="Map showing CHIC Building in Kigali City Center, Kigali, Rwanda"
                src="https://maps.google.com/maps?q=CHIC%20Building%2C%20Kigali%20City%20Center%2C%20Kigali%2C%20Rwanda&amp;output=embed"
                loading="lazy"
                referrerpolicy="strict-origin-when-cross-origin"
                allowfullscreen></iframe>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
