# Farm Monitoring System - MVC Architecture

This project has been refactored to follow the Model-View-Controller (MVC) architectural pattern for better code organization, maintainability, and scalability.

## Project Structure

```
mwaba/
├── app/                          # Application core
│   ├── Config/                   # Configuration files
│   │   ├── Database.php         # Database connection class
│   │   ├── Router.php           # URL routing system
│   │   └── App.php              # Application configuration
│   ├── Controllers/             # Business logic controllers
│   │   ├── BaseController.php   # Base controller class
│   │   ├── DashboardController.php
│   │   ├── SensorController.php
│   │   ├── CropController.php
│   │   └── EquipmentController.php
│   ├── Models/                  # Data models
│   │   ├── BaseModel.php        # Base model class
│   │   ├── SensorModel.php
│   │   ├── CropModel.php
│   │   ├── EquipmentModel.php
│   │   └── UserModel.php
│   └── Views/                   # View templates
│       ├── layouts/
│       │   └── main.php         # Main layout template
│       ├── dashboard/
│       │   └── index.php        # Dashboard view
│       ├── crops/
│       │   └── index.php        # Crops management view
│       └── equipment/
│           └── index.php        # Equipment management view
├── public/                      # Public assets
│   └── assets/
│       ├── css/
│       │   └── styles.css       # Main stylesheet
│       ├── js/
│       │   └── script.js        # JavaScript functionality
│       └── images/
│           └── poslogo.png      # Application logo
├── index.php                    # Main entry point
├── data-receiver.php            # Legacy endpoint (redirects to MVC)
├── .htaccess                    # URL rewriting rules
└── README.md                    # This file
```

## Features

### Models
- **BaseModel**: Provides common database operations (CRUD)
- **SensorModel**: Manages sensor data and readings
- **CropModel**: Handles crop area management and health tracking
- **EquipmentModel**: Manages farm equipment status and maintenance
- **UserModel**: User authentication and management

### Controllers
- **BaseController**: Common controller functionality
- **DashboardController**: Main dashboard and data visualization
- **SensorController**: Sensor data reception and API endpoints
- **CropController**: Crop management and health updates
- **EquipmentController**: Equipment status and maintenance tracking

### Views
- **Responsive Design**: Mobile-friendly interface
- **Real-time Updates**: Live sensor data visualization
- **Interactive Charts**: Environmental trends and crop health
- **Modular Layout**: Reusable components and templates

## API Endpoints

### Dashboard
- `GET /` - Main dashboard
- `GET /dashboard` - Dashboard view
- `GET /api/dashboard/latest` - Latest sensor data
- `GET /api/dashboard/charts` - Chart data

### Sensors
- `POST /api/sensors/data` - Receive sensor data
- `GET /api/sensors` - Get all sensors
- `GET /api/sensors/{id}/readings` - Get sensor readings
- `GET /api/sensors/latest` - Latest readings

### Crops
- `GET /crops` - Crop management page
- `GET /api/crops` - Get all crops
- `GET /api/crops/health` - Crop health data
- `POST /api/crops/update-health` - Update crop health
- `POST /api/crops` - Create new crop

### Equipment
- `GET /equipment` - Equipment management page
- `GET /api/equipment` - Get all equipment
- `POST /api/equipment/update-status` - Update equipment status
- `POST /api/equipment/update-maintenance` - Update maintenance record

## Database Schema

The system uses the following main tables:
- `sensors` - Sensor device information
- `sensor_readings` - Sensor data readings
- `crop_areas` - Crop area management
- `equipment` - Farm equipment tracking
- `users` - User management

## Installation

1. Ensure you have XAMPP or similar PHP/MySQL environment
2. Import the `farm_monitoring.sql` database
3. Update database credentials in `app/Config/Database.php` if needed
4. Place files in your web server directory
5. Access the application through your web browser

## Legacy Compatibility

The original `data-receiver.php` endpoint is maintained for backward compatibility with existing ESP32 devices. It now redirects to the new MVC structure.

## Benefits of MVC Architecture

1. **Separation of Concerns**: Clear separation between data, logic, and presentation
2. **Maintainability**: Easier to modify and extend functionality
3. **Reusability**: Components can be reused across different parts of the application
4. **Testability**: Each component can be tested independently
5. **Scalability**: Easy to add new features and modules
6. **Code Organization**: Cleaner, more organized codebase

## Future Enhancements

- User authentication system
- Real-time notifications
- Advanced analytics and reporting
- Mobile application integration
- API documentation with Swagger
- Unit testing implementation
- Database migration system
