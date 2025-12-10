# 1. Requisitos previos

Sistema:
    - Sistema Debian 
    - Acceso sudo

Este instructivo fue provado en Debian 13 trixie.

# 2. Instalar PHP 8.3 y extensiones necesarias

Symfony necesita PHP y algunas extensiones similares a las que usaste en Docker.
## Habilitar repositorio Sury para instalar PHP.

sudo apt update
sudo apt install -y ca-certificates apt-transport-https lsb-release gnupg wget curl git

sudo wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
echo deb https://packages.sury.org/php/ trixie main | sudo tee /etc/apt/sources.list.d/php.list
sudo apt update

## Instalar PHP 8.3 + extensiones

sudo apt install -y   php8.3 php8.3-cli php8.3-common php8.3-intl php8.3-mysql php8.3-xsl   php8.3-gd php8.3-sockets php8.3-curl php8.3-zip php8.3-xml php8.3-mbstring   libicu-dev libpq-dev libxslt1-dev libgd-dev libssl-dev libsodium-dev php-amqp

# 3. Instalar Node.js + npm para assets

sudo apt install -y nodejs npm

# 4. Instalar Composer

curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# 5. Instalar Symfony CLI 

wget https://get.symfony.com/cli/installer -O - | bash
sudo mv ~/.symfony*/bin/symfony /usr/local/bin/symfony

# 6. Colocar el proyecto en la VM
Puede tambien copiarse por otro medio ademas de Git.

Ejemplo usando Git:
```
sudo git clone https://github.com/fcosolito/gestion-posgrado.git /var/www/gestion-posgrado
cd /var/www/gestion-posgrado/app
```

# 7. Instalar dependencias del proyecto

En el directorio del proyecto:
Dependencias PHP (prod):

composer install --no-dev --optimize-autoloader --no-scripts

Dependencias JS (si usás Webpack Encore):

npm install
npm run build

# 8. Configuración de entorno

Crear .env.local:

nano .env.local

Ejemplo:

APP_ENV=prod
APP_DEBUG=0
DATABASE_URL=mysql://<usuario>:<contrasena>@<host con base de datos>:3306/posgrado

Estas propiedades tambien pueden definirse como variables de
entorno. Si se definen con ambos metodos, las variables de 
entorno van a emplearse.

# 9. Calentar caché de producción
Esto tambien genera el directorio var/.

```
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod
```

# 10. Permisos en var/ y vendor/

El usuario del servidor web (www-data) debe poder escribir en:

    - var/

    - public/ 

    - vendor/ (solo lectura)

Aplicar:

```
sudo chown -R www-data:www-data var
sudo chmod -R 775 var
sudo chown -R www-data:www-data public/
sudo chmod -R 775 public/
```

# 11. Configurar servidor web

Ejemplo con Nginx + PHP-FPM 8.3:

Instalar:

```
sudo apt install -y nginx php8.3-fpm
```

Configurar sitio:

sudo nano /etc/nginx/sites-available/gestion-posgrado.conf

Contenido:

server {
    listen 8080;
    server_name _;
    root /var/www/gestion-posgrado/app/public;

    location / {
        try_files $uri /index.php$is_args$args;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
    }
}

Activar sitio:

```
sudo ln -s /etc/nginx/sites-available/gestion-posgrado.conf /etc/nginx/sites-enabled/
sudo systemctl restart nginx
sudo systemctl restart php8.3-fpm
```

# 12. Acceder a la aplicación

Abrí un navegador:

http://<IP_DE_LA_VM>:8080/

# 13. Comandos útiles

Ver logs de Symfony:

```
tail -f var/log/prod.log
```

Ver logs del servidor web:

```
sudo journalctl -u nginx -f
```

# 14. Solucion de problemas
## Composer install falla
Comprobar que la version que se esta usando de php es la correcta (8.3).
```
php -v
```
