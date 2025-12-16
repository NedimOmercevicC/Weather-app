// SPA Router for Weather Application
class SPARouter {
    constructor() {
        this.apiBaseUrl = 'backend/index.php';
        this.routes = {
            '#home': 'frontend/views/home.html',
            '#weather': 'frontend/views/weather.html',
            '#about': 'frontend/views/about.html',
            '#pricing': 'frontend/views/pricing.html',
            '#signup': 'frontend/views/contact.html',
            '#dashboard': 'frontend/views/dashboard.html',
            '#admin': 'frontend/views/admin.html'
        };
        this.currentRoute = '#home';
        this.modalsLoaded = false;
        this.currentUser = null;
        this.init();
    }

    async apiCall(endpoint, method = 'GET', data = null) {
        const url = `${this.apiBaseUrl}${endpoint}`;
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            }
        };

        // Add JWT token to Authorization header if available
        const token = localStorage.getItem('jwt_token');
        if (token) {
            options.headers['Authorization'] = `Bearer ${token}`;
        }

        if (data) {
            options.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(url, options);
            const result = await response.json();

            if (!response.ok || result.error) {
                // Handle 401 Unauthorized - token expired or invalid
                if (response.status === 401) {
                    this.logout();
                    throw new Error('Session expired. Please log in again.');
                }
                throw new Error(result.message || 'API request failed');
            }

            return result;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    init() {
        this.loadModals();
        this.loadRoute(window.location.hash || '#home');
        this.checkSession();

        window.addEventListener('hashchange', () => {
            this.loadRoute(window.location.hash);
        });

        document.addEventListener('click', (e) => {
            if (e.target.matches('a[href^="#"]')) {
                e.preventDefault();
                const href = e.target.getAttribute('href');
                if (href && this.routes[href]) {
                    window.location.hash = href;
                }
            }
        });
    }

    async checkSession() {
        const token = localStorage.getItem('jwt_token');
        const userId = localStorage.getItem('userId');

        if (token && userId) {
            try {
                // Use /api/users/me endpoint to get current user
                const result = await this.apiCall('/api/users/me');
                if (result.data) {
                    this.currentUser = result.data;
                    this.updateAuthUI();
                }
            } catch (error) {
                // Token expired or invalid, clear session
                this.logout();
            }
        } else {
            this.logout();
        }
    }

    logout() {
        localStorage.removeItem('jwt_token');
        localStorage.removeItem('userId');
        localStorage.removeItem('userEmail');
        localStorage.removeItem('isAdmin');
        this.currentUser = null;
        this.updateAuthUI();

        // Redirect to home if on protected pages
        if (window.location.hash === '#dashboard' || window.location.hash === '#admin') {
            window.location.hash = '#home';
        }
    }

    async loadModals() {
        if (this.modalsLoaded) return;

        try {
            const response = await fetch('frontend/views/login.html');
            if (response.ok) {
                const content = await response.text();
                const modalsContainer = document.getElementById('modals-container');
                if (modalsContainer) {
                    modalsContainer.innerHTML = content;
                    this.modalsLoaded = true;
                    this.initializeAuth();
                }
            }
        } catch (error) {
            console.error('Error loading modals:', error);
        }
    }

    async loadRoute(route) {
        if (!this.routes[route]) {
            route = '#home';
        }

        // Protect dashboard and admin routes
        if (route === '#dashboard' || route === '#admin') {
            if (!this.isAuthenticated()) {
                alert('Please log in to access this page.');
                window.location.hash = '#home';
                this.openLoginModal();
                return;
            }
        }

        // Protect admin route - require admin role
        if (route === '#admin') {
            if (!this.isAdmin()) {
                alert('Admin access required.');
                window.location.hash = '#dashboard';
                return;
            }
        }

        this.currentRoute = route;

        try {
            const response = await fetch(this.routes[route]);
            if (!response.ok) {
                throw new Error(`Failed to load ${route}`);
            }
            const content = await response.text();

            // Update main content
            const mainContent = document.getElementById('main-content');
            if (mainContent) {
                mainContent.innerHTML = content;

                // Re-initialize any components that need it
                this.initializeComponents();
            }
        } catch (error) {
            console.error('Error loading route:', error);
            // Fallback to home
            if (route !== '#home') {
                this.loadRoute('#home');
            }
        }
    }

    initializeComponents() {
        // Load dashboard data if on dashboard route
        if (this.currentRoute === '#dashboard') {
            setTimeout(() => this.loadDashboard(), 100);
        }

        // Load admin panel data if on admin route
        if (this.currentRoute === '#admin') {
            setTimeout(() => this.loadAdminPanel(), 100);
        }

        const weatherForm = document.getElementById('weatherForm');
        if (weatherForm) {
            weatherForm.addEventListener('submit', this.handleWeatherSubmit);
        }

        document.querySelectorAll('.quick-city').forEach(btn => {
            btn.addEventListener('click', function () {
                const cityInput = document.getElementById('cityInput');
                if (cityInput) {
                    cityInput.value = this.getAttribute('data-city') || '';
                    const form = document.getElementById('weatherForm');
                    if (form) form.dispatchEvent(new Event('submit'));
                }
            });
        });

        document.querySelectorAll('.subscribe-btn').forEach(btn => {
            btn.addEventListener('click', this.handleSubscribeClick);
        });
    }

    handleWeatherSubmit = async (e) => {
        e.preventDefault();
        const cityInput = document.getElementById('cityInput');
        const unitsSelect = document.getElementById('unitsSelect');
        const weatherResult = document.getElementById('weatherResult');
        const weatherCity = document.getElementById('weatherCity');
        const weatherDesc = document.getElementById('weatherDesc');
        const weatherTemp = document.getElementById('weatherTemp');
        const weatherMeta = document.getElementById('weatherMeta');
        const weatherError = document.getElementById('weatherError');

        if (!cityInput) return;

        weatherError && weatherError.classList.add('d-none');
        weatherResult && weatherResult.classList.add('d-none');

        const city = cityInput.value.trim();
        const units = unitsSelect ? unitsSelect.value : 'metric';

        if (!city) return;

        try {
            const { loc, wx } = await this.fetchWeather(city, units);
            const current = wx.current;
            const unitSymbol = units === 'imperial' ? '°F' : '°C';

            weatherCity && (weatherCity.textContent = `${loc.name}, ${loc.country}`);
            weatherDesc && (weatherDesc.textContent = this.weatherCodeToText(current.weather_code));
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

    async fetchWeather(city, units) {
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

    weatherCodeToText(code) {
        const weatherMap = {
            0: 'Clear', 1: 'Mainly Clear', 2: 'Partly Cloudy', 3: 'Overcast',
            45: 'Fog', 48: 'Depositing Rime Fog',
            51: 'Drizzle Light', 53: 'Drizzle Moderate', 55: 'Drizzle Dense',
            61: 'Rain Slight', 63: 'Rain Moderate', 65: 'Rain Heavy',
            71: 'Snow Slight', 73: 'Snow Moderate', 75: 'Snow Heavy',
            95: 'Thunderstorm', 96: 'Thunderstorm with Hail', 99: 'Thunderstorm with Heavy Hail'
        };
        return weatherMap[code] || 'Unknown';
    }

    handleSubscribeClick = (e) => {
        e.preventDefault();
        const plan = e.target.getAttribute('data-plan');
        const rawPrice = e.target.getAttribute('data-price');
        const currency = e.target.getAttribute('data-currency') || 'BAM';
        const price = (rawPrice || '').replace('.', ',');
        const email = this.getSession();

        if (!email) {
            this.openLoginModal();
            return;
        }

        const paymentPlan = document.getElementById('paymentPlan');
        if (paymentPlan) paymentPlan.value = `${plan.toUpperCase()} plan - ${currency} ${price}/mo`;

        const modalEl = document.getElementById('paymentModal');
        if (modalEl) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    }

    openLoginModal() {
        const modalEl = document.getElementById('authModal');
        if (modalEl) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
            const loginTabBtn = document.querySelector('#authTabs button#login-tab');
            if (loginTabBtn) new bootstrap.Tab(loginTabBtn).show();
        }
    }


    initializeAuth() {
        // Auth logic from original scripts.js
        const loginForm = document.getElementById('loginForm');
        const signupForm = document.getElementById('signupForm');
        const paymentForm = document.getElementById('paymentForm');

        if (loginForm) {
            loginForm.addEventListener('submit', this.handleLogin);
        }
        if (signupForm) {
            signupForm.addEventListener('submit', this.handleSignup);
        }
        if (paymentForm) {
            paymentForm.addEventListener('submit', this.handlePayment);
        }

        // Initialize subscription form
        const contactForm = document.getElementById('contactForm');
        if (contactForm) {
            contactForm.addEventListener('submit', this.handleSubscription);
        }
    }

    handleLogin = async (e) => {
        e.preventDefault();
        const email = document.getElementById('loginEmail')?.value.trim();
        const password = document.getElementById('loginPassword')?.value;
        const error = document.getElementById('loginError');

        if (error) error.classList.add('d-none');

        if (!email || !password) {
            if (error) {
                error.textContent = 'Please enter email and password.';
                error.classList.remove('d-none');
            }
            return;
        }

        try {
            const result = await this.apiCall('/api/users/login', 'POST', {
                email: email,
                password: password
            });

            if (result.data) {
                // Store user data and JWT token
                const userData = result.data.user || result.data;
                const token = result.data.token;

                this.currentUser = userData;
                localStorage.setItem('jwt_token', token);
                localStorage.setItem('userId', userData.id);
                localStorage.setItem('userEmail', userData.email);
                localStorage.setItem('isAdmin', userData.is_admin ? 'true' : 'false');

                const modalEl = document.getElementById('authModal');
                if (modalEl) {
                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.hide();
                }
                this.updateAuthUI();

                // Redirect to dashboard after login
                window.location.hash = '#dashboard';
            }
        } catch (err) {
            if (error) {
                error.textContent = err.message || 'Invalid credentials.';
                error.classList.remove('d-none');
            }
        }
    }

    handleSignup = async (e) => {
        e.preventDefault();
        const email = document.getElementById('signupEmail')?.value.trim();
        const password = document.getElementById('signupPassword')?.value;
        const error = document.getElementById('signupError');
        const success = document.getElementById('signupSuccess');

        if (error) error.classList.add('d-none');
        if (success) success.classList.add('d-none');

        if (!this.validateEmail(email) || !password || password.length < 6) {
            if (error) {
                error.textContent = 'Invalid email or password too short (min 6 characters).';
                error.classList.remove('d-none');
            }
            return;
        }

        try {
            const nameParts = email.split('@')[0].split('.');
            const fname = nameParts[0] || 'User';
            const lname = nameParts[1] || '';

            await this.apiCall('/api/users/register', 'POST', {
                fname: fname,
                lname: lname,
                email: email,
                password: password
            });

            if (success) {
                success.textContent = 'Account created successfully! You can log in now.';
                success.classList.remove('d-none');
            }

            document.getElementById('signupEmail').value = '';
            document.getElementById('signupPassword').value = '';
        } catch (err) {
            if (error) {
                error.textContent = err.message || 'Failed to create account.';
                error.classList.remove('d-none');
            }
        }
    }

    handlePayment = async (e) => {
        e.preventDefault();
        const fullName = document.getElementById('payerFullName')?.value.trim();
        const nameOnCard = document.getElementById('cardName')?.value.trim();
        const planText = document.getElementById('paymentPlan')?.value || '';
        const error = document.getElementById('paymentError');
        const success = document.getElementById('paymentSuccess');

        if (error) error.classList.add('d-none');
        if (success) success.classList.add('d-none');

        if (!fullName || !nameOnCard) {
            if (error) {
                error.textContent = 'Please enter your full name and card name.';
                error.classList.remove('d-none');
            }
            return;
        }

        if (!this.currentUser || !this.currentUser.id) {
            if (error) {
                error.textContent = 'Please log in first.';
                error.classList.remove('d-none');
            }
            return;
        }

        try {
            const planMatch = planText.match(/(BASIC|PRO)/i);
            const planType = planMatch ? planMatch[1].toLowerCase() : 'basic';
            const price = planType === 'pro' ? 9.99 : 4.99;

            const endDate = new Date();
            endDate.setMonth(endDate.getMonth() + 1);

            const subscriptionResult = await this.apiCall('/api/subscriptions', 'POST', {
                user_id: this.currentUser.id,
                lasts_until: endDate.toISOString().slice(0, 19).replace('T', ' ')
            });

            if (subscriptionResult.data) {
                const subscriptionId = typeof subscriptionResult.data === 'number'
                    ? subscriptionResult.data
                    : (subscriptionResult.data.id || subscriptionResult.data);

                await this.apiCall('/api/payments', 'POST', {
                    subscription_id: subscriptionId,
                    payment_method: 'credit_card',
                    amount: price,
                    card_number: nameOnCard.substring(0, 4) + '****',
                    bank_transaction_id: 'TXN' + Date.now()
                });

                if (success) {
                    success.textContent = 'Payment successful! Subscription activated.';
                    success.classList.remove('d-none');
                }

                const modalEl = document.getElementById('paymentModal');
                if (modalEl) {
                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    setTimeout(() => {
                        modal.hide();
                        document.getElementById('paymentForm').reset();
                    }, 2000);
                }
            }
        } catch (err) {
            if (error) {
                error.textContent = err.message || 'Payment failed. Please try again.';
                error.classList.remove('d-none');
            }
        }
    }

    handleSubscription = (e) => {
        e.preventDefault();
        const emailInput = document.getElementById('emailAddress');
        const submitButton = document.getElementById('submitButton');
        const submitSuccessMessage = document.getElementById('submitSuccessMessage');
        const submitErrorMessage = document.getElementById('submitErrorMessage');

        if (submitButton) submitButton.classList.remove('disabled');

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
    }

    validateEmail(email) {
        return /.+@.+\..+/.test(email);
    }

    getSession() {
        return localStorage.getItem('userEmail');
    }

    isAdmin() {
        return localStorage.getItem('isAdmin') === 'true';
    }

    isAuthenticated() {
        return !!localStorage.getItem('jwt_token');
    }

    updateAuthUI() {
        const email = this.getSession();
        const isAdmin = this.isAdmin();
        const isAuthenticated = this.isAuthenticated();
        let nav = document.querySelector('#mainNav .navbar-nav');
        if (!nav) return;

        // Remove existing auth elements
        const existingBadge = document.getElementById('authBadge');
        const existingLogout = document.getElementById('logoutNav');
        const existingDashboard = document.getElementById('dashboardNav');
        const existingAdmin = document.getElementById('adminNav');

        if (existingBadge) existingBadge.parentElement.remove();
        if (existingLogout) existingLogout.remove();
        if (existingDashboard) existingDashboard.remove();
        if (existingAdmin) existingAdmin.remove();

        if (isAuthenticated && email) {
            // User badge
            const badgeLi = document.createElement('li');
            badgeLi.className = 'nav-item';
            badgeLi.innerHTML = `<span class="nav-link" id="authBadge">${email}</span>`;
            nav.appendChild(badgeLi);

            // Dashboard link
            const dashboardLi = document.createElement('li');
            dashboardLi.className = 'nav-item';
            dashboardLi.id = 'dashboardNav';
            dashboardLi.innerHTML = '<a class="nav-link" href="#dashboard">Dashboard</a>';
            nav.appendChild(dashboardLi);

            // Admin panel link (only for admins)
            if (isAdmin) {
                const adminLi = document.createElement('li');
                adminLi.className = 'nav-item';
                adminLi.id = 'adminNav';
                adminLi.innerHTML = '<a class="nav-link" href="#admin">Admin Panel</a>';
                nav.appendChild(adminLi);
            }

            // Logout link
            const logoutLi = document.createElement('li');
            logoutLi.className = 'nav-item';
            logoutLi.id = 'logoutNav';
            logoutLi.innerHTML = '<a class="nav-link" href="#" id="logoutLink">Logout</a>';
            nav.appendChild(logoutLi);

            document.getElementById('logoutLink').addEventListener('click', (e) => {
                e.preventDefault();
                this.logout();
            });
        } else {
            // Show login link when not authenticated
            const loginNavItem = document.getElementById('loginNavItem');
            if (loginNavItem) {
                loginNavItem.classList.remove('d-none');
            }
        }

        // Hide login link when authenticated
        if (isAuthenticated) {
            const loginNavItem = document.getElementById('loginNavItem');
            if (loginNavItem) {
                loginNavItem.classList.add('d-none');
            }
        }
    }

    async loadDashboard() {
        const userId = localStorage.getItem('userId');
        if (!userId) {
            window.location.hash = '#home';
            return;
        }

        try {
            // Load user profile
            const userResult = await this.apiCall('/api/users/me');
            if (userResult.data) {
                const profileDiv = document.getElementById('userProfile');
                if (profileDiv) {
                    profileDiv.innerHTML = `
                        <p><strong>Name:</strong> ${userResult.data.fname} ${userResult.data.lname}</p>
                        <p><strong>Email:</strong> ${userResult.data.email}</p>
                        <p><strong>Role:</strong> ${userResult.data.is_admin ? 'Admin' : 'User'}</p>
                    `;
                }
            }

            // Load active subscription
            try {
                const subResult = await this.apiCall(`/api/subscriptions/user/${userId}/active`);
                const subDiv = document.getElementById('activeSubscription');
                if (subDiv) {
                    if (subResult.data) {
                        subDiv.innerHTML = `
                            <p><strong>Status:</strong> Active</p>
                            <p><strong>Expires:</strong> ${new Date(subResult.data.lasts_until).toLocaleDateString()}</p>
                        `;
                    } else {
                        subDiv.innerHTML = '<p class="text-muted">No active subscription</p>';
                    }
                }
            } catch (e) {
                const subDiv = document.getElementById('activeSubscription');
                if (subDiv) {
                    subDiv.innerHTML = '<p class="text-muted">No active subscription</p>';
                }
            }

            // Load saved filters
            try {
                const filtersResult = await this.apiCall(`/api/saved-filters/user/${userId}`);
                const filtersDiv = document.getElementById('savedFiltersList');
                if (filtersDiv) {
                    if (filtersResult.data && filtersResult.data.length > 0) {
                        filtersDiv.innerHTML = filtersResult.data.map(filter => `
                            <div class="card mb-2">
                                <div class="card-body">
                                    <h6>Filter #${filter.id}</h6>
                                    <p class="mb-0">City ID: ${filter.city_id} | Days: ${filter.forecast_days || 'N/A'}</p>
                                </div>
                            </div>
                        `).join('');
                    } else {
                        filtersDiv.innerHTML = '<p class="text-muted">No saved filters</p>';
                    }
                }
            } catch (e) {
                const filtersDiv = document.getElementById('savedFiltersList');
                if (filtersDiv) filtersDiv.innerHTML = '<p class="text-muted">No saved filters</p>';
            }

            // Load payment history
            try {
                const paymentsResult = await this.apiCall(`/api/payments/user/${userId}`);
                const paymentsDiv = document.getElementById('paymentHistory');
                if (paymentsDiv) {
                    if (paymentsResult.data && paymentsResult.data.length > 0) {
                        paymentsDiv.innerHTML = `
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${paymentsResult.data.map(payment => `
                                        <tr>
                                            <td>$${payment.amount}</td>
                                            <td>${payment.payment_method}</td>
                                            <td>${new Date(payment.created_at).toLocaleDateString()}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        `;
                    } else {
                        paymentsDiv.innerHTML = '<p class="text-muted">No payment history</p>';
                    }
                }
            } catch (e) {
                const paymentsDiv = document.getElementById('paymentHistory');
                if (paymentsDiv) paymentsDiv.innerHTML = '<p class="text-muted">No payment history</p>';
            }

            const loadingEl = document.getElementById('dashboardLoading');
            const contentEl = document.getElementById('dashboardContent');
            if (loadingEl) loadingEl.classList.add('d-none');
            if (contentEl) contentEl.classList.remove('d-none');
        } catch (error) {
            const errorEl = document.getElementById('dashboardError');
            const loadingEl = document.getElementById('dashboardLoading');
            if (errorEl) {
                errorEl.textContent = error.message;
                errorEl.classList.remove('d-none');
            }
            if (loadingEl) loadingEl.classList.add('d-none');
        }
    }

    async loadAdminPanel() {
        if (!this.isAdmin()) {
            alert('Admin access required.');
            window.location.hash = '#dashboard';
            return;
        }

        try {
            // Load all users
            const usersResult = await this.apiCall('/api/users');
            const usersDiv = document.getElementById('usersList');
            if (usersDiv && usersResult.data) {
                usersDiv.innerHTML = `
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${usersResult.data.map(user => `
                                <tr>
                                    <td>${user.id}</td>
                                    <td>${user.fname} ${user.lname}</td>
                                    <td>${user.email}</td>
                                    <td>${user.is_admin ? '<span class="badge bg-danger">Admin</span>' : '<span class="badge bg-secondary">User</span>'}</td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="alert('Edit feature coming soon')">Edit</button>
                                        <button class="btn btn-sm btn-danger" onclick="window.routerInstance?.deleteUser(${user.id})">Delete</button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                `;
            }

            // Load all cities
            const citiesResult = await this.apiCall('/api/cities');
            const citiesDiv = document.getElementById('citiesList');
            if (citiesDiv && citiesResult.data) {
                citiesDiv.innerHTML = `
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Zip Code</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${citiesResult.data.map(city => `
                                <tr>
                                    <td>${city.id}</td>
                                    <td>${city.name}</td>
                                    <td>${city.zip_code || 'N/A'}</td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="alert('Edit feature coming soon')">Edit</button>
                                        <button class="btn btn-sm btn-danger" onclick="window.routerInstance?.deleteCity(${city.id})">Delete</button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                `;
            }

            // Load subscriptions
            const subsResult = await this.apiCall('/api/subscriptions');
            const subsDiv = document.getElementById('subscriptionsList');
            if (subsDiv && subsResult.data) {
                subsDiv.innerHTML = `
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User ID</th>
                                <th>Until</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${subsResult.data.map(sub => `
                                <tr>
                                    <td>${sub.id}</td>
                                    <td>${sub.user_id}</td>
                                    <td>${new Date(sub.lasts_until).toLocaleDateString()}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                `;
            }

            // Load payments
            const paymentsResult = await this.apiCall('/api/payments');
            const paymentsDiv = document.getElementById('paymentsList');
            if (paymentsDiv && paymentsResult.data) {
                paymentsDiv.innerHTML = `
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${paymentsResult.data.map(p => `
                                <tr>
                                    <td>${p.id}</td>
                                    <td>$${p.amount}</td>
                                    <td>${p.payment_method}</td>
                                    <td>${new Date(p.created_at).toLocaleDateString()}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                `;
            }

            // Load Saved Filters
            const filtersResult = await this.apiCall('/api/saved-filters');
            const filtersDiv = document.getElementById('filtersList');
            if (filtersDiv && filtersResult.data) {
                filtersDiv.innerHTML = `
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User ID</th>
                                <th>City ID</th>
                                <th>Days</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${filtersResult.data.map(f => `
                                <tr>
                                    <td>${f.id}</td>
                                    <td>${f.user_id}</td>
                                    <td>${f.city_id}</td>
                                    <td>${f.forecast_days}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                `;
            }

            const loadingEl = document.getElementById('adminLoading');
            const contentEl = document.getElementById('adminContent');
            if (loadingEl) loadingEl.classList.add('d-none');
            if (contentEl) contentEl.classList.remove('d-none');
        } catch (error) {
            const errorEl = document.getElementById('adminError');
            const loadingEl = document.getElementById('adminLoading');
            if (errorEl) {
                errorEl.textContent = error.message;
                errorEl.classList.remove('d-none');
            }
            if (loadingEl) loadingEl.classList.add('d-none');
        }
    }

    // Admin Actions
    async deleteUser(id) {
        if (!confirm('Are you sure you want to delete this user?')) return;
        try {
            await this.apiCall(`/api/users/${id}`, 'DELETE');
            this.loadAdminPanel(); // Refresh
        } catch (error) {
            alert('Failed to delete user: ' + error.message);
        }
    }

    async deleteCity(id) {
        if (!confirm('Are you sure you want to delete this city?')) return;
        try {
            await this.apiCall(`/api/cities/${id}`, 'DELETE');
            this.loadAdminPanel(); // Refresh
        } catch (error) {
            alert('Failed to delete city: ' + error.message);
        }
    }
}

// Initialize router when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.routerInstance = new SPARouter();
});
