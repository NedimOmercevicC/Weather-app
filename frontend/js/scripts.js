/*!
* Start Bootstrap - Grayscale v7.0.6 (https://startbootstrap.com/theme/grayscale)
* Copyright 2013-2023 Start Bootstrap
* Licensed under MIT (https://github.com/StartBootstrap/startbootstrap-grayscale/blob/master/LICENSE)
*/
//
// Scripts
// 

window.addEventListener('DOMContentLoaded', event => {

    // Navbar shrink function
    var navbarShrink = function () {
        const navbarCollapsible = document.body.querySelector('#mainNav');
        if (!navbarCollapsible) {
            return;
        }
        if (window.scrollY === 0) {
            navbarCollapsible.classList.remove('navbar-shrink')
        } else {
            navbarCollapsible.classList.add('navbar-shrink')
        }

    };

    // Shrink the navbar 
    navbarShrink();

    // Shrink the navbar when page is scrolled
    document.addEventListener('scroll', navbarShrink);

    // Activate Bootstrap scrollspy on the main nav element
    const mainNav = document.body.querySelector('#mainNav');
    if (mainNav) {
        new bootstrap.ScrollSpy(document.body, {
            target: '#mainNav',
            rootMargin: '0px 0px -40%',
        });
    };

    // Collapse responsive navbar when toggler is visible
    const navbarToggler = document.body.querySelector('.navbar-toggler');
    const responsiveNavItems = [].slice.call(
        document.querySelectorAll('#navbarResponsive .nav-link')
    );
    responsiveNavItems.map(function (responsiveNavItem) {
        responsiveNavItem.addEventListener('click', () => {
            if (window.getComputedStyle(navbarToggler).display !== 'none') {
                navbarToggler.click();
            }
        });
    });

});

// Weather, Subscription, and Simple Email Auth logic
(function () {
    const weatherForm = document.getElementById('weatherForm');
    const cityInput = document.getElementById('cityInput');
    const unitsSelect = document.getElementById('unitsSelect');
    const weatherResult = document.getElementById('weatherResult');
    const weatherCity = document.getElementById('weatherCity');
    const weatherDesc = document.getElementById('weatherDesc');
    const weatherTemp = document.getElementById('weatherTemp');
    const weatherMeta = document.getElementById('weatherMeta');
    const weatherError = document.getElementById('weatherError');

    async function fetchWeather(city, units) {
        // Uses Open-Meteo (no key) via geocoding + forecast
        const geocodeUrl = `https://geocoding-api.open-meteo.com/v1/search?name=${encodeURIComponent(city)}&count=1`;
        const geoRes = await fetch(geocodeUrl);
        if (!geoRes.ok) throw new Error('Failed to geocode city');
        const geoData = await geoRes.json();
        const loc = geoData && geoData.results && geoData.results[0];
        if (!loc) throw new Error('City not found');

        const isMetric = units !== 'imperial';
        const tempUnit = isMetric ? 'celsius' : 'fahrenheit';
        const speedUnit = isMetric ? 'kmh' : 'mph';
        const forecastUrl = `https://api.open-meteo.com/v1/forecast?latitude=${loc.latitude}&longitude=${loc.longitude}&current=temperature_2m,relative_humidity_2m,apparent_temperature,precipitation,weather_code,wind_speed_10m&temperature_unit=${tempUnit}&wind_speed_unit=${speedUnit}`;
        const wxRes = await fetch(forecastUrl);
        if (!wxRes.ok) throw new Error('Failed to fetch weather');
        const wx = await wxRes.json();
        return { loc, wx };
    }

    function weatherCodeToText(code) {
        // Minimal mapping for display
        const map = {
            0: 'Clear', 1: 'Mainly Clear', 2: 'Partly Cloudy', 3: 'Overcast',
            45: 'Fog', 48: 'Depositing Rime Fog',
            51: 'Drizzle Light', 53: 'Drizzle Moderate', 55: 'Drizzle Dense',
            61: 'Rain Slight', 63: 'Rain Moderate', 65: 'Rain Heavy',
            71: 'Snow Slight', 73: 'Snow Moderate', 75: 'Snow Heavy',
            95: 'Thunderstorm', 96: 'Thunderstorm with Hail', 99: 'Thunderstorm with Heavy Hail'
        };
        return map[code] || 'Weather';
    }

    async function handleWeatherSubmit(e) {
        if (!weatherForm) return;
        e.preventDefault();
        weatherError && weatherError.classList.add('d-none');
        weatherResult && weatherResult.classList.add('d-none');
        const city = cityInput && cityInput.value.trim();
        const units = unitsSelect ? unitsSelect.value : 'metric';
        if (!city) return;
        try {
            const { loc, wx } = await fetchWeather(city, units);
            const current = wx.current;
            const unitSymbol = units === 'imperial' ? '°F' : '°C';
            weatherCity && (weatherCity.textContent = `${loc.name}, ${loc.country}`);
            weatherDesc && (weatherDesc.textContent = weatherCodeToText(current.weather_code));
            weatherTemp && (weatherTemp.textContent = `${Math.round(current.temperature_2m)}${unitSymbol}`);
            weatherMeta && (weatherMeta.textContent = `Feels ${Math.round(current.apparent_temperature)}${unitSymbol} • Humidity ${current.relative_humidity_2m}% • Wind ${Math.round(current.wind_speed_10m)} ${units === 'imperial' ? 'mph' : 'km/h'}`);
            weatherResult && weatherResult.classList.remove('d-none');
        } catch (err) {
            if (weatherError) {
                weatherError.textContent = err.message || 'Unable to fetch weather.';
                weatherError.classList.remove('d-none');
            }
        }
    }

    weatherForm && weatherForm.addEventListener('submit', handleWeatherSubmit);
    // Quick city buttons
    document.querySelectorAll('.quick-city').forEach(btn => {
        btn.addEventListener('click', function () {
            if (!cityInput) return;
            cityInput.value = this.getAttribute('data-city') || '';
            weatherForm && weatherForm.dispatchEvent(new Event('submit'));
        });
    });

    // Subscription form handler (local-only feedback)
    const contactForm = document.getElementById('contactForm');
    const emailInput = document.getElementById('emailAddress');
    const submitButton = document.getElementById('submitButton');
    const submitSuccessMessage = document.getElementById('submitSuccessMessage');
    const submitErrorMessage = document.getElementById('submitErrorMessage');

    if (submitButton) submitButton.classList.remove('disabled');

    contactForm && contactForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const email = emailInput && emailInput.value.trim();
        if (!email) {
            submitErrorMessage && (submitErrorMessage.classList.remove('d-none'));
            return;
        }
        try {
            const list = JSON.parse(localStorage.getItem('subscriptions') || '[]');
            if (!list.includes(email)) list.push(email);
            localStorage.setItem('subscriptions', JSON.stringify(list));
            submitErrorMessage && submitErrorMessage.classList.add('d-none');
            submitSuccessMessage && submitSuccessMessage.classList.remove('d-none');
        } catch (_) {
            submitErrorMessage && submitErrorMessage.classList.remove('d-none');
        }
    });

    // Simple email login/signup with localStorage
    const loginForm = document.getElementById('loginForm');
    const loginEmail = document.getElementById('loginEmail');
    const loginPassword = document.getElementById('loginPassword');
    const loginError = document.getElementById('loginError');

    const signupForm = document.getElementById('signupForm');
    const signupEmail = document.getElementById('signupEmail');
    const signupPassword = document.getElementById('signupPassword');
    const signupError = document.getElementById('signupError');
    const signupSuccess = document.getElementById('signupSuccess');

    function readUsers() {
        try {
            return JSON.parse(localStorage.getItem('users') || '{}');
        } catch (_) {
            return {};
        }
    }

    function writeUsers(users) {
        localStorage.setItem('users', JSON.stringify(users));
    }

    function setSession(email) {
        localStorage.setItem('sessionEmail', email);
    }

    function getSession() {
        return localStorage.getItem('sessionEmail');
    }

    function clearSession() {
        localStorage.removeItem('sessionEmail');
    }

    function validateEmail(email) {
        return /.+@.+\..+/.test(email);
    }

    signupForm && signupForm.addEventListener('submit', function (e) {
        e.preventDefault();
        signupError && signupError.classList.add('d-none');
        signupSuccess && signupSuccess.classList.add('d-none');
        const email = signupEmail && signupEmail.value.trim();
        const password = signupPassword && signupPassword.value;
        if (!validateEmail(email) || !password || password.length < 6) {
            signupError && (signupError.textContent = 'Invalid email or password too short.');
            signupError && signupError.classList.remove('d-none');
            return;
        }
        const users = readUsers();
        if (users[email]) {
            signupError && (signupError.textContent = 'Email already registered.');
            signupError && signupError.classList.remove('d-none');
            return;
        }
        users[email] = { email: email, password: password };
        writeUsers(users);
        signupSuccess && signupSuccess.classList.remove('d-none');
    });

    loginForm && loginForm.addEventListener('submit', function (e) {
        e.preventDefault();
        loginError && loginError.classList.add('d-none');
        const email = loginEmail && loginEmail.value.trim();
        const password = loginPassword && loginPassword.value;
        const users = readUsers();
        if (!users[email] || users[email].password !== password) {
            loginError && (loginError.textContent = 'Invalid credentials.');
            loginError && loginError.classList.remove('d-none');
            return;
        }
        setSession(email);
        const modalEl = document.getElementById('authModal');
        if (modalEl) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.hide();
        }
        updateAuthUI();
    });

    // Auth UI indicator in navbar
    function updateAuthUI() {
        const email = getSession();
        let nav = document.querySelector('#mainNav .navbar-nav');
        if (!nav) return;
        let badge = document.getElementById('authBadge');
        if (!badge) {
            badge = document.createElement('li');
            badge.className = 'nav-item';
            badge.innerHTML = '<span class="nav-link" id="authBadge"></span>';
            nav.appendChild(badge);
            badge = document.getElementById('authBadge');
        }
        let logoutLi = document.getElementById('logoutNav');
        if (!logoutLi) {
            logoutLi = document.createElement('li');
            logoutLi.className = 'nav-item';
            logoutLi.id = 'logoutNav';
            logoutLi.innerHTML = '<a class="nav-link" href="#">Logout</a>';
            nav.appendChild(logoutLi);
            logoutLi.addEventListener('click', function (e) {
                e.preventDefault();
                clearSession();
                updateAuthUI();
            });
        }
        if (email) {
            badge.textContent = email;
            logoutLi.classList.remove('d-none');
        } else {
            badge.textContent = '';
            logoutLi.classList.add('d-none');
        }
    }

    updateAuthUI();
})();

// Subscription buttons: require login, then redirect to placeholder payment URL
(function () {
    function getSession() {
        return localStorage.getItem('sessionEmail');
    }
    function openLoginModal() {
        const modalEl = document.getElementById('authModal');
        if (modalEl) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
            // switch to login tab
            const loginTabBtn = document.querySelector('#authTabs button#login-tab');
            if (loginTabBtn) new bootstrap.Tab(loginTabBtn).show();
        }
    }
    function handleSubscribeClick(e) {
        e.preventDefault();
        const plan = this.getAttribute('data-plan');
        const rawPrice = this.getAttribute('data-price');
        const currency = this.getAttribute('data-currency') || 'BAM';
        // format price with comma decimal for BAM
        const price = (rawPrice || '').replace('.', ',');
        const email = getSession();
        if (!email) {
            openLoginModal();
            return;
        }
        // Open Payment modal instead of redirecting
        const paymentPlan = document.getElementById('paymentPlan');
        if (paymentPlan) paymentPlan.value = `${plan.toUpperCase()} plan - ${currency} ${price}/mo`;
        const modalEl = document.getElementById('paymentModal');
        if (modalEl) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    }
    document.querySelectorAll('.subscribe-btn').forEach(btn => {
        btn.addEventListener('click', handleSubscribeClick);
    });
})();

// Handle Payment form submit (simulated)
(function () {
    const paymentForm = document.getElementById('paymentForm');
    const payerFullName = document.getElementById('payerFullName');
    const cardName = document.getElementById('cardName');
    const paymentError = document.getElementById('paymentError');
    const paymentSuccess = document.getElementById('paymentSuccess');
    const paymentModalEl = document.getElementById('paymentModal');

    paymentForm && paymentForm.addEventListener('submit', function (e) {
        e.preventDefault();
        paymentError && paymentError.classList.add('d-none');
        paymentSuccess && paymentSuccess.classList.add('d-none');
        const fullName = payerFullName && payerFullName.value.trim();
        const nameOnCard = cardName && cardName.value.trim();
        if (!fullName || !nameOnCard) {
            if (paymentError) {
                paymentError.textContent = 'Please enter your full name and card name.';
                paymentError.classList.remove('d-none');
            }
            return;
        }
        // Simulate success
        setTimeout(() => {
            paymentSuccess && paymentSuccess.classList.remove('d-none');
            if (paymentModalEl) {
                const modal = bootstrap.Modal.getOrCreateInstance(paymentModalEl);
                setTimeout(() => modal.hide(), 1000);
            }
        }, 500);
    });
})();