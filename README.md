# Weather Application - First Milestone

## Project Structure

```
weather/
├── backend/
│   ├── routes/          # API route handlers
│   ├── services/        # Business logic services
│   └── dao/            # Data Access Objects
├── frontend/
│   ├── views/           # HTML view templates
│   │   ├── index.html   # Main SPA container
│   │   ├── home.html    # Home page
│   │   ├── weather.html # Weather search page
│   │   ├── about.html   # About page
│   │   ├── pricing.html # Subscription plans
│   │   ├── contact.html # Contact & signup
│   │   ├── login.html   # Auth modals
│   │   └── footer.html  # Footer component
│   ├── css/            # Stylesheets
│   │   └── styles.css  # Main styles
│   ├── js/             # JavaScript files
│   │   ├── scripts.js  # Core functionality
│   │   └── router.js   # SPA router
│   └── assets/         # Static assets
│       ├── favicon.ico
│       └── img/        # Images
├── database_schema.md   # ERD and database design
└── README.md           # This file
```

## Features Implemented

### Frontend (SPA)
- ✅ Single Page Application with hash-based routing
- ✅ Separate view files for each page/component
- ✅ Responsive Bootstrap design
- ✅ Weather search with Open-Meteo API
- ✅ User authentication (login/signup) - **Connected to Backend API**
- ✅ Subscription plans with payment modal (currency: BAM) - **Connected to Backend API**
- ✅ Email subscription form
- ✅ Quick city buttons for free users

### Backend (API)
- ✅ RESTful API with FlightPHP framework
- ✅ Full CRUD operations for all 5 entities
- ✅ Service layer with business logic and validation
- ✅ DAO layer with PDO database access
- ✅ Error handling and proper HTTP status codes
- ✅ CORS enabled for frontend integration
- ✅ API documentation (OpenAPI/Swagger)

### Database Schema
- ✅ 5 entities identified (Users, Cities, Saved_filters, Subscriptions, Payments)
- ✅ ERD with relationships documented in database_schema.md
- ✅ Foreign key constraints planned
- ✅ MySQL database with all tables created

## Navigation Structure

- **Home** (`#home`) - Landing page with hero section
- **Weather** (`#weather`) - Weather search and display
- **About** (`#about`) - Company mission and features
- **Subscription** (`#pricing`) - Pricing plans
- **Contact** (`#signup`) - Newsletter signup and contact info
- **Login/Signup** - Modal-based authentication

## Milestone 3: Frontend-Backend Integration

### What Was Implemented

1. **API Integration**
   - Frontend now connects to backend API at `backend/index.php`
   - All authentication (login/signup) uses backend API endpoints
   - Subscription and payment processing creates records in database
   - Session management using user ID from backend

2. **Authentication Flow**
   - **Signup**: Creates user account via `POST /api/users`
   - **Login**: Authenticates via `POST /api/users/login` with email/password
   - **Session**: Stores user ID and email in localStorage after successful login
   - **Logout**: Clears session data

3. **Subscription & Payment Flow**
   - User selects a plan (Basic/Pro)
   - Payment modal collects name and card details
   - Creates subscription record via `POST /api/subscriptions`
   - Creates payment record via `POST /api/payments`
   - Both records linked to user account

4. **API Methods**
   - `apiCall()` method in router.js handles all API requests
   - Proper error handling and user feedback
   - JSON request/response format

### Testing the Integration

1. **Start Backend Server**
   - Ensure MySQL is running
   - Database `weather_app_db` exists with all tables
   - Access backend at `http://localhost/weather/backend/index.php`

2. **Test Authentication**
   - Click "Login" in navbar
   - Sign up with a new email/password
   - Log in with the created account
   - Verify user email appears in navbar

3. **Test Subscription**
   - Navigate to Subscription page
   - Click "Subscribe" on Basic or Pro plan
   - Fill payment form (name and card name)
   - Submit and verify success message
   - Check database for new subscription and payment records

### API Endpoints Used

- `POST /api/users` - User registration
- `POST /api/users/login` - User authentication
- `GET /api/users/{id}` - Get user by ID (for session check)
- `POST /api/subscriptions` - Create subscription
- `POST /api/payments` - Create payment record

