// SPA Router for Weather Application
class SPARouter {
    constructor() {
        this.apiBaseUrl = 'backend/index.php';
        this.routes = {
            '#home': 'frontend/views/home.html',
            '#weather': 'frontend/views/weather.html',
            '#about': 'frontend/views/about.html',
            '#pricing': 'frontend/views/pricing.html',
            '#signup': 'frontend/views/contact.html'
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
        
        if (data) {
            options.body = JSON.stringify(data);
        }
        
        try {
            const response = await fetch(url, options);
            const result = await response.json();
            
            if (!response.ok || result.error) {
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
        const userId = localStorage.getItem('userId');
        if (userId) {
            try {
                const result = await this.apiCall(`/api/users/${userId}`);
                if (result.data) {
                    this.currentUser = result.data;
                    this.updateAuthUI();
                }
            } catch (error) {
                localStorage.removeItem('userId');
                localStorage.removeItem('userEmail');
            }
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

    getSession() {
        return this.currentUser ? this.currentUser.email : null;
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
                this.currentUser = result.data;
                localStorage.setItem('userId', result.data.id);
                localStorage.setItem('userEmail', result.data.email);
                
                const modalEl = document.getElementById('authModal');
                if (modalEl) {
                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.hide();
                }
                this.updateAuthUI();
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
            
            await this.apiCall('/api/users', 'POST', {
                fname: fname,
                lname: lname,
                email: email,
                pass: password,
                is_admin: false
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

    updateAuthUI() {
        const email = this.getSession();
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
            logoutLi.addEventListener('click', (e) => {
                e.preventDefault();
                this.logout();
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

    logout() {
        this.currentUser = null;
        localStorage.removeItem('userId');
        localStorage.removeItem('userEmail');
        this.updateAuthUI();
    }
}

// Initialize router when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new SPARouter();
});
