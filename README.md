# HTZone PHP Developer Technical Assessment

## Overview
Hello candidate,

I'm excited to see what you can bring to our team. This technical assessment is designed to evaluate your PHP development skills, particularly in areas that are crucial for our day-to-day operations: API integration, database management, and frontend implementation.

## Project Setup

### Requirements
- PHP 7.4 or higher
- SQLite3 extension enabled
- PHP's built-in server
- Modern web browser with JavaScript enabled

### Getting Started
1. Clone or download this repository to your local machine
2. Ensure your PHP environment has SQLite3 extension enabled
3. Start your web use PHP's built-in server:
   
   # Navigate to the project directory
   cd path/to/project

   # Start PHP's built-in server
   php -S localhost:8000
 
4. Open your browser and navigate to:
   using built-in server: `http://localhost:8000`


###Suggested Project Structure (this is only a suggestion you are allowed to change the stracture)

project/
├── index.php              # Main landing page
├── init_database.php      # Database initialization script
├── ajax/
│   └── ajax.php          # AJAX request handler
├── class/
│   ├── HtzoneApi.php     # API integration class
│   ├── Category.php      # Category operations class
│   └── Item.php          # Database operations class
├── static/
│   ├── css/
│   │   └── styles.css    # Stylesheet
│   └── js/
│       └── scripts.js    # JavaScript functions
└── database/
    └── database.sqlite   # SQLite database


## The Task
You'll be building a sales landing page that showcases our products through multiple carousels and a comprehensive item grid. This task will test your ability to work with APIs, manage data, and create an engaging user interface.

### Core Requirements

#### 1. API Integration (`HtzoneApi` Class)
- Implement the `HtzoneApi` class to fetch item and category data from our API
- Store the fetched data in the local SQLite database
- Refer to `api_doc.txt` for detailed API documentation
- Ensure proper error handling and data validation

#### 2. Data Management
a. `Item` Class:
- Implement database operations for items
- Create proper mapping between database records and class properties
- Implement methods for filtering and sorting items
- Ensure efficient query performance

b. `Category` Class:
- Database operations for categories
- Methods needed:
  - `getCategories()`: Fetch all categories
  - `getCategoryById($id)`: Get specific category
  - `getCategoryItems($category_id)`: Get items for a specific category
  - `getTopCategories($limit)`: Get categories with most items
- Properties:
  - `id`: Category ID
  - `name`: Category name
  - `description`: Category description

#### 3. Product Carousels (Server-side Rendering)
- Create 3 distinct category-based product carousels
- Each carousel should display up to 10 items from its assigned category
- Implement server-side rendering using PHP
- (bonus)Ensure responsive design and smooth scrolling

#### 4. Product Grid (Frontend Implementation)
- Display all items in a grid or flexible layout below the carousels
- Implement lazy loading (10 items per request)
- Use either jQuery + $.ajax or vanilla JavaScript for AJAX requests
- (bonus) Ensure smooth scrolling and loading transitions

### Bonus Features
While not required, implementing these features will demonstrate your ability to go above and beyond:

1. **Sorting Functionality**
   - Implement sorting for the item grid
   - Can be either frontend or backend implementation
   - Common sorting options (price, name, etc.)

2. **Advanced Filtering**
   - Add filters for:
     - Category
     - Price range
     - Brand

3. **Carousel-Grid Integration**
   - Add a filter button under each carousel
   - Clicking the button should filter the grid to show only items from that carousel's category

### Files to Implement
1. `class/HtzoneApi.php`:
   - Implement API calls
   - Data fetching and storage

2. `class/Category.php`:
   - Database operations for categories
   - Category data management

3. `class/Item.php`:
   - Database operations
   - Item data management

4. `index.php`:
   - Server-side carousel rendering
   - HTML structure

5. `static/js/scripts.js`:
   - AJAX functionality
   - Infinite scroll
   - Filtering and sorting
   - Client rendering for item grid

### Database Initialization
After you've implemented the HtzoneApi class:
1. Run the initialization script:

   php init_database.php

   Or navigate to `http://localhost:8000/init_database.php`
2. Check the JSON response for successful initialization

### Database Schema
The SQLite database is already created with the following schema:

-- Categories Table
CREATE TABLE categories (
    id INTEGER PRIMARY KEY,
    name TEXT NOT NULL,
    description TEXT,
    parent_id INTEGER,
);

-- Items Table
CREATE TABLE items (
    id INTEGER PRIMARY KEY,
    name TEXT NOT NULL,
    description TEXT,
    price REAL NOT NULL,
    brand TEXT,
    category_id INTEGER,
    image_url TEXT,
    stock INTEGER,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);


## Evaluation Criteria
1. **Code Quality**
   - Clean, readable, and well-structured code
   - Proper error handling and edge cases
   - Code documentation and comments

2. **Functionality**
   - All features working as specified
   - Smooth user experience
   - Performance optimization

3. **Database Design**
   - Efficient database schema
   - Proper indexing
   - Query optimization

4. **UI/UX**
   - Responsive design
   - Smooth transitions
   - Intuitive user interface

## Time Expectation
- We expect this task to take 2-4 hours
- Focus on core requirements first
- Implement bonus features only if time permits

## Submission
- Ensure all code is properly commented
- Include any necessary setup instructions
- Document any assumptions or design decisions made
- If you implement bonus features, clearly indicate them in your submission

## Troubleshooting
If you encounter any issues:
1. Check PHP error logs
2. Ensure SQLite3 extension is enabled
3. Verify file permissions for the database
4. Check browser console for JavaScript errors

Best of luck! We're looking forward to seeing your implementation.
