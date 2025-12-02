# HealthScope BD - XAMPP Setup Guide

## 🚀 Quick Start Guide

### Step 1: Install XAMPP

1. **Download XAMPP:**
   - Go to: https://www.apachefriends.org/download.html
   - Download for Windows (PHP 8.x recommended)
   - File size: ~150MB

2. **Install:**
   - Run the installer
   - Select components: **Apache, MySQL, PHP, phpMyAdmin**
   - Install to: `C:\xampp`

### Step 2: Setup Database

1. **Start XAMPP:**
   - Open XAMPP Control Panel
   - Click **Start** on Apache
   - Click **Start** on MySQL

2. **Create Database:**
   - Open browser: http://localhost/phpmyadmin
   - Click **New** to create database
   - Click **Import** tab
   - Choose file: `setup.sql` from your project
   - Click **Go**

### Step 3: Move Project to XAMPP

**Option A: Create Symbolic Link (Recommended)**
```powershell
# Run as Administrator
New-Item -ItemType SymbolicLink -Path "C:\xampp\htdocs\healthscope" -Target "E:\Project\healthscope"
```

**Option B: Copy Files**
```powershell
# Copy entire project
Copy-Item -Path "E:\Project\healthscope\*" -Destination "C:\xampp\htdocs\healthscope" -Recurse
```

### Step 4: Access Your Project

1. **Stop Python Server:**
   - Go to your terminal running `python -m http.server 8000`
   - Press `Ctrl+C` to stop

2. **Open in Browser:**
   - http://localhost/healthscope
   - or
   - http://localhost/healthscope/index.html

## 📁 Project Structure

```
healthscope/
├── index.html
├── login.html
├── signup.html
├── symptom-checker.html
├── report.html
├── styles.css
├── auth.css
├── app.js
├── api/                      # NEW: PHP API
│   ├── login.php            # Login endpoint
│   └── signup.php           # Signup endpoint
├── config/                   # NEW: Configuration
│   └── database.php         # Database connection
└── setup.sql                # NEW: Database schema
```

## 🔐 Test Login Credentials

After running `setup.sql`, you can test with:
- **Email:** demo@healthscope.com
- **Password:** password123

## 🔧 Update JavaScript to Use PHP API

Update your `app.js` file:

```javascript
// Login Form Handler
document.getElementById('login-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    try {
        const response = await fetch('/healthscope/api/login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email, password })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Login successful! Welcome ' + data.user.name, 'success');
            setTimeout(() => {
                window.location.href = 'index.html';
            }, 1500);
        } else {
            showToast(data.message, 'error');
        }
    } catch (error) {
        showToast('Server error. Please try again.', 'error');
    }
});

// Signup Form Handler
document.getElementById('signup-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const firstName = document.getElementById('first-name').value;
    const lastName = document.getElementById('last-name').value;
    const email = document.getElementById('signup-email').value;
    const phone = document.getElementById('phone').value;
    const password = document.getElementById('signup-password').value;
    
    try {
        const response = await fetch('/healthscope/api/signup.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                firstName,
                lastName,
                email,
                phone,
                password
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Account created successfully!', 'success');
            setTimeout(() => {
                window.location.href = 'login.html';
            }, 1500);
        } else {
            showToast(data.message, 'error');
        }
    } catch (error) {
        showToast('Server error. Please try again.', 'error');
    }
});
```

## 🐛 Common Issues

### Issue 1: Port 80 Already in Use
**Solution:**
- Open: `C:\xampp\apache\conf\httpd.conf`
- Find: `Listen 80`
- Change to: `Listen 8080`
- Restart Apache
- Access: http://localhost:8080/healthscope

### Issue 2: MySQL Won't Start
**Solution:**
- Check if another MySQL service is running
- Open Services (Win+R → `services.msc`)
- Stop any other MySQL services

### Issue 3: Database Connection Error
**Solution:**
- Check XAMPP MySQL is running
- Verify database name in `config/database.php`
- Make sure `setup.sql` was imported correctly

## ✅ Verification Steps

1. ✅ XAMPP Apache is running (green in Control Panel)
2. ✅ XAMPP MySQL is running (green in Control Panel)
3. ✅ Database created (check phpMyAdmin)
4. ✅ Project accessible at http://localhost/healthscope
5. ✅ Login works with demo credentials

## 🎯 Next Steps

1. Test login/signup functionality
2. Create more API endpoints:
   - `api/report.php` - Submit disease reports
   - `api/symptoms.php` - Symptom checker logic
   - `api/trends.php` - Get disease trends data
3. Add authentication middleware
4. Implement session management

## 📞 Need Help?

If you encounter any issues, check:
- XAMPP error logs: `C:\xampp\apache\logs\error.log`
- PHP errors: Enable in `php.ini`
- Browser console for JavaScript errors

---

**Created by:** HealthScope BD Development Team
**Last Updated:** December 2024
