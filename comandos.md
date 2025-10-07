# Comandos usados para el desarrollo


## Crear proyecto symfony
El contenedor monta la carpeta del proyecto en la carpeta /app/ por
lo que pueden hacerse cambios con las herramientas disponibles 
en el contenedor y persistir los archivos generados en la carpeta
del proyecto.

### IMPORTANTE
Voy a poner "(contenedor)" de ahora en adelante para indicar que
el comando se tiene que ejecutar dentro de este. Ademas, casi
todos los comandos, por ejemplo composer, symfony, npm, tienen que
ejecutarse en /app/app dentro del contenedor, es decir, la 
carpeta del proyecto symfony.

### Entrar por primera vez al contenedor
```
docker compose run php bash
```

### Generar el proyecto symfony
(contenedor)
```
symfony new app --webapp
```
Este nombre, 'app', tiene que coincidir con el valor pasado con --dir= al comando
por defecto en Dockerfile.

Esto va a crear el proyecto symfony en la carpeta gestion-posgrado/app/
o en el contenedor, /app/app/.
De ahora en mas puede ejecutarse el contenedor sin comandos para levantar
el servidor de desarrollo. Luego, puede abrirse otra terminal dentro
del contenedor para usar symfony-cli.

### Borrar el repositorio git generado por symfony
El repositorio va a generarse para la carpeta gestion posgrado por lo que no
se va a usar el que genera symfony.
```
rm -rf app/.git
```

## Iniciar contenedor de desarrollo en background
```
sudo docker compose up -d
```

## Abrir una terminal con bash dentro del contenedor
```
sudo docker exec -ti desarrollo-gestion-posgrado bash
```

## Borrar cualquier proceso que haya quedado ejecutando
Cuando se detiene el contenedor que tiene levantado el servidor
symfony, si no se detiene correctamente luego el contenedor no
va a levantar correctamente. Si esto ocurre, entrar al contenedor
como la primera vez (docker compose run php bash) y ejecutar lo
siguiente:
```
symfony server:stop
```

Despues va a poder iniciarse correctamente el contenedor con 
```
docker compose up [-d]
```

## Instalar Encore Webpack
Para compilar los archivos javascript y jsx a javascript que puedan
usar los navegadores es necesario un webpack y Encore es la 
recomendacion para usar con Symfony.

(contenedor)
```
composer require symfony/webpack-encore-bundle
```

## Instalar React
(contenedor)
```
npm install @babel/preset-react react react-dom
```

## Instalar Twig
(contenedor)
```
composer require symfony/twig-bundle
```

## Instalar Bootstrap
(contenedor)
```
npm install bootstrap
```

## Instalar forms y validaciones de symfony
(contenedor)
```
composer require symfony/form symfony/validator
```

## Instalar maker bundle
(contenedor)
```
composer require symfony/maker-bundle
```

## Instalar Doctrine y dependencias relacionadas
(contenedor)
```
composer require symfony/orm-pack
```

