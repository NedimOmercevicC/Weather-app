# Weather App Backend - Milestone 2

## Database Setup

### 1. Create Database
Run the SQL file to create the database and tables:
```sql
mysql -u root -p < weather_app_database.sql
```

### 2. Database Configuration
Update `backend/dao/config.php` with your database credentials:
- Host: localhost
- Database: weather_app_db
- Username: root
- Password: (your MySQL password)

## DAO Classes

### BaseDao.php
- Provides basic CRUD operations for all entities
- Methods: getAll(), getById(), insert(), update(), delete(), count()

### UserDao.php
- Manages user accounts
- Methods: getByEmail(), emailExists(), createUser(), updatePassword(), getAdmins()

### CityDao.php
- Manages city data
- Methods: getByName(), getByZipCode(), searchByName(), createCity(), cityExists()

### SavedFilterDao.php
- Manages user weather preferences
- Methods: getByUserId(), getByUserAndCity(), createFilter(), updateFilter(), deleteByUserId()

### SubscriptionDao.php
- Manages user subscriptions
- Methods: getByUserId(), getActiveByUserId(), createSubscription(), hasActiveSubscription()

### PaymentDao.php
- Manages payment records
- Methods: getBySubscriptionId(), getByUserId(), createPayment(), getTotalAmountByUserId()


## Database Schema

5 entities with relationships:
- Users (1) → (M) Saved_filters
- Users (1) → (M) Subscriptions  
- Cities (1) → (M) Saved_filters
- Subscriptions (1) → (M) Payments


