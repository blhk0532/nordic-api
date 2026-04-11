# Booking Calendar System

## Overview

The Booking Calendar system is a comprehensive solution for managing service bookings across multiple locations in Sweden. It's designed for call center agents to book service jobs for service technicians.

## Features

### 1. Calendar Interface
- **View**: Full calendar view with day, week, and month views
- **Time Slots**: Customizable time slots (default 6:00 AM - 10:00 PM)
- **Location Display**: Shows location in the all-day row above the calendar
- **Real-time Updates**: Calendar refreshes automatically after creating, editing, or deleting bookings

### 2. Booking Management
- **Create Bookings**: Click on a time slot to create a new booking
- **Edit Bookings**: Click on existing bookings to edit details
- **Delete Bookings**: Remove bookings from the calendar
- **Status Tracking**: Track booking status (New, Booked, Confirmed, Processing, Cancelled, Updated, Complete)

### 3. Database Structure

#### booking_locations
Stores location information for service bookings:
- `name`: Location name
- `code`: Unique location code
- `address`, `city`, `postal_code`, `country`: Address information
- `phone`, `email`: Contact information
- `is_active`: Whether the location is currently active
- `settings`: JSON field for additional settings

#### booking_schedules
Defines available time slots for each location:
- `booking_location_id`: Foreign key to locations
- `date`: Schedule date
- `start_time`, `end_time`: Available hours
- `is_available`: Whether the schedule is active
- `max_bookings`: Maximum number of bookings allowed
- `notes`: Additional schedule notes

#### booking_bookings (Modified)
Enhanced booking table with new fields:
- **New Fields**:
  - `booking_location_id`: Location reference
  - `service_date`: Date of service
  - `start_time`, `end_time`: Service time window
  - `service_note`: Notes specific to the service
  - `is_active`: Active status
  - `notified_at`, `confirmed_at`, `completed_at`: Timestamp tracking
- **Removed Fields**:
  - `shipping_price`, `shipping_method` (replaced by time fields)
- **Defaults**:
  - `currency`: 'SEK' (Swedish Kronor)
  - `country`: 'Sweden'

### 4. User Roles

#### Agents (Call Center)
- Create bookings on behalf of clients
- Tracked via `booking_user_id` field
- Can view and manage all bookings

#### Service Technicians
- Users with `role='service'`
- Assigned to bookings via `service_user_id`
- Perform the actual service work

### 5. Client Management
- **Auto-creation**: New clients are automatically created when making a booking
- **Client Fields**: Name, email, phone, address, city, postal_code, country
- **Default Country**: Sweden

## Usage

### Accessing the Calendar
Navigate to **Bookings > Booking Calendar** in the Filament admin panel.

### Creating a Booking
1. Click on a time slot in the calendar
2. Fill in the booking form:
   - **Client**: Select existing or create new client
   - **Service**: Choose the service type
   - **Location**: Select the location (required)
   - **Service Technician**: Assign a technician (optional)
   - **Service Date & Time**: Set the date and time window
   - **Status**: Set initial status (defaults to 'New')
   - **Total Price**: Optional pricing in SEK
   - **Notes**: General notes and service-specific notes
3. Click "Create" to save the booking

### Editing a Booking
1. Click on an existing booking in the calendar
2. Modify the fields as needed
3. Click "Save" to update

### Tracking Bookings
The system tracks:
- **Who booked it**: Agent user (`booking_user_id`)
- **For which location**: Location reference
- **Assigned to whom**: Service technician (`service_user_id`)
- **Timestamps**: Created, notified, confirmed, and completed dates

## Technical Details

### Models
- `BookingLocation`: Location model
- `BookingSchedule`: Schedule model
- `Booking`: Enhanced booking model with calendar support

### Widget
- `BookingCalendarWidget`: Main calendar widget extending FullCalendarWidget

### Page
- `BookingCalendar`: Dedicated page for the booking calendar

### Actions
- `CreateAction`: Create new bookings
- `EditAction`: Edit existing bookings
- `DeleteAction`: Remove bookings
- `ViewAction`: View booking details

## Configuration

### Calendar Settings
Default settings in `BookingCalendarWidget::config()`:
- Initial view: Week view
- Time range: 06:00 - 22:00
- Slot duration: 30 minutes
- All-day text: "Location"

### Customization
Modify the `config()` method in `BookingCalendarWidget.php` to adjust:
- Time ranges
- Slot durations
- View options
- Calendar behavior

## Sample Data
Sample locations and schedules have been created:
- Stockholm - City
- Gothenburg - Central
- Malmö - South

Each location has schedules for the next 14 weekdays (Monday-Friday, 08:00-20:00).

## Next Steps
1. Test the calendar interface in the browser
2. Create test bookings to verify functionality
3. Adjust styling and layout as needed
4. Configure notifications for booking events
5. Add role-based permissions for agents and technicians
