/*!
* Start Bootstrap - Grayscale v7.0.6 (https://startbootstrap.com/theme/grayscale)
* Copyright 2013-2023 Start Bootstrap
* Licensed under MIT (https://github.com/StartBootstrap/startbootstrap-grayscale/blob/master/LICENSE)
*/
//
// Scripts
// 

// --- Configuration ---
const API_BASE_URL = 'http://localhost/weather/backend/api';

// --- State Management ---
const AppState = {
    user: null,
    token: localStorage.getItem('jwt_token'),

    isAuthenticated() {
        return !!this.token;
    },

    setToken(token) {
        this.token = token;
        if (token) {
            localStorage.setItem('jwt_token', token);
        } else {
            localStorage.removeItem('jwt_token');
        }
    },

    logout() {
        this.setToken(null);
        this.user = null;
        ViewManager.showLanding();
        AuthUI.update();
    }
};

// --- API Service ---
const ApiService = {
    async request(endpoint, method = 'GET', body = null) {
        const headers = {
            'Content-Type': 'application/json'
        };
        if (AppState.token) {
            headers['Authorization'] = `Bearer ${AppState.token}`;
        }

        const config = {
            method,
            headers
        };
        if (body) {
            config.body = JSON.stringify(body);
        }

        try {
            const response = await fetch(`${API_BASE_URL}${endpoint}`, config);

            if (response.status === 401) {
                // Token expired or invalid
                AppState.logout();
                throw new Error('Session expired. Please login again.');
            }

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'API request failed');
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    async login(email, password) {
        return this.request('/users/login', 'POST', { email, password });
    },

    async register(fname, lname, email, password) {
        return this.request('/users/register', 'POST', { fname, lname, email, password });
    },

    async getMe() {
        return this.request('/users/me');
    },

    async getUsers() {
        return this.request('/users');
    }
};

// --- View Manager ---
const ViewManager = {
    views: {
        landing: document.getElementById('landing-page'),
        dashboard: document.getElementById('dashboard-page'),
        admin: document.getElementById('admin-page')
    },

    hideAll() {
        Object.values(this.views).forEach(el => {
            if (el) el.classList.add('d-none');
        });
    },

    showLanding() {
        this.hideAll();
        if (this.views.landing) this.views.landing.classList.remove('d-none');
        window.location.hash = '';
    },

    async showDashboard() {
        if (!AppState.isAuthenticated()) {
            this.showLanding();
            return;
        }
        this.hideAll();
        if (this.views.dashboard) this.views.dashboard.classList.remove('d-none');

        // Load Dashboard Data
        await DashboardController.loadData();
    },

    async showAdmin() {
        if (!AppState.isAuthenticated()) {
            this.showLanding();
            return;
        }
        // Basic check, backend will enforce security
        if (AppState.user && !AppState.user.is_admin) {
            alert("Access Denied: You are not an admin.");
            this.showDashboard();
            return;
        }

        this.hideAll();
        if (this.views.admin) this.views.admin.classList.remove('d-none');

        // Load Admin Data
        await AdminController.loadData();
    }
};

// --- Controllers ---
const DashboardController = {
    async loadData() {
        try {
            const user = await ApiService.getMe();
            AppState.user = user; // Update local user state

            const profileContainer = document.getElementById('userProfile');
            if (profileContainer) {
                profileContainer.innerHTML = `
                    <p><strong>Name:</strong> ${user.fname} ${user.lname}</p>
                    <p><strong>Email:</strong> ${user.email}</p>
                    <p><strong>Role:</strong> ${user.is_admin ? '<span class="badge bg-danger">Admin</span>' : '<span class="badge bg-secondary">User</span>'}</p>
                    <p><strong>Joined:</strong> ${new Date(user.created_at).toLocaleDateString()}</p>
                `;
            }

            // Placeholder for other dashboard widgets
            document.getElementById('activeSubscription').innerHTML = '<p class="text-muted">No active subscription found.</p>';
            document.getElementById('paymentHistory').innerHTML = '<p class="text-muted">No payment history.</p>';

        } catch (error) {
            console.error("Failed to load dashboard:", error);
            // Optionally show error UI
        }
    }
};

const AdminController = {
    async loadData() {
        await this.loadUsers();
    },

    async loadUsers() {
        const listContainer = document.getElementById('usersList');
        if (!listContainer) return;

        try {
            listContainer.innerHTML = '<div class="spinner-border spinner-border-sm"></div> Loading...';
            const users = await ApiService.getUsers();

            if (users.length === 0) {
                listContainer.innerHTML = '<p>No users found.</p>';
                return;
            }

            let html = '<div class="table-responsive"><table class="table table-striped"><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Actions</th></tr></thead><tbody>';

            users.forEach(u => {
                html += `
                    <tr>
                        <td>${u.id}</td>
                        <td>${u.fname} ${u.lname}</td>
                        <td>${u.email}</td>
                        <td>${u.is_admin ? '<span class="badge bg-danger">Admin</span>' : 'User'}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="alert('Edit user ${u.id}')">Edit</button>
                        </td>
                    </tr>
                `;
            });

            html += '</tbody></table></div>';
            listContainer.innerHTML = html;

        } catch (error) {
            listContainer.innerHTML = `<div class="alert alert-danger">Error loading users: ${error.message}</div>`;
        }
    }
};

// --- Auth UI Handler ---
const AuthUI = {
    init() {
        // Login Form
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const email = document.getElementById('loginEmail').value;
                const password = document.getElementById('loginPassword').value;
                const errorDiv = document.getElementById('loginError');

                try {
                    const response = await ApiService.login(email, password);
                    AppState.setToken(response.token);
                    AppState.user = response.user;

                    // Close modal
                    const modalEl = document.getElementById('authModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();

                    // Update UI and redirect
                    this.update();
                    ViewManager.showDashboard();
                } catch (err) {
                    if (errorDiv) {
                        errorDiv.textContent = err.message;
                        errorDiv.classList.remove('d-none');
                    }
                }
            });
        }

        // Signup Form
        const signupForm = document.getElementById('signupForm');
        if (signupForm) {
            signupForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const email = document.getElementById('signupEmail').value;
                const password = document.getElementById('signupPassword').value;
                // Quick hack for names since the simple form only has email/pass
                // ideally we'd add name fields to the form
                const fname = "New";
                const lname = "User";

                const errorDiv = document.getElementById('signupError');
                const successDiv = document.getElementById('signupSuccess');

                try {
                    await ApiService.register(fname, lname, email, password);
                    if (successDiv) successDiv.classList.remove('d-none');
                    if (errorDiv) errorDiv.classList.add('d-none');
                    signupForm.reset();
                } catch (err) {
                    if (errorDiv) {
                        errorDiv.textContent = err.message;
                        errorDiv.classList.remove('d-none');
                    }
                }
            });
        }

        this.update();
    },

    update() {
        const nav = document.querySelector('#mainNav .navbar-nav');
        if (!nav) return;

        // Remove existing dynamic items
        const existingBadge = document.getElementById('authBadge');
        if (existingBadge) existingBadge.remove();

        const existingLogout = document.getElementById('logoutNav');
        if (existingLogout) existingLogout.remove();

        const existingDash = document.getElementById('dashNav');
        if (existingDash) existingDash.remove();

        const existingAdmin = document.getElementById('adminNav');
        if (existingAdmin) existingAdmin.remove();


        if (AppState.isAuthenticated()) {
            // Add Dashboard Link
            const dashLi = document.createElement('li');
            dashLi.className = 'nav-item';
            dashLi.id = 'dashNav';
            dashLi.innerHTML = '<a class="nav-link" href="#" onclick="ViewManager.showDashboard(); return false;">Dashboard</a>';
            nav.appendChild(dashLi);

            // Add Admin Link (if admin) - we might need to check user object if loaded
            // For now, always show if there's a token, verify click later or relies on AppState.user
            if (AppState.user && AppState.user.is_admin) {
                const adminLi = document.createElement('li');
                adminLi.className = 'nav-item';
                adminLi.id = 'adminNav';
                adminLi.innerHTML = '<a class="nav-link" href="#" onclick="ViewManager.showAdmin(); return false;">Admin</a>';
                nav.appendChild(adminLi);
            }

            // Add Logout
            const logoutLi = document.createElement('li');
            logoutLi.className = 'nav-item';
            logoutLi.id = 'logoutNav';
            logoutLi.innerHTML = '<a class="nav-link" href="#" onclick="AppState.logout(); return false;">Logout</a>';
            nav.appendChild(logoutLi);

            // Hide Login Modal Trigger link (the last fixed one)
            // It's a bit tricky to find by selector without ID, assuming it's the one with data-bs-target="#authModal"
            const loginTrigger = document.querySelector('[data-bs-target="#authModal"]');
            if (loginTrigger && loginTrigger.parentElement) {
                loginTrigger.parentElement.classList.add('d-none');
            }

        } else {
            // Show Login Modal Trigger link
            const loginTrigger = document.querySelector('[data-bs-target="#authModal"]');
            if (loginTrigger && loginTrigger.parentElement) {
                loginTrigger.parentElement.classList.remove('d-none');
            }
        }
    }
};

// --- Initialization ---
window.addEventListener('DOMContentLoaded', event => {
    // 1. Navbar Logic (Original)
    var navbarShrink = function () {
        const navbarCollapsible = document.body.querySelector('#mainNav');
        if (!navbarCollapsible) return;
        if (window.scrollY === 0) {
            navbarCollapsible.classList.remove('navbar-shrink')
        } else {
            navbarCollapsible.classList.add('navbar-shrink')
        }
    };
    navbarShrink();
    document.addEventListener('scroll', navbarShrink);

    // 2. Init Auth
    AuthUI.init();

    // 3. Init View
    // Check if we have a token, if so try to get user and stay on dashboard
    if (AppState.isAuthenticated()) {
        ApiService.getMe().then(user => {
            AppState.user = user;
            AuthUI.update(); // Update nav with admin/dash links
            ViewManager.showDashboard();
        }).catch(err => {
            // Token likely invalid
            AppState.logout();
        });
    } else {
        ViewManager.showLanding();
    }
});

// Original Weather & Subscription Logic (Preserved but adapted)
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

    if (weatherForm) weatherForm.addEventListener('submit', handleWeatherSubmit);

    document.querySelectorAll('.quick-city').forEach(btn => {
        btn.addEventListener('click', function () {
            if (!cityInput) return;
            cityInput.value = this.getAttribute('data-city') || '';
            weatherForm && weatherForm.dispatchEvent(new Event('submit'));
        });
    });
})();

// Expose Helpers globally for onclick events in HTML
window.AppState = AppState;
window.ViewManager = ViewManager;
window.ApiService = ApiService;