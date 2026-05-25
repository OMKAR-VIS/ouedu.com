(function () {
    const year = new Date().getFullYear();

    if (!document.getElementById('siteFooter')) {
        const footer = document.createElement('footer');
        footer.id = 'siteFooter';
        footer.className = 'site-footer';
        footer.innerHTML = `
            <div class="footer-grid">
                <div class="footer-brand">
                    <img src="image/OUR_EDUCATION_MENTOR'S_png.png" alt="Our Education Mentor">
                    <h4>OUR EDUCATION MENTOR</h4>
                    <p>From Jharkhand, for Jharkhand students. JAC Board Class 10 ke liye clear concepts, video lectures aur PDF notes.</p>
                    <div class="footer-social">
                        <a href="https://youtube.com/@oureducationmentor" target="_blank" rel="noopener"><i class="fab fa-youtube"></i></a>
                        <a href="https://www.instagram.com/oureducationmentor" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a>
                        <a href="https://www.facebook.com/share/1Sc5XM9Qma/" target="_blank" rel="noopener"><i class="fab fa-facebook"></i></a>
                        <a href="https://whatsapp.com/channel/0029VbCMwENEawdtAnUt0f2s" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
                <div>
                    <h5>Quick Access</h5>
                    <ul>
                        <li><a href="index.html">Home</a></li>
                        <li><a href="about.html">About Us</a></li>
                        <li><a href="course.html">Courses</a></li>
                        <li><a href="contact.html">Contact</a></li>
                        <li><a href="#" id="openRegisterFromFooter">Student Register</a></li>
                    </ul>
                </div>
                <div>
                    <h5>Our Courses</h5>
                    <ul>
                        <li><a href="course.html">English</a></li>
                        <li><a href="course.html">Mathematics</a></li>
                        <li><a href="course.html">Science</a></li>
                        <li><a href="course.html">Social Science</a></li>
                        <li><a href="course.html">Hindi &amp; Computer</a></li>
                    </ul>
                </div>
                <div>
                    <h5>Contact Us</h5>
                    <ul class="footer-contact">
                        <li><i class="fas fa-map-marker-alt"></i><span>Giridih, Jharkhand, India</span></li>
                        <li><i class="fas fa-envelope"></i><span>oureducationmentor@gmail.com</span></li>
                        <li><i class="fas fa-phone"></i><span>+91 0000000000</span></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; ${year} <span>Our Education Mentor</span>. All Rights Reserved.</p>
            </div>
        `;
        document.body.appendChild(footer);
    }

    document.body.classList.add('page-with-footer');

    if (!document.querySelector('script[data-notices]')) {
        const ns = document.createElement('script');
        ns.src = 'js/notices.js';
        ns.setAttribute('data-notices', '1');
        document.body.appendChild(ns);
    }

    if (!document.getElementById('alertBox')) {
        document.body.insertAdjacentHTML('beforeend', '<div id="alertBox"></div>');
    }

    if (!document.getElementById('loginModal')) {
        document.body.insertAdjacentHTML('beforeend', `
            <div id="loginModal" class="modal-overlay">
                <div class="modal-box">
                    <button class="modal-close" id="closeModal" type="button">&times;</button>
                    <h2>Welcome Back!</h2>
                    <p class="modal-sub">Login to continue learning</p>
                    <form id="loginForm">
                        <div class="form-group">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" placeholder="Your Email" required>
                        </div>
                        <div class="form-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" placeholder="Your Password" required>
                        </div>
                        <button type="submit" class="modal-submit-btn" id="loginBtn">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                    </form>
                    <div class="modal-footer-link">
                        New student? <a href="#" id="openRegisterFromLogin">Create account here</a>
                    </div>
                </div>
            </div>
        `);
    }

    if (!document.getElementById('registerModal')) {
        document.body.insertAdjacentHTML('beforeend', `
            <div id="registerModal" class="modal-overlay">
                <div class="modal-box">
                    <button class="modal-close" id="closeRegisterModal" type="button">&times;</button>
                    <h2>Create Account</h2>
                    <p class="modal-sub">Register as a student</p>
                    <form id="registerModalForm">
                        <div class="form-group">
                            <i class="fas fa-user"></i>
                            <input type="text" name="name" placeholder="Full Name" required>
                        </div>
                        <div class="form-group">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" placeholder="Email" required>
                        </div>
                        <div class="form-group">
                            <i class="fas fa-phone"></i>
                            <input type="tel" name="mobile" placeholder="Mobile Number" pattern="[0-9]{10}" maxlength="10" required>
                        </div>
                        <div class="form-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" placeholder="Password (min 6 chars)" minlength="6" required>
                        </div>
                        <button type="submit" class="modal-submit-btn" id="registerModalBtn">
                            <i class="fas fa-user-plus me-2"></i>Register
                        </button>
                    </form>
                    <div class="modal-footer-link">
                        I already have an account. <a href="#" id="openLoginFromRegister">Login here</a>
                    </div>
                </div>
            </div>
        `);
    }
})();
