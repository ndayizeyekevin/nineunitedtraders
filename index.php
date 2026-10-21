<?php
require_once __DIR__ . '/includes/site.php';
$pageTitle = 'Reliable Logistics & Trade';
$pageDescription = 'Cargo transportation, cement importation, warehousing, clearing and logistics solutions across Rwanda and the region.';
$activePage = 'home';
include __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div class="container hero-grid">
        <div class="hero-copy">
            <span class="eyebrow">Logistics rooted in Rwanda</span>
            <h1>Your reliable partner in <em>logistics &amp; trade</em></h1>
            <p>Cargo transportation, cement importation, warehousing, clearing and logistics support across Rwanda and the region.</p>
            <div class="hero-actions">
                <a class="button" href="quote.php">Request a quote <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                <a class="button button-secondary" href="services.php">Explore our services</a>
            </div>
            <div class="hero-trust">
                <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                <span>Based in Kigali <span class="trust-separator">·</span> Connected across the region</span>
            </div>
        </div>
        <div class="hero-art" role="img" aria-label="Cargo truck on a regional road">
            <div class="hero-caption">
                <span class="caption-icon" aria-hidden="true"><i class="fa-solid fa-truck-fast"></i></span>
                <span><strong>Moving trade forward</strong><span>Transport · Trade · Logistics</span></span>
            </div>
        </div>
    </div>
</section>

<section class="intro-section">
    <div class="container intro-grid">
        <div class="intro-statement">
            <p>Practical logistics and trade services, built around the needs of your business.</p>
        </div>
        <div class="intro-copy">
            <span class="eyebrow">Who we are</span>
            <h2>Local knowledge. Regional perspective.</h2>
            <p>Nine United Traders Ltd is a Rwandan company working across cargo transportation, cement and commodity importation, and logistics support. From Kigali, we help businesses connect the services they need to keep goods moving.</p>
            <a class="text-link" href="about.php">Discover Nine United <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
        </div>
    </div>
</section>

<section class="service-section">
    <div class="container">
        <div class="section-heading">
            <span class="eyebrow">What we do</span>
            <h2>Services for every step of the journey</h2>
            <p>Explore our core services and tell us what your operation needs. We’ll help you find the right next step.</p>
        </div>
        <div class="service-grid">
            <article class="service-card"><span class="service-icon" aria-hidden="true"><i class="fa-solid fa-truck-fast"></i></span><h3>Cargo transportation</h3><p>Regional cargo transportation services for businesses moving goods.</p><a class="text-link" href="services.php#transport">Explore service <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></article>
            <article class="service-card"><span class="service-icon" aria-hidden="true"><i class="fa-solid fa-box"></i></span><h3>Cement &amp; commodities</h3><p>Importation of cement and other commodities for the market.</p><a class="text-link" href="services.php#importation">Explore service <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></article>
            <article class="service-card"><span class="service-icon" aria-hidden="true"><i class="fa-solid fa-warehouse"></i></span><h3>Warehousing &amp; stores</h3><p>Storage and warehousing support for goods and operations.</p><a class="text-link" href="services.php#warehousing">Explore service <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></article>
            <article class="service-card"><span class="service-icon" aria-hidden="true"><i class="fa-solid fa-screwdriver-wrench"></i></span><h3>Garage services</h3><p>Fleet and truck support services for transport operations.</p><a class="text-link" href="services.php#garage">Explore service <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></article>
            <article class="service-card"><span class="service-icon" aria-hidden="true"><i class="fa-solid fa-right-left"></i></span><h3>Clearing agency</h3><p>Clearing and logistics documentation support.</p><a class="text-link" href="services.php#clearing">Explore service <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></article>
            <article class="service-card"><span class="service-icon" aria-hidden="true"><i class="fa-solid fa-person-chalkboard"></i></span><h3>Training center</h3><p>Capacity building in transport and logistics.</p><a class="text-link" href="services.php#training">Explore service <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></article>
        </div>
    </div>
</section>

<section class="stats-section" aria-label="Company facts">
    <div class="container">
        <div class="stats-grid">
            <div class="stat"><strong>2019</strong><span>Established</span></div>
            <div class="stat"><strong>25</strong><span>Shareholders</span></div>
            <div class="stat"><strong>200+</strong><span>Truck units cleared</span></div>
            <div class="stat"><strong>6B+</strong><span>RWF investment</span></div>
        </div>
        <p class="stat-note">Company figures as published in the Nine United Traders company profile.</p>
    </div>
</section>

<section class="home-gallery">
    <div class="container">
        <div class="section-heading">
            <span class="eyebrow">Our work</span>
            <h2>Trade depends on reliable connections</h2>
            <p>From transport and storage to clearing support, our services help connect the pieces of regional trade.</p>
        </div>
        <div class="gallery-grid">
            <article class="gallery-card"><a href="fleet.php"><h3>Transportation</h3><p>Regional cargo services <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></p></a></article>
            <article class="gallery-card"><a href="services.php#warehousing"><h3>Warehousing</h3><p>Storage and logistics support <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></p></a></article>
            <article class="gallery-card"><a href="about.php"><h3>Company profile</h3><p>Get to know Nine United <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></p></a></article>
        </div>
        <p class="image-disclaimer">Images are representative logistics photography and may not depict Nine United Traders equipment or facilities.</p>
    </div>
</section>
<section class="cta-band">
    <div class="container cta-band-inner">
        <div><span class="eyebrow">Let's get moving</span><h2>Have a shipment or logistics requirement?</h2><p>Share a few details and our team can follow up with you.</p></div>
        <a class="button" href="quote.php">Start a quote <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
