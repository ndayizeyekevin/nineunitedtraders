</main>
<footer class="site-footer">
    <div class="container footer-grid">
        <div class="footer-brand">
            <a class="brand brand-light" href="./">
                <img class="brand-logo" src="assets/images/logo.png" alt="">
                <span class="brand-name">Nine United <span>Traders Ltd</span></span>
            </a>
            <p>A reliable partner in logistics and trade, based in Kigali, Rwanda.</p>
        </div>
        <div>
            <h2 class="footer-title">Get in touch</h2>
            <address class="footer-contact"><i class="fa-solid fa-location-dot" aria-hidden="true"></i><span>CHIC Building, Kigali City Center<br>P.O. Box 5855, Kigali, Rwanda</span></address>
            <a class="footer-link footer-contact" href="tel:+250788302388"><i class="fa-solid fa-phone" aria-hidden="true"></i><span>+250 788 302 388</span></a>
            <a class="footer-link footer-contact" href="mailto:nineunited2050@gmail.com"><i class="fa-solid fa-envelope" aria-hidden="true"></i><span>nineunited2050@gmail.com</span></a>
        </div>
        <div>
            <h2 class="footer-title">Explore</h2>
            <a class="footer-link" href="services">Our services</a>
            <a class="footer-link" href="fleet">Fleet & operations</a>
            <a class="footer-link" href="management">Management</a>
            <a class="footer-link" href="contact">Contact</a>
        </div>
        <div class="footer-cta">
            <p>Have a shipment or logistics requirement?</p>
            <a class="button button-light" href="quote">Tell us what you need <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
        </div>
    </div>
    <div class="container footer-bottom">
        <span>© <?= date('Y') ?> Nine United Traders Ltd. All rights reserved.</span>
        <div class="footer-bottom-meta">
            <span>Registered in Rwanda · TIN: 108642399</span>
            <button class="footer-admin-login" type="button" data-login-open aria-haspopup="dialog" aria-controls="admin-login-dialog">Admin login</button>
        </div>
    </div>
</footer>
<dialog class="site-login-dialog" id="admin-login-dialog" aria-labelledby="admin-login-title">
    <div class="site-login-dialog-content">
        <button class="site-login-close" type="button" data-login-close aria-label="Close sign-in dialog">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
        <a class="admin-auth-brand" href="./">
            <img src="assets/images/logo.png" alt="">
            <span>Nine United <strong>Traders Ltd</strong></span>
        </a>
        <div class="admin-auth-content">
            <section class="page-hero">
                <div class="container page-hero-inner">
                    <div><span class="eyebrow">Administration</span><h1 id="admin-login-title">Admin sign in</h1><p>Sign in to manage quote requests and management profiles.</p></div>
                </div>
            </section>
            <section class="admin-page">
                <section class="admin-card">
                    <div class="site-login-message form-alert form-error" role="alert" aria-live="polite" hidden></div>
                    <form class="admin-form" method="post" action="<?= escape(site_base_url()) ?>admin/login" data-admin-login>
                        <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                        <div class="field"><label for="footer-admin-username">Username</label><input class="form-control" id="footer-admin-username" name="username" autocomplete="username" required></div>
                        <div class="field"><label for="footer-admin-password">Password</label><input class="form-control" id="footer-admin-password" name="password" type="password" autocomplete="current-password" required><a class="admin-forgot-link" href="<?= escape(site_base_url()) ?>admin/forgot-password">Forgot password?</a></div>
                        <button class="button form-submit" type="submit">Sign in</button>
                    </form>
                </section>
            </section>
        </div>
        <a class="admin-auth-back" href="./" data-login-close><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to website</a>
        <footer class="admin-auth-footer">© <?= date('Y') ?> Nine United Traders Ltd</footer>
    </div>
</dialog>
<a class="whatsapp-link" href="https://wa.me/250788302388" target="_blank" rel="noopener noreferrer" aria-label="Chat with us on WhatsApp">
    <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
</a>
<a class="scroll-top" href="#top" aria-label="Scroll to top" hidden>
    <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
</a>
</body>
</html>
