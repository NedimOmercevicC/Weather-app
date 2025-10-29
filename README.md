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
- ✅ User authentication (login/signup)
- ✅ Subscription plans with payment modal (currency: BAM)
- ✅ Email subscription form
- ✅ Quick city buttons for free users

### Database Schema
- ✅ 5 entities identified (Users, Cities, Saved_filters, Subscriptions, Payments)
- ✅ ERD with relationships documented in database_schema.md
- ✅ Foreign key constraints planned

## Navigation Structure

- **Home** (`#home`) - Landing page with hero section
- **Weather** (`#weather`) - Weather search and display
- **About** (`#about`) - Company mission and features
- **Subscription** (`#pricing`) - Pricing plans
- **Contact** (`#signup`) - Newsletter signup and contact info
- **Login/Signup** - Modal-based authentication

