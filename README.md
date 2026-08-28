# S3Service

Clase PHP sencilla para gestionar archivos en Amazon S3 (subir, descargar, borrar y listar).

## Requisitos

- PHP 8.0 o superior
- Composer
- Cuenta de AWS con un bucket S3 creado
- Extensión `curl` habilitada en PHP

## Instalación

1. Instala las dependencias necesarias:

```bash
composer require aws/aws-sdk-php
composer require aws/aws-sdk-php vlucas/phpdotenv

# S3Service

Clase PHP para gestionar archivos en Amazon S3 de forma sencilla.  
Permite **subir**, **descargar**, **borrar** y **listar** archivos usando el AWS SDK oficial.

---

## ¿Qué hace esta clase?

`S3Service` encapsula la lógica de conexión y las operaciones más comunes con S3.  
Internamente utiliza el cliente oficial de AWS (`Aws\S3\S3Client`) y lee las credenciales desde variables de entorno (`.env`).

### Responsabilidades de la clase:

- Crear y configurar el cliente de S3 una sola vez (en el constructor).
- Subir archivos locales a un bucket.
- Descargar archivos del bucket a una ruta local.
- Eliminar objetos del bucket.
- Listar todos los archivos existentes en el bucket.

---

## Estructura del código

```php
namespace src;

class S3Service
{
    private S3Client $s3;      // Cliente de AWS S3
    private string $bucket;    // Nombre del bucket

    public function __construct() { ... }

    public function upload(...) { ... }
    public function download(...) { ... }
    public function delete(...) { ... }
    public function listFiles() { ... }
}