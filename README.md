# Book Bank - Library Management System

A web-based library management system that allows users to search for books, reserve them, and manage their reservations. Built with PHP, MySQL, and modern web technologies.

## Features

### User Management
- **User Registration**: New users can create accounts with personal information
- **Secure Login**: Password hashing for secure authentication
- **Session Management**: Persistent login sessions across pages

### Book Search & Discovery
- **Text Search**: Search books by title, author, or genre
- **Category Filtering**: Browse books by predefined categories
- **Combined Filters**: Use text search and category filters together
- **Pagination**: Navigate through search results with 5 books per page
- **Book Details**: View cover images, author, year, genre, and availability status

### Reservation System
- **Reserve Books**: Logged-in users can reserve available books
- **Multiple Reservations**: Users can reserve multiple different books
- **My Reservations Page**: View all currently reserved books
- **Unreserve Books**: Cancel reservations when done with a book
- **Real-time Availability**: Books show as "Available" or "Reserved"

### User Interface
- Clean, modern design with warm color palette
- Responsive layout for different screen sizes
- Success/error message notifications
- Intuitive navigation between pages

## Technology Stack

- **Frontend**: HTML5, CSS3
- **Backend**: PHP 7+
- **Database**: MySQL
- **Server**: XAMPP (Apache + MySQL)
- **Font**: DM Sans (Google Fonts)


## File Structure

```
library/
├── css/
│   └── style.css              # All styling for the application
├── images/
│   ├── libraryLogo.png        # Logo image
│   └── [book covers]          # Book cover images
├── home.php                   # Main search and book listing page
├── login.php                  # User login page
├── register.php               # New user registration
├── reserve.php                # View and manage user's reservations
├── logout.php                 # Logout handler
└── error.php                  # Error page (optional)
```