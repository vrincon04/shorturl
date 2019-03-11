# Short Url

API que se encarga de minificar una url larga usando una variante del algoritmo base_convert

La variante del algoritmo permite generar una cadena secuencial entre 1 y 14 caracteres de longitud en función del número pasado como argumento a la función. La cadena secuencial generada solo letras del alfabeto desde la A a la Z mayúscula y de la A a la Z minúscula.

## Descripción del Algoritmo

```php
/**
 *  Converts an integer into the alphabet base (A-z).
 * 
 * @param int $number This is the number to convert.
    * 
    * @return string
    */
public static function numberToAlphabet($number)
{
    // Create and fill an array with alphabet(A-Za-z).
    $alphabet = array_merge(range('A', 'Z'), range('a', 'z'));
    //Assign the size of $alphabet array to the variable $length.
    $length = count($alphabet);
    //This variable will hold the output generated.
    $result = '';
    //Make sure the parameter is greater than or equal to zero so we can star lopping.
    for ($i = 1; $number >= 0; $i++) {
        // The code below limits the number to the $alphabet array size
        $formula = abs(($number % pow($length, $i) / pow($length, $i - 1)));
        // Comcat the current result with the previous
        $result = $alphabet[$formula] . $result;
        // Reduce the number with the size of the array raised to the iteration
        $number -= pow($length, $i);
    }

    return $result;
}
```

## Usar el Algoritmo

```php
use App\Libraries\Helper;

Helper::numberToAlphabet(0) # returns 'A'
Helper::numberToAlphabet(26) # returns 'a'
Helper::numberToAlphabet(26) # returns 'Ac'
```

## Instalación de la Aplicación Usando Docker Compose

Descargar E Instalar Short Url

Lo primero es clonar el código de la aplicación shorturl.

```bash
git clone https://github.com/vrincon04/shorturl.git
cd shorturl
```

Luego de hacer eso usaremos la imagen de composer y ejecutamos el siguiente comando para asegurar de que la carpeta vendor haya sido creada.

```bash
docker run --rm -v $(pwd):/app composer install
```

y por ultimo nos adueñamos del directorio
```bash
sudo chown -R $USER:$USER ~/shorturl
```

Ya tenemos el proyecto de shorturl instalado, ahora nos falta crear y configurar nuestro archivo docker-compose.ym el cual va a tener todas las instrucciones para nuestro contenedor.

```bash
nano shorturl/docker-compose.yml
```
### Creamos el Docker Compose

Aqui vamos a definir nuestros 3 principales servicios [app, servidor web, y base de datos] y copie y pegue el siguiente codigo.

```yml
services:
 
  #PHP
  app:
    build:
      context: .
      dockerfile: Dockerfile
    image: mi-shorturl
    container_name: app
    restart: unless-stopped
    tty: true
    environment:
      SERVICE_NAME: app
      SERVICE_TAGS: dev
    working_dir: /var/www
    networks:
      - app-network
 
  #Nginx
  webserver:
    image: nginx:alpine
    container_name: webserver
    restart: unless-stopped
    tty: true
    ports:
      - "80:80"
      - "443:443"
    networks:
      - app-network
 
  #MySQL Service
  db:
    image: mysql:5.7.22
    container_name: db
    restart: unless-stopped
    tty: true
    ports:
      - "3306:3306"
    environment:
      MYSQL_DATABASE: short
      MYSQL_ROOT_PASSWORD: root
      SERVICE_TAGS: dev
      SERVICE_NAME: mysql
    networks:
      - app-network
 
#Redes
networks:
  app-network:
    driver: bridge
```

Luego de tener nuestro docker-compose vamos a configurar nuestra persistencia de datos, ya que si lo dejamos así como esta cada vez que el contenedor se reinicie (o se caiga) toda la data se perderá asi que vamos agregar un volumen al servicio db (Base de datos) de nuestro contenedor.

```yml
db:
    volumes:
      - dbdata:/var/lib/mysql
      - ./mysql/my.cnf:/etc/mysql/my.cnf
    networks:
      - app-network
```

Con esto le decimos que todo lo que esta en /var/lib/mysql será replicado en la carpeta dbdata de nuestro sistema anfitrión. ademas persistimos la configuración de la base de datos en /etc/mysql/my.cnf.

El resultado final deberá ser un docker-compose como este:

```yml
services:
 
  #PHP
  app:
    build:
      context: .
      dockerfile: Dockerfile
    image: mi-shorturl
    container_name: app
    restart: unless-stopped
    tty: true
    environment:
      SERVICE_NAME: app
      SERVICE_TAGS: dev
    working_dir: /var/www
    networks:
      - app-network
 
  #Nginx
  webserver:
    image: nginx:alpine
    container_name: webserver
    restart: unless-stopped
    tty: true
    ports:
      - "80:80"
      - "443:443"
    networks:
      - app-network
 
  #MySQL Service
  db:
    image: mysql:5.7.22
    container_name: db
    restart: unless-stopped
    tty: true
    ports:
      - "3306:3306"
    environment:
      MYSQL_DATABASE: short
      MYSQL_ROOT_PASSWORD: root
      SERVICE_TAGS: dev
      SERVICE_NAME: mysql
    volumes:
      - dbdata:/var/lib/mysql/
      - ./mysql/my.cnf:/etc/mysql/my.cnf
    networks:
      - app-network
 
#Redes
networks:
  app-network:
    driver: bridge
```
## Dockerfile

Ahora para que el docker-compose que recién creamos funcione como es debido debemos crear un Dockerfile que definirá la imagen que nombramos como mi-shorturl. y configura php ademas todos los directorios y puertos de la app.

```bash
FROM php:7.2-fpm
 
# Copiar composer.lock y composer.json
COPY composer.lock composer.json /var/www/
 
# Configura el directorio raiz
WORKDIR /var/www
 
# Instalamos dependencias
RUN apt-get update && apt-get install -y \
    build-essential \
    mysql-client \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl
 
# Borramos cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*
 
# Instalamos extensiones
RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl
RUN docker-php-ext-configure gd --with-gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ --with-png-dir=/usr/include/
RUN docker-php-ext-install gd
 
# Instalar composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
 
# agregar usuario para la aplicación shorturl
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www
 
# Copiar el directorio existente a /var/www
COPY . /var/www
 
# copiar los permisos del directorio de la aplicación
COPY --chown=www:www . /var/www
 
# cambiar el usuario actual por www
USER www
 
# exponer el puerto 9000 e iniciar php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
```

## Configurando PHP

Para esto vamos a crear un directorio que sera usado por los volúmenes que definimos en el servicio de la app, luego vamos a entrar al directorio creado el archivo local.ini.

```bash
mkdir shorturl/php
nano shorturl/php/local.ini
```
Incluimos algunas configuraciones básicas

```code
upload_max_filesize=100M
post_max_size=100M
```

## Configurando Nginx

Creamos el directorio y el archivo de configuración que establecimos en el volumen del servicio webserver

```bash
mkdir -p shorturl/nginx/conf.d
nano shorturl/nginx/conf.d/app.conf
```

Agreamos las configuraciones de lugar

```code
server {
    listen 80;
    index index.php index.html;
    error_log  /var/log/nginx/error.log;
    access_log /var/log/nginx/access.log;
    root /var/www/public;
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
    }
    location / {
        try_files $uri $uri/ /index.php?$query_string;
        gzip_static on;
    }
}
```

## Configurando MySQL

Creamos el directorio y el archivo de configuración de MySQL

```bash
mkdir shorturl/mysql
nano shorturl/mysql/my.cnf
```
Agreamos las configuraciones a nuestro archivo.
```code
[mysqld]
general_log = 1
general_log_file = /var/lib/mysql/general.log
```

Ahora vamos a crear nuestras variables de entorno de nuestra aplicación shorturl en el archivo .env:

```bash
cp .env.example .env
```

Y le configuramos las variables que hacen sentido con nuestro contenedor:

```code
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=short
DB_USERNAME=root
DB_PASSWORD=root
```

Luego de haber realizado todas estas configuraciones ejecutamos nuestro contenedor.

```bash
docker-compose up -d
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan config:cache
```
Los 2 ultimos comando son para agregar una llave de encripción generada para este contenedor, y cacheamos nuestra configuración.

# Ejecutando las Migraciones

Lo primero que debemos hacer es acceder a la base de datos y crear el usuario root que escribimos en nuestra configuración del archivo .env

```bash
docker-compose exec db bash
mysql -u root -p <root>
```

Una vez dendro desde la consola a nuestro motor de base de datos vamos a crear el usuario a nuestra base de datos short que fue la que nombramos en nuestro archivo docker-composer, esto lo hacemos ejecutando el siquiente Query o Consulta

```bash
mysql> GRANT ALL ON short.* TO 'root'@'%' IDENTIFIED BY 'root';
mysql> FLUSH PRIVILEGES;
mysql> EXIT;
```
Por ultimo salimos de nuestro contenedor y ejecutamos las migraciones.

```bash
docker-compose exec app php artisan migrate
```

Luego de haber ejecutado cada uno de los pasos anteriores ya puedemos usar nuestra aplicación de minificación de url largas.

## Como usar la aplicación

Desde desde un cliente de endpoint como ejemplo POSTMAN o usando la libreria CURL y por ultimo usando una petición HTTP podes usar las siguientes rutas segun el nombre de dominio establecido en el servidor.

## Creación de Url Minificada

Para crear una url minificada usaremos la ruta:

api/v1//link/generate mediante el verbo POST, en el cuerpo de esta petición vamos a enviar la variable url la cual va a contener la url que usted quiere minificar.

En caso de que la variable url esta vacia o no cumpla con el formato de una url valida el API retornara un json con el siguiente formato:

```json
{
    "errors": {
        "url": [
            "The url field is required.",
            "The url format is invalid."
        ]
    }
}
```

En caso de que la url cumpla con las validaciones el API retornara un json con el siguiente formato:

```json
{
    "url": "https://www.facebook.com/",
    "generated": {
        "url": "http://<dominio>.<algo>:<puerto>/B",
        "code": "B"
    }
}
```

## Uso de la Url Minificada

Para poder usar nuestra url minificada deberemos entrar al navegador y usar nuestra url generada

http://dominio.algo/codigo

## Obtener los Top 100 mas Visitadas

Para visualizar el top 100 links mas visitado usaremos la ruta:

api/v1//link/top mediante el verbo GET, el cual devuelve una colección con los datos de los links mas solicitados y la cantidad de click realizados.

```json
{
    "links": [
        {
            "id": 1,
            "url": "https://www.google.com/",
            "code": "A",
            "created_at": "2019-03-11 04:11:24",
            "updated_at": "2019-03-11 04:11:24",
            "histories_count": 4
        },
        {
            "id": 2,
            "url": "https://www.facebook.com/",
            "code": "B",
            "created_at": "2019-03-11 04:11:35",
            "updated_at": "2019-03-11 04:11:35",
            "histories_count": 2
        }
    ]
}
```

## License

The Lumen framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
