# Weather App Database Schema (Initial Draft)

## Entity-Relationship Diagram (ERD)


### Entities (5):

1. **Users**
   - id (Primary Key)
   - fname (First Name)
   - lname (Last Name)
   - email
   - pass (Password)
   - is_admin (Boolean flag)

2. **Cities**
   - id (Primary Key)
   - zip_code
   - name

3. **Saved_filters**
   - id (Primary Key)
   - user_id (Foreign Key → Users.id)
   - city_id (Foreign Key → Cities.id)
   - forecast_days
   - min_temp_selected
   - max_temp_selected
   - avg_temp_selected
   - weather_cond_selected (Weather condition selected)

4. **Subscriptions**
   - id (Primary Key)
   - user_id (Foreign Key → Users.id)
   - created_at
   - lasts_until

5. **Payments**
   - id (Primary Key)
   - subscription_id (Foreign Key → Subscriptions.id)
   - payment_method
   - amount
   - card_number
   - bank_transaction_id

## Relationships:

- Users (1) → (M) Saved_filters
- Users (1) → (M) Subscriptions
- Cities (1) → (M) Saved_filters
- Subscriptions (1) → (M) Payments

## Database Design Notes:

- Foreign key constraints ensure data integrity
- Users can have multiple saved filters and subscriptions
- Cities can be associated with multiple saved filters
- Subscriptions can have multiple payments
- Admin flag allows for user role management
- Saved filters store user preferences for weather conditions


