# PHPCoreAPI Project Setup

## 1. Web Server Configuration

- Set your web server root to the 'public' folder of the project.
  Example paths:
    - Apache: DocumentRoot -> /path/to/myapi/public
    - Nginx: root -> /path/to/myapi/public

- URL rewriting is required for routing to work properly.

**Apache (.htaccess example):**
```
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>
```

**Nginx (server block example):**
```
server {
    listen 80;
    server_name yourdomain.com;
    root /path/to/myapi/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock; # adjust PHP version
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

- Restart the server after changes.
- Visit your project URL to confirm it works.

## 2. Database Configuration

- File: `app/Core/Database.php`
- Update variables with your database credentials:
```php
$dbHost = '127.0.0.1';
$dbUser = 'root';
$dbPass = '';
$dbName = 'mydatabase';
```
- Controllers automatically get a private MySQLi connection:
```php
$this->_db = Database::connect();
```

## 3. Routing

- All routes are in `app/Routes/web.php`

**Independent routes:**
```php
$router->get('/status', [HealthController::class, 'index']);
```

**Base route groups:**
```php
$router->group('/api', function($r){
    $r->get('/health', [HealthController::class, 'index']);
    $r->post('/user/create', [UserController::class, 'store']);
});
```
- You can have multiple base groups like `/auth` or `/admin`.

## 4. Controllers

- Create a controller:
```bash
phpcoreapi make:controller UserController
```
- Create controller in subfolder:
```bash
phpcoreapi make:controller Auth/UserController
```
- Request helpers:
  - `Request::JsonBody()` → raw JSON string
  - `Request::JsonData()` → decoded JSON array
  - `Request::GetData()` → $_GET
  - `Request::PostData()` → $_POST

- Example:
```php
$data = Request::JsonData();
$name = $data['name'] ?? null;
$this->_db->query("INSERT INTO users (name) VALUES ('$name')");
```

## 5. CLI Commands

- **Create project:** `phpcoreapi create-project myapi`
- **Serve server:** `phpcoreapi serve` (default 8080) or `phpcoreapi serve 2303`
- **Build production:** `phpcoreapi build`
- **Make controller:** `phpcoreapi make:controller UserController`
- **Add composer library:** `phpcoreapi add guzzlehttp/guzzle`

## 6. Local Development

- Start server: `phpcoreapi serve`
- Open browser: `http://localhost:8080`

## 7. Production Build

- Run: `phpcoreapi build`
- Output: `/dist`
- Install composer optimized: `composer install --no-dev --optimize-autoloader`
- Set web root to `/dist/public`

## 8. Adding Composer Libraries

- Example: `phpcoreapi add guzzlehttp/guzzle`

## 9. Example API Requests

**JSON POST:**
```bash
curl -X POST http://localhost:8080/user/create \
  -H "Content-Type: application/json" \
  -d '{"name":"John","email":"john@example.com"}'
```

**Form POST:**
```bash
curl -X POST http://localhost:8080/user/create -d "name=John&email=john@example.com"
```

**GET:**
```bash
curl http://localhost:8080/status
```

## 10. Notes

- Always keep web root pointing to 'public'
- Controllers use Request & Database helpers
- Edit `web.php` for routing only
