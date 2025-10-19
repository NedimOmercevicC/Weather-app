<<<<<<< HEAD
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

## Technical Implementation

### SPA Router
- Hash-based navigation (`#weather`, `#about`, etc.)
- Dynamic content loading without page reloads
- Component re-initialization after route changes
- Event delegation for dynamic content

### Authentication
- Local storage-based user management
- Session persistence
- Form validation
- Modal-based UI

### Weather Integration
- Open-Meteo API for weather data
- Geocoding for city lookup
- Unit conversion (metric/imperial)
- Error handling and user feedback

## Next Steps (Future Milestones)

1. **Backend API Development**
   - RESTful API endpoints
   - Database integration
   - User authentication middleware
   - Weather data caching

2. **Payment Integration**
   - Stripe/PayPal integration
   - Subscription management
   - Payment history tracking

3. **Advanced Features**
   - Weather alerts and notifications
   - User preferences and settings
   - Historical weather data
   - Mobile app development

## Setup Instructions

1. Clone the repository
2. Open `frontend/views/index.html` in a web browser
3. Navigate using the navbar links (no page reloads; SPA loads views dynamically)
4. Test weather search and user authentication (localStorage only; no backend)
5. Try subscription flow (payment simulation; BAM formatting)

## Dependencies

- Bootstrap 5.2.3
- Font Awesome 6.3.0
- Google Fonts (Varela Round, Nunito)
- Open-Meteo API (no API key required)



=======
# Weather-app
Weather
>>>>>>> 46542cd1599f909366698a8f87bd0a3b4f08e714
