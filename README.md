# Lumeren Optician - Web Application

This is the **administrative web application** for managing an optician business. This application is part of a two-component solution:

2. **Web Application** - This repository - A complete management system for running an optician business

> **Note:** The marketing website and web application are maintained in **separate repositories** and deployed independently.

---

## 📁 Project Structure

```
Lumeren-admin/                   # Admin web application (PHP)
├── index.php                    # Login page
├── config.php                   # Database configuration & initialization
├── dashboard.php                # Main dashboard
├── customers.php                # Customer management
├── appointments.php             # Appointment scheduling
├── prescriptions.php            # Prescription records
├── products.php                 # Product inventory
├── sales.php                    # Sales tracking
└── logout.php                   # Logout functionality
```

---


## 💼 Web Application (Admin Portal)

### Features:

#### Dashboard
- Real-time statistics (customers, appointments, low stock alerts)
- Upcoming appointments overview
- Low stock product alerts
- Quick access to all sections

#### Customer Management
- Add, edit, delete customer records
- Store contact information and addresses
- View customer history

#### Appointment Scheduling
- Schedule appointments with date/time
- Multiple service types (Eye Exam, Contact Lens Fitting, etc.)
- Status tracking (scheduled, completed, cancelled)
- Customer linking

#### Prescription Management
- Record eye prescriptions with full details
- OD (right eye) and OS (left eye) measurements
- Sphere, Cylinder, Axis, and PD values
- Additional notes

#### Product Inventory
- Track frames, lenses, contact lenses, accessories
- Stock management with low stock alerts
- Pricing and brand information
- Category organization

#### Sales Tracking
- Record product sales
- Automatic stock deduction
- Multiple payment methods
- Sales analytics (daily/total)

---

## 🚀 Installation Instructions

### Prerequisites:
- PHP 8.0 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Web browser

### Step 1: Server Setup

**Using XAMPP (Recommended for development):**
1. Download and install XAMPP from https://www.apachefriends.org/
2. Start Apache and MySQL from XAMPP Control Panel

**Using LAMP/WAMP/MAMP:**
- Follow standard installation for your operating system

### Step 2: Database Setup

1. Open phpMyAdmin (usually at http://localhost/phpmyadmin)
2. The database will be created automatically when you first access the application
3. Default database name: `optician_db`

**Or manually create database:**
```sql
CREATE DATABASE optician_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Step 3: Application Installation

1. Clone or download this repository to your web server directory:
   - XAMPP: `C:\xampp\htdocs\Lumeren-admin\`
   - LAMP/Linux: `/var/www/html/Lumeren-admin/`
   - Laragon: `C:\laragon\www\Lumeren-admin\`

2. Update database credentials in `config.php` if needed:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'lumeren_db');
define('DB_USER', 'root');        // Change if needed
define('DB_PASS', '');            // Change if needed
```

3. Set proper file permissions (Linux/Mac):
```bash
chmod 755 Lumeren-admin
chmod 644 Lumeren-admin/*.php
```

### Step 4: Access the Application

1. Open your web browser
2. Navigate to: `http://localhost/Lumeren-admin/`
3. Login with default credentials:


### Step 5: Initial Setup

The database will automatically initialize on first access with:
- Sample customer data
- Sample appointments
- Sample prescriptions
- Sample products
- Sample sales records

---

## 🔐 Security Recommendations

**For Production Use:**

1. **Change Default Credentials:**
   - Update the login credentials in `index.php`
   - Use strong passwords

2. **Implement Password Hashing:**
   ```php
   // Use password_hash() and password_verify()
   $hashedPassword = password_hash('password', PASSWORD_DEFAULT);
   ```

3. **Use Environment Variables:**
   - Store database credentials in `.env` file
   - Don't commit credentials to version control

4. **Enable HTTPS:**
   - Use SSL/TLS certificates
   - Redirect all HTTP traffic to HTTPS

5. **Input Validation:**
   - Already implemented with prepared statements
   - Add additional validation as needed

6. **Session Security:**
   - Set secure session parameters
   - Implement session timeout

---

## 📊 Database Schema

### Tables:

**customers**
- id, name, email, phone, address, created_at

**appointments**
- id, customer_id, appointment_date, appointment_time, service_type, status, notes, created_at

**prescriptions**
- id, customer_id, prescription_date, od_sphere, od_cylinder, od_axis, os_sphere, os_cylinder, os_axis, pd, notes, created_at

**products**
- id, name, category, brand, price, stock, description, created_at

**sales**
- id, customer_id, product_id, quantity, total_price, sale_date, payment_method, created_at

---

## 🎨 Customization

### Branding:
1. Update logo and company name in all files
2. Modify color scheme in CSS variables:
   - Website: `:root` variables in HTML
   - Web App: `:root` variables in each PHP file

### Features:
1. Add new service types in appointments dropdown
2. Add new product categories
3. Customize prescription fields
4. Add new payment methods

---

## 📱 Browser Compatibility

### Marketing Website:
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

### Web Application:
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Mobile browsers (responsive design)

---

## 🐛 Troubleshooting

### Database Connection Issues:
- Verify MySQL is running
- Check database credentials in `config.php`
- Ensure database user has proper permissions

### Session Issues:
- Check PHP session configuration
- Verify write permissions on session directory
- Clear browser cookies

### Display Issues:
- Clear browser cache
- Check if Bootstrap CDN is accessible
- Verify all CSS/JS resources are loading

---

## 📝 Usage Guide

### Adding a New Customer:
1. Go to Customers page
2. Click "Add Customer" button
3. Fill in customer details
4. Save

### Booking an Appointment:
1. Go to Appointments page
2. Click "Add Appointment"
3. Select customer, date, time, and service
4. Save

### Recording a Prescription:
1. Go to Prescriptions page
2. Click "Add Prescription"
3. Select customer
4. Enter eye measurements
5. Save

### Managing Inventory:
1. Go to Products page
2. Add/edit products with stock levels
3. System alerts when stock is low (<10 units)

### Recording a Sale:
1. Go to Sales page
2. Click "New Sale"
3. Select customer and product
4. Enter quantity (stock updates automatically)
5. Choose payment method
6. Record sale

---

## 🔄 Future Enhancements

Potential features to add:
- Email notifications for appointments
- SMS reminders
- Online booking integration
- Payment gateway integration
- Reporting and analytics
- Multi-user support with roles
- Inventory alerts via email
- Customer portal
- Export data to PDF/Excel

---

## 📄 License

This is a custom-built solution for optician businesses. Feel free to modify and customize according to your needs.

---

## 🤝 Support

For issues or questions:
1. Check the troubleshooting section
2. Review PHP error logs
3. Check MySQL error logs
4. Verify all prerequisites are met

---

## 🚢 Deployment

### Web Application (This Repository)

This PHP application requires a server with PHP and MySQL support:

**Recommended Hosting Options:**
- Traditional shared hosting (cPanel, Plesk)
- VPS with LAMP/LEMP stack
- Cloud platforms (AWS, DigitalOcean, Linode)

**Deployment Steps:**
1. Upload all files to your web server
2. Import or auto-initialize the database
3. Update `config.php` with production database credentials
4. Change default login credentials
5. Enable HTTPS with SSL certificate
6. Set up automated backups

### Marketing Website (Separate Repository)

The marketing website is a static HTML/React application deployed separately:

**Recommended Hosting Options:**
- GitHub Pages
- Netlify
- Vercel
- Any static hosting service

---

## ✅ Checklist for Deployment

- [ ] Install web server (Apache/Nginx)
- [ ] Install PHP 8.0+
- [ ] Install MySQL 5.7+
- [ ] Clone/upload repository files to web directory
- [ ] Update database credentials in `config.php`
- [ ] Change default login credentials
- [ ] Test all functionality
- [ ] Enable HTTPS with SSL certificate
- [ ] Set up automated database backups
- [ ] Configure firewall rules
- [ ] Test on target browsers
- [ ] Deploy marketing website separately (if applicable)

---

## 📦 Repository Information

**Repository:** Lumeren-admin (Web Application)

**Related Repository:** Lumeren-website (Marketing Website - separate)

**Built with:** PHP 8, MySQL, Bootstrap 5, HTML5, CSS3, JavaScript

**Version:** 1.0.0

**Last Updated:** January 2026
