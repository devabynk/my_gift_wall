# Magical Moments ✨

> Create unforgettable digital memories for your loved ones. Build interactive, time-locked gift walls for birthdays, holidays, graduations, and special occasions.

![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=flat-square&logo=mysql&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind-3.x-38B2AC?style=flat-square&logo=tailwind-css&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)

## 📖 Table of Contents

- [Features](#-features)
- [Demo](#-demo)
- [Requirements](#-requirements)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Project Structure](#-project-structure)
- [Themes](#-themes)
- [Security](#-security)
- [API Reference](#-api-reference)
- [Contributing](#-contributing)
- [License](#-license)

## 🎁 Features

### Core Features
- **8 Interactive Themes** - Beautiful, animated themes for different occasions
- **Unlimited Gifts** - Friends can leave as many gifts and messages as they want
- **Time-Locked Opening** - Gifts remain locked until the specified date
- **Drag & Drop Positioning** - Place gifts anywhere on the wall
- **Real-time Countdown** - Live countdown timer to the event

### Social Features
- **Easy Sharing** - One-click sharing to WhatsApp, X (Twitter), Instagram, Telegram, LinkedIn
- **Two Link Types** - Share link (for adding gifts) and View link (for the recipient)
- **Mobile Optimized** - Perfect experience on all devices

### Security Features
- **CSRF Protection** - All forms protected against cross-site request forgery
- **Rate Limiting** - Maximum 1 gift per minute to prevent spam
- **Math CAPTCHA** - Human verification without third-party services
- **Input Sanitization** - All user inputs are sanitized and validated
- **Prepared Statements** - SQL injection prevention with PDO
- **Security Headers** - CSP, X-Frame-Options, and more

## 🎮 Demo

1. **Create a Wall** - Enter your name, select a theme, set the unlock date
2. **Share the Link** - Send the share link to friends and family  
3. **Collect Gifts** - Friends click anywhere on the wall to place their gifts
4. **Open on Event Day** - All gifts and messages are revealed on the specified date!

## 📋 Requirements

| Requirement | Version |
|-------------|---------|
| PHP | 8.0 or higher |
| MySQL/MariaDB | 5.7+ / 10.3+ |
| Web Server | Apache 2.4+ or Nginx |
| mod_rewrite | Required for Apache |

### PHP Extensions
- PDO with MySQL driver
- Session support
- OpenSSL (for secure token generation)

## 🚀 Installation

### Quick Start

```bash
# 1. Clone the repository
git clone https://github.com/yourusername/magical-moments.git
cd magical-moments

# 2. Import the database
mysql -u root -p your_database < database_example.sql

# 3. Configure the application
cp config.example.php config.php
nano config.php  # Edit with your database credentials

# 4. Set permissions
mkdir -p logs
chmod 755 logs
chmod 644 config.php

# 5. Access the application
# Open http://localhost/magical-moments in your browser
```

### Detailed Installation

#### Step 1: Database Setup

Create a new MySQL database and user:

```sql
CREATE DATABASE magical_moments CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'mm_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON magical_moments.* TO 'mm_user'@'localhost';
FLUSH PRIVILEGES;
```

Import the schema and data:

```bash
mysql -u mm_user -p magical_moments < database_example.sql
```

#### Step 2: Application Configuration

Copy the example configuration file:

```bash
cp config.example.php config.php
```

Edit `config.php` with your settings:

```php
// Environment
define('APP_ENV', 'production');  // 'development' or 'production'
define('APP_DEBUG', false);        // Set to false in production
define('APP_URL', 'https://yourdomain.com');

// Database
$host = 'localhost';
$dbname = 'magical_moments';
$username = 'mm_user';
$password = 'your_secure_password';
```

#### Step 3: Web Server Configuration

**Apache** (mod_rewrite required):
The included `.htaccess` file handles all configuration automatically.

**Nginx**:
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/magical-moments;
    index index.php;

    # Security: Block access to sensitive files
    location ~ /includes/ {
        deny all;
    }
    
    location ~ \.sql$ {
        deny all;
    }
    
    location ~ /logs/ {
        deny all;
    }
    
    location ~ ^/config\.php$ {
        deny all;
    }

    # PHP processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## ⚙️ Configuration

### Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `APP_ENV` | Environment mode | `development` |
| `APP_DEBUG` | Enable debug mode | `true` |
| `APP_URL` | Base URL of application | `http://localhost` |

### Session Configuration

Sessions are configured for security:
- `cookie_httponly`: Enabled (prevents XSS)
- `cookie_secure`: Enabled in production (HTTPS only)
- `use_strict_mode`: Enabled (prevents session fixation)

## 📁 Project Structure

```
magical-moments/
├── assets/
│   ├── css/
│   │   └── style.css          # Theme styles and animations
│   ├── img/                    # Images and assets
│   └── js/                     # JavaScript files
├── includes/
│   ├── captcha.php            # CAPTCHA generation and validation
│   ├── footer.php             # HTML footer
│   ├── header.php             # HTML header with meta tags
│   ├── nav-footer.php         # Navigation footer component
│   ├── nav-header.php         # Navigation header component
│   └── security.php           # Security headers and helpers
├── logs/                       # Error logs (gitignored)
├── .htaccess                   # Apache configuration
├── .gitignore                  # Git ignore rules
├── CHANGELOG.md                # Version history
├── config.example.php          # Example configuration
├── config.php                  # Your configuration (gitignored)
├── create.php                  # Wall creation wizard
├── database_example.sql        # Database schema and sample data
├── index.php                   # Homepage
├── LICENSE                     # MIT License
├── README.md                   # This file
├── success.php                 # Success page with share links
└── wall.php                    # Interactive wall view
```

## 🎨 Themes

Each theme includes unique animations, decorations, and color schemes:

| Theme | Description | Elements |
|-------|-------------|----------|
| 🎂 **Birthday** | Colorful party celebration | Cake, balloons, confetti, garlands |
| 🎄 **Christmas** | Cozy winter wonderland | Tree, fireplace, snow, stockings |
| ❤️ **Valentine's** | Romantic starlit evening | Picnic blanket, candles, fireflies, hearts |
| 🎃 **Halloween** | Spooky haunted scene | Haunted house, graveyard, bats, pumpkins |
| 👶 **Baby Shower** | Soft nursery setting | Crib, mobile, clouds, toys |
| 🎓 **Graduation** | Academic celebration | Gate, podium, flying caps, confetti |
| 👋 **Farewell** | Office goodbye | Desk, corkboard, suitcase, moving boxes |
| 🩹 **Get Well** | Cozy recovery room | Armchair, fireplace, flowers, tea |

### Gift Options per Theme

Each theme has 8 unique gift types. Example for Birthday:
- 🕯️ Candle, 🎈 Balloon, 🎁 Gift Box, 🥳 Party Hat
- 🍰 Cake Slice, 🎂 Cake, 🎊 Confetti, 🧁 Cupcake

## 🔒 Security

### Implemented Security Measures

1. **CSRF Protection**
   - Token-based validation on all POST requests
   - Tokens regenerated per session

2. **Rate Limiting**
   - 60-second cooldown between gift submissions
   - Session-based tracking

3. **Input Validation**
   - All inputs sanitized with `htmlspecialchars()`
   - Length limits enforced
   - Type validation (datetime, UUID, etc.)

4. **SQL Injection Prevention**
   - PDO with prepared statements
   - No raw query concatenation

5. **XSS Prevention**
   - Output encoding
   - Content Security Policy headers

6. **Security Headers**
   ```
   X-Frame-Options: SAMEORIGIN
   X-Content-Type-Options: nosniff
   X-XSS-Protection: 1; mode=block
   Referrer-Policy: strict-origin-when-cross-origin
   Content-Security-Policy: [configured]
   ```

### Security Best Practices

- Never commit `config.php` to version control
- Use HTTPS in production
- Keep PHP and dependencies updated
- Review logs regularly

## 📚 API Reference

### Database Schema

#### `walls` Table
| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| wall_uuid | VARCHAR(32) | Unique identifier |
| creator_name | VARCHAR(100) | Wall creator's name |
| event_type | VARCHAR(50) | Theme slug |
| theme_id | INT | Foreign key to themes |
| event_time | DATETIME | Unlock date/time |
| created_at | TIMESTAMP | Creation time |

#### `gifts` Table
| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| wall_id | INT | Foreign key to walls |
| sender_name | VARCHAR(100) | Gift sender's name |
| gift_type | VARCHAR(50) | Emoji icon |
| message | TEXT | Gift message |
| position_x | INT | X position (0-100%) |
| position_y | INT | Y position (0-100%) |
| created_at | TIMESTAMP | Creation time |

#### `themes` Table
| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| slug | VARCHAR(50) | URL-safe identifier |
| name | VARCHAR(100) | Display name |
| css_class | VARCHAR(50) | CSS class name |

#### `theme_gifts` Table
| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| theme_id | INT | Foreign key to themes |
| name | VARCHAR(100) | Gift name |
| icon | VARCHAR(50) | Emoji icon |

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. **Fork** the repository
2. **Create** a feature branch
   ```bash
   git checkout -b feature/amazing-feature
   ```
3. **Commit** your changes
   ```bash
   git commit -m 'Add amazing feature'
   ```
4. **Push** to the branch
   ```bash
   git push origin feature/amazing-feature
   ```
5. **Open** a Pull Request

### Development Guidelines

- Follow PSR-12 coding standards
- Write meaningful commit messages
- Test on multiple browsers and devices
- Update documentation as needed

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- [Tailwind CSS](https://tailwindcss.com/) - Utility-first CSS framework
- [Google Fonts](https://fonts.google.com/) - Outfit font family
- Emoji graphics by various vendors

---

<p align="center">Made with ❤️ in Turkey</p>
