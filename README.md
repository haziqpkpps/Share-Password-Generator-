# Password Share Application

## Prerequisites

- PHP 8.0+ with PDO MySQL
- MySQL or MariaDB
- Nginx or Apache
- Composer (if using additional packages)

## Installation

1. **Clone the repository**:
   ```bash
   git clone https://your.git.repo/password-share.git
   cd password-share
   ```

2. **Import the database schema**:
   ```bash
   mysql -u your_db_user -p < schema.sql
   ```

3. **Configure `db.php`**:
   ```php
   <?php
   // db.php
   session_set_cookie_params([
       'lifetime' => 86400,
       'path'     => '/',
       'secure'   => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
       'httponly' => true,
       'samesite' => 'Strict',
   ]);
   session_start();

   try {
       $pdo = new PDO(
           'mysql:host=localhost;dbname=password_share;charset=utf8mb4',
           'your_db_user',
           'your_db_password',
           [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
       );
   } catch (PDOException $e) {
       die('Database error');
   }
   ```

4. **Set file permissions**:
   ```bash
   chown -R www-data:www-data .
   chmod -R 750 .
   ```

5. **Configure your web server** (Nginx example):
   ```nginx
   server {
     listen 80;
     server_name password.yourdomain.com;

     root /var/www/password-share;
     index index.php;

     location / {
       try_files $uri $uri/ /index.php?$query_string;
     }

     location ~ \.php$ {
       fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
       fastcgi_index index.php;
       include fastcgi_params;
       fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
     }
   }
   ```

6. **Restart services**:
   ```bash
   systemctl restart php8.4-fpm nginx
   ```

## Adding or Changing Admin User

### Create a new admin

1. Generate a bcrypt hash:
   ```bash
   php -r "echo password_hash('NewSecureP@ssw0rd', PASSWORD_DEFAULT);"
   ```
2. Insert into database:
   ```sql
   INSERT INTO admin (username, password_hash)
     VALUES ('newadmin', '$2y$10$...');
   ```

### Change existing admin’s password

Use the application’s **Change Password** link while logged in as admin, or manually:
```sql
UPDATE admin
SET password_hash = '$2y$10$...'
WHERE username = 'admin';
```
