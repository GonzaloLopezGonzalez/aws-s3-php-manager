<?php

namespace src;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class S3Service
{
    private S3Client $s3;
    private string $bucket;

    public function __construct()
    {
        $this->bucket = $_ENV['AWS_BUCKET'];

        $this->s3 = new S3Client([
            'version'     => 'latest',
            'region'      => $_ENV['AWS_REGION'],
            'credentials' => [
                'key'    => $_ENV['AWS_ACCESS_KEY_ID'],
                'secret' => $_ENV['AWS_SECRET_ACCESS_KEY'],
            ],
        ]);
    }

    /**
     * Subir un archivo a S3
     */
    public function upload(string $filePath, ?string $nombreEnS3 = null): string
    {
        // Si no se indica nombre, usamos el nombre original del archivo
        if ($nombreEnS3 === null) {
            $nombreEnS3 = basename($filePath);
        }

        // Opcional: descomenta la línea siguiente si quieres nombres únicos
        // $nombreEnS3 = time() . '_' . $nombreEnS3;

        try {
            $result = $this->s3->putObject([
                'Bucket'     => $this->bucket,
                'Key'        => $nombreEnS3,
                'SourceFile' => $filePath,
                // 'ACL'     => 'public-read', // solo si quieres que sea público
            ]);

            return $result['ObjectURL'];
        } catch (AwsException $e) {
            throw new Exception("Error al subir el archivo: " . $e->getMessage());
        }
    }

    /**
     * Descargar un archivo de S3 y guardarlo en local
     */
    public function download(string $nombreEnS3, string $rutaLocal): bool
    {
        try {
            $this->s3->getObject([
                'Bucket' => $this->bucket,
                'Key'    => $nombreEnS3,
                'SaveAs' => $rutaLocal,
            ]);

            return true;
        } catch (AwsException $e) {
            throw new Exception("Error al descargar el archivo: " . $e->getMessage());
        }
    }

    /**
     * Borrar un archivo de S3
     */
    public function delete(string $nombreEnS3): bool
    {
        try {
            $this->s3->deleteObject([
                'Bucket' => $this->bucket,
                'Key'    => $nombreEnS3,
            ]);

            return true;
        } catch (AwsException $e) {
            throw new Exception("Error al borrar el archivo: " . $e->getMessage());
        }
    }

    /**
    * Listar todos los archivos del bucket
    */
    public function listFiles(): array
    {
        try {
            $result = $this->s3->listObjectsV2([
                'Bucket' => $this->bucket,
            ]);

            $files = [];
            if (isset($result['Contents'])) {
                foreach ($result['Contents'] as $object) {
                    $files[] = $object['Key'];
                }
            }
            return $files;
        } catch (AwsException $e) {
            throw new Exception("Error al listar archivos: " . $e->getMessage());
        }
    }
}
