<?php
require_once __DIR__ . '/includes/site.php';
$pageTitle = 'Services';
$pageDescription = 'Explore cargo transportation, cement importation, warehousing, garage, clearing and training services from Nine United Traders Ltd.';
$activePage = 'services';
include __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
    <div class="container page-hero-inner">
        <div><div class="breadcrumb"><a href="index.php">Home</a> / Services</div><span class="eyebrow">What we do</span><h1>Logistics services, connected around your needs</h1><p>From cargo transportation to clearing support, discover the services available from Nine United Traders.</p></div>
        <a class="button" href="quote.php">Request a service quote <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
    </div>
</section>
<section class="page-content">
    <div class="container">
        <div class="service-detail-grid">
            <article class="service-detail" id="transport"><span class="service-icon" aria-hidden="true"><i class="fa-solid fa-truck-fast"></i></span><div><h2>Cargo transportation</h2><p>Regional cargo transportation services to help businesses move goods. Contact us with your route and shipment details so we can discuss your requirements.</p><a class="text-link" href="quote.php?service=Cargo%20transportation">Request a transport quote <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div></article>
            <article class="service-detail" id="importation"><span class="service-icon" aria-hidden="true"><i class="fa-solid fa-box"></i></span><div><h2>Cement &amp; commodity importation</h2><p>Importation of cement and other commodities. Get in touch to discuss product availability, quantities and your business requirements.</p><a class="text-link" href="quote.php?service=Cement%20and%20commodity%20importation">Discuss importation <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div></article>
            <article class="service-detail" id="warehousing"><span class="service-icon" aria-hidden="true"><i class="fa-solid fa-warehouse"></i></span><div><h2>Warehousing &amp; stores</h2><p>Storage and warehousing support for goods and business operations. Share what you need to store and when you need the service.</p><a class="text-link" href="quote.php?service=Warehousing%20and%20stores">Discuss storage needs <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div></article>
            <article class="service-detail" id="garage"><span class="service-icon" aria-hidden="true"><i class="fa-solid fa-screwdriver-wrench"></i></span><div><h2>Garage services</h2><p>Fleet and truck support services. Contact us to explain your vehicle or operational support requirement.</p><a class="text-link" href="quote.php?service=Garage%20services">Ask about garage services <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div></article>
            <article class="service-detail" id="clearing"><span class="service-icon" aria-hidden="true"><i class="fa-solid fa-right-left"></i></span><div><h2>Clearing agency</h2><p>Clearing and logistics documentation support. Tell us about your shipment and where it is in the process.</p><a class="text-link" href="quote.php?service=Clearing%20agency">Discuss clearing support <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div></article>
            <article class="service-detail" id="training"><span class="service-icon" aria-hidden="true"><i class="fa-solid fa-person-chalkboard"></i></span><div><h2>Training center</h2><p>Capacity building in transport and logistics. Contact us with the subject and training needs you have in mind.</p><a class="text-link" href="quote.php?service=Training%20center">Ask about training <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div></article>
        </div>
    </div>
</section>
<section class="cta-band"><div class="container cta-band-inner"><div><span class="eyebrow">A clear next step</span><h2>Not sure which service fits your requirement?</h2><p>Send us the details and we’ll direct your inquiry to the right team.</p></div><a class="button" href="contact.php">Ask a question <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div></section>
<?php include __DIR__ . '/includes/footer.php'; ?>
