<?php
require_once __DIR__ . '/includes/site.php';
$pageTitle = 'About Us';
$pageDescription = 'Learn about Nine United Traders Ltd, a Rwandan logistics and trade company based in Kigali.';
$activePage = 'about';
include __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
    <div class="container page-hero-inner">
        <div><div class="breadcrumb"><a href="index.php">Home</a> / About</div><span class="eyebrow">About Nine United</span><h1>Connecting trade with dependable logistics</h1><p>We are a Rwandan company working across transportation, commodity importation and logistics support.</p></div>
        <a class="button" href="contact.php">Talk to our team <span aria-hidden="true">↗</span></a>
    </div>
</section>
<section class="page-content">
    <div class="container">
        <div class="content-grid">
            <div>
                <span class="eyebrow">Our company</span>
                <h2>Rooted in Kigali. Focused on getting business done.</h2>
                <p>Nine United Traders Ltd is a Rwandan business established in 2019. Our work brings together cargo transportation, cement and commodity importation, warehousing, garage services, clearing support and logistics training.</p>
                <p>We work with businesses that need practical support moving goods and coordinating the services around them. Get in touch to discuss your requirement and the right way to proceed.</p>
                <a class="text-link" href="services.php">See our services <span aria-hidden="true">→</span></a>
            </div>
            <div class="content-panel">
                <span class="eyebrow">At a glance</span>
                <h2>Built for trade and logistics</h2>
                <ul class="check-list">
                    <li>Established in Rwanda in 2019</li>
                    <li>Based at CHIC Building, Kigali City Center</li>
                    <li>Working across transport, trade and logistics support</li>
                    <li>Company profile reports 25 shareholders</li>
                </ul>
            </div>
        </div>
        <div class="value-grid">
            <article class="value-card"><span class="service-icon" aria-hidden="true"><svg class="icon"><use href="#icon-transfer"></use></svg></span><h3>Practical service</h3><p>We offer a range of connected services to support the movement and handling of goods.</p></article>
            <article class="value-card"><span class="service-icon" aria-hidden="true"><svg class="icon"><use href="#icon-location"></use></svg></span><h3>Local presence</h3><p>Our Kigali base puts our team in touch with the businesses and partners we serve.</p></article>
            <article class="value-card"><span class="service-icon" aria-hidden="true"><svg class="icon"><use href="#icon-globe"></use></svg></span><h3>Regional outlook</h3><p>Our transportation and clearing services support business activity across Rwanda and the region.</p></article>
        </div>
    </div>
</section>
<section class="cta-band"><div class="container cta-band-inner"><div><span class="eyebrow">Need more information?</span><h2>Let’s discuss how we can support your business.</h2></div><a class="button" href="contact.php">Contact us <span aria-hidden="true">↗</span></a></div></section>
<?php include __DIR__ . '/includes/footer.php'; ?>
