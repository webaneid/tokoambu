# DEPLOYMENT BLUEPRINT — TOKO AMBU (CODEX 5.1 FRIENDLY)

> **Context**: This is a **READY application**, not a build-from-scratch project.
> Target: Deploy Laravel 12 + Vite app using **MySQL**, **GitHub**, **releases + current + shared** pattern,
> with **tokoambu.com** (frontend) and **admin.tokoambu.com** (admin).

---

## 0. ASSUMPTIONS / INPUTS

```text
<GITHUB_REPO_URL>    = GitHub repository URL
<DEPLOY_ROOT>        = /var/www/tokoambu
<REL>                = release id (example: 20260115_093000)
<DB_PASSWORD>        = strong database password
<PHP_FPM_SOCK>       = /run/php/php8.3-fpm.sock (example)
```

Domains:

* Frontend: [https://tokoambu.com](https://tokoambu.com)
* Admin: [https://admin.tokoambu.com](https://admin.tokoambu.com)

Server stack:

* Linux + Nginx
* PHP-FPM (Laravel 12 compatible)
* Composer
* Node.js + npm
* MySQL 8.x

---

## 1. GITHUB — FIX REPO (ALREADY CREATED) + PUSH READY APPLICATION

> Scenario: You already created `https://github.com/webaneid/tokoambu.git` and pushed a README-only first commit.
> Goal: Replace repo content with the full Laravel app source (safe to force-push because it’s only README).

### 1.1 MUST NOT BE COMMITTED

```text
.env
/vendor
/node_modules
/storage/**   (uploads, logs, cache)
```

### 1.2 VERIFY YOU ARE IN THE REAL PROJECT ROOT

Project root should contain:

```text
app/
bootstrap/
config/
database/
public/
resources/
routes/
artisan
composer.json
package.json
```

### 1.3 ENSURE .gitignore IS CORRECT (MINIMUM)

```gitignore
.env
/vendor
/node_modules
/storage/*.key
/storage/app/*
/storage/framework/*
/storage/logs/*
```

### 1.4 RE-INITIALIZE LOCAL GIT (CLEAN) AND PUSH FULL APP

Use this if current local repo history is messy or you previously init in the wrong folder.

```bash
# from project root
rm -rf .git

git init
git add .
git commit -m "Initial commit: Toko Ambu production app"

git branch -M main
git remote add origin https://github.com/webaneid/tokoambu.git

# overwrite remote history (safe because remote only had README)
git push -u origin main --force
```

### 1.5 PRIVATE REPO: SERVER ACCESS (RECOMMENDED: SSH DEPLOY KEY)

If the GitHub repo is private, the server must authenticate to clone/pull.

On the SERVER:

```bash
ssh-keygen -t ed25519 -C "deploy@tokoambu" -f ~/.ssh/tokoambu_deploy_key
cat ~/.ssh/tokoambu_deploy_key.pub
```

In GitHub:

* Repo → Settings → Deploy keys
* Add deploy key (paste the `.pub` content)
* Enable **Allow read access** (write not required)

Test on server:

```bash
ssh -T git@github.com
```

Use SSH URL for cloning in production:

```text
git@github.com:webaneid/tokoambu.git
```

---

## 2. SERVER — DEPLOYMENT DIRECTORY STRUCTURE

```text
/var/www/tokoambu
├── releases/
│   ├── <REL>/
├── shared/
│   ├── .env
│   └── storage/
└── current -> releases/<REL>
```

### 2.1 CREATE DIRECTORIES

```bash
sudo mkdir -p /var/www/tokoambu/{releases,shared}
sudo mkdir -p /var/www/tokoambu/shared/storage
```

### 2.2 PERMISSIONS

```bash
sudo chown -R <DEPLOY_USER>:www-data /var/www/tokoambu
sudo chmod -R 775 /var/www/tokoambu/shared/storage
```

---

## 3. MYSQL — PRODUCTION DATABASE SETUP

```sql
CREATE DATABASE toko_ambu CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'tokoambu_user'@'localhost' IDENTIFIED BY '<DB_PASSWORD>';
GRANT ALL PRIVILEGES ON toko_ambu.* TO 'tokoambu_user'@'localhost';
FLUSH PRIVILEGES;
```

---

## 4. PRODUCTION ENV FILE (SHARED)

Path:

```text
/var/www/tokoambu/shared/.env
```

```env
APP_NAME="Toko Ambu"
APP_ENV=production
APP_KEY=<APP_KEY>
APP_DEBUG=false
APP_URL=https://tokoambu.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=toko_ambu
DB_USERNAME=tokoambu_user
DB_PASSWORD=<DB_PASSWORD>

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=database

VITE_APP_NAME="Toko Ambu"
```

Generate key once:

```bash
php artisan key:generate --show
```

---

## 5. DEPLOY RELEASE

### 5.1 CLONE CODE

```bash
cd /var/www/tokoambu
mkdir -p releases/<REL>
cd releases/<REL>

git clone <GITHUB_REPO_URL> .
```

### 5.2 INSTALL DEPENDENCIES

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

### 5.3 LINK SHARED FILES

```bash
ln -sfn /var/www/tokoambu/shared/.env .env
rm -rf storage
ln -sfn /var/www/tokoambu/shared/storage storage
```

### 5.4 LARAVEL OPTIMIZE

```bash
php artisan storage:link || true
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 5.5 ACTIVATE RELEASE

```bash
ln -sfn /var/www/tokoambu/releases/<REL> /var/www/tokoambu/current
```

---

## 6. NGINX — FRONTEND + ADMIN SUBDOMAIN

Both domains point to:

```text
/var/www/tokoambu/current/public
```

### 6.1 FRONTEND CONFIG

```nginx
server {
    listen 80;
    server_name tokoambu.com www.tokoambu.com;

    root /var/www/tokoambu/current/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass <PHP_FPM_SOCK>;
    }
}
```

### 6.2 ADMIN CONFIG

```nginx
server {
    listen 80;
    server_name admin.tokoambu.com;

    root /var/www/tokoambu/current/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass <PHP_FPM_SOCK>;
    }
}
```

```bash
sudo nginx -t
sudo systemctl reload nginx
```

---

## 7. QUEUE & SCHEDULER

```bash
php artisan queue:table
php artisan migrate --force
```

Worker command:

```bash
php artisan queue:work --sleep=3 --tries=3
```

Cron:

```cron
* * * * * cd /var/www/tokoambu/current && php artisan schedule:run >> /dev/null 2>&1
```

---

## 8. ROLLBACK PROCEDURE

```bash
ln -sfn /var/www/tokoambu/releases/<PREV_REL> /var/www/tokoambu/current
sudo systemctl reload nginx || true
sudo systemctl reload php-fpm || true
```

---

## 9. GO-LIVE CHECKLIST

* tokoambu.com loads
* admin.tokoambu.com loads
* admin login works
* product CRUD works
* media upload persists
* APP_DEBUG=false

---

END OF DOCUMENT
