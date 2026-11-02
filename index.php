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
        <div class="hero-art" data-hero-slider role="region" aria-roledescription="carousel" aria-label="Nine United Traders logistics services">
            <div class="hero-slides" aria-live="off">
                <div class="hero-slide is-active" role="group" aria-roledescription="slide" aria-label="1 of 3" aria-hidden="false">
                    <img src="assets/images/site/freight-truck.jpg" alt="Freight truck travelling on a regional road" fetchpriority="high">
                    <div class="hero-caption">
                        <span class="caption-icon" aria-hidden="true"><i class="fa-solid fa-truck-fast"></i></span>
                        <span><strong>Moving trade forward</strong><span>Transport · Trade · Logistics</span></span>
                    </div>
                </div>
                <div class="hero-slide" role="group" aria-roledescription="slide" aria-label="2 of 3" aria-hidden="true">
                    <img src="assets/images/site/warehouse-cargo.jpg" alt="Goods organized and ready for warehouse handling" loading="lazy">
                    <div class="hero-caption">
                        <span class="caption-icon" aria-hidden="true"><i class="fa-solid fa-warehouse"></i></span>
                        <span><strong>Supporting every link</strong><span>Warehousing · Trade · Logistics</span></span>
                    </div>
                </div>
                <div class="hero-slide" role="group" aria-roledescription="slide" aria-label="3 of 3" aria-hidden="true">
                    <img src="assets/images/site/cargo-ship.jpg" alt="Cargo vessel carrying freight across regional trade routes" loading="lazy">
                    <div class="hero-caption">
                        <span class="caption-icon" aria-hidden="true"><i class="fa-solid fa-ship"></i></span>
                        <span><strong>Connecting regional trade</strong><span>Importation · Clearing · Delivery</span></span>
                    </div>
                </div>
            </div>
            <div class="hero-slider-controls" role="group" aria-label="Hero image controls">
                <button class="hero-slider-arrow" type="button" data-hero-previous aria-label="Previous image"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button>
                <div class="hero-slider-dots" role="group" aria-label="Choose an image">
                    <button class="hero-slider-dot is-active" type="button" data-hero-slide="0" aria-label="Show image 1 of 3" aria-pressed="true"></button>
                    <button class="hero-slider-dot" type="button" data-hero-slide="1" aria-label="Show image 2 of 3" aria-pressed="false"></button>
                    <button class="hero-slider-dot" type="button" data-hero-slide="2" aria-label="Show image 3 of 3" aria-pressed="false"></button>
                </div>
                <button class="hero-slider-arrow" type="button" data-hero-toggle aria-label="Pause automatic image rotation" aria-pressed="false"><i class="fa-solid fa-pause" aria-hidden="true"></i></button>
                <button class="hero-slider-arrow" type="button" data-hero-next aria-label="Next image"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
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

<section class="planner-section" aria-labelledby="planner-title">
    <div class="container planner-layout">
        <div class="planner-main">
            <div class="section-heading">
                <span class="eyebrow">A simpler place to start</span>
                <h2 id="planner-title">Not sure what your shipment needs?</h2>
                <p>Tell us a little about what you’re planning. We’ll point you toward a relevant service and carry your details into a quote request.</p>
            </div>
            <form class="planner-card" action="quote#quote-form" method="get" data-shipment-planner>
                <div class="planner-form-heading">
                    <span class="planner-step">01</span>
                    <div><strong>Plan your request</strong><span>Share what you know; you can fill in the rest later.</span></div>
                </div>
                <div class="planner-fields">
                    <div class="field field-full">
                        <label for="planner-service">What do you need help with?</label>
                        <select class="form-select" id="planner-service" name="service" data-planner-service>
                            <option value="Cargo transportation" data-guidance="For moving goods, share where they’re starting, where they’re going, and what needs to be transported.">Moving goods</option>
                            <option value="Cement and commodity importation" data-guidance="For an import inquiry, include the product or commodity and any quantity or timing details you know.">Importing cement or commodities</option>
                            <option value="Warehousing and stores" data-guidance="For storage support, tell us what you need to store, approximate quantity, and when you need space.">Storing goods</option>
                            <option value="Garage services" data-guidance="For fleet or garage support, describe the vehicle or operational service you need.">Getting vehicle or fleet support</option>
                            <option value="Clearing agency" data-guidance="For clearing support, share the cargo, route, and any shipment or document details currently available.">Clearing a shipment</option>
                            <option value="Training center" data-guidance="For training, tell us the topic and who the training is for.">Finding logistics training</option>
                            <option value="Other" data-guidance="Tell us what you’re trying to achieve and our team can help identify a suitable service.">I’m not sure yet</option>
                        </select>
                        <p class="planner-guidance" data-planner-guidance aria-live="polite">For moving goods, share where they’re starting, where they’re going, and what needs to be transported.</p>
                    </div>
                    <div class="field"><label for="planner-origin">From <span>(if applicable)</span></label><input class="form-control" id="planner-origin" name="origin" type="text" maxlength="120" placeholder="Town, city or country"></div>
                    <div class="field"><label for="planner-destination">To <span>(if applicable)</span></label><input class="form-control" id="planner-destination" name="destination" type="text" maxlength="120" placeholder="Town, city or country"></div>
                    <div class="field field-full"><label for="planner-cargo">Cargo or other details <span>(optional)</span></label><input class="form-control" id="planner-cargo" name="cargo" type="text" maxlength="180" placeholder="What are you moving, importing or storing?"></div>
                </div>
                <button class="button planner-submit" type="submit">Continue to quote details <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
            </form>
        </div>
        <aside class="planner-aside">
            <div class="planner-checklist-icon" aria-hidden="true"><i class="fa-solid fa-clipboard-check"></i></div>
            <span class="form-kicker">Before you get in touch</span>
            <h3>A few useful details</h3>
            <p>Having these details nearby can help us understand your request:</p>
            <ul class="planner-checklist">
                <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>What the goods or service requirement involves</span></li>
                <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Origin, destination and preferred timing</span></li>
                <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Approximate quantity, weight or vehicle details</span></li>
                <li><i class="fa-solid fa-check" aria-hidden="true"></i><span>Any relevant invoices, packing lists or shipment references you already have</span></li>
            </ul>
            <p class="planner-disclaimer">Document needs can vary by cargo, route and service. Our team can confirm what applies to your request.</p>
            <a class="text-link" href="services.php">Explore all services <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
        </aside>
    </div>
</section>

<section class="stats-section" aria-label="Company facts">
    <div class="container">
        <div class="stats-grid">
            <div class="stat"><strong data-count="2019">2019</strong><span>Established</span></div>
            <div class="stat"><strong data-count="25">25</strong><span>Shareholders</span></div>
            <div class="stat"><strong data-count="200" data-count-suffix="+">200+</strong><span>Truck units cleared</span></div>
            <div class="stat"><strong data-count="6" data-count-suffix="B+">6B+</strong><span>RWF investment</span></div>
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
