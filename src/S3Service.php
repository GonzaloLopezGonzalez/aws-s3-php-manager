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
            throw new \Exception("Error al subir el archivo: " . $e->getMessage());
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
            throw new \Exception("Error al descargar el archivo: " . $e->getMessage());
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
            throw new \Exception("Error al borrar el archivo: " . $e->getMessage());
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
            throw new \Exception("Error al listar archivos: " . $e->getMessage());
        }
    }

        /**
         * Valida si un nombre es válido para un bucket de Amazon S3
         * según las reglas básicas:
         * - Longitud entre 3 y 63 caracteres
         * - Solo letras minúsculas, números, puntos y guiones
         * - Debe empezar y terminar con letra o número
         */
        private function isValidS3BucketName(string $name): bool
        {
            // 1. Longitud
            $length = strlen($name);
            if ($length < 3 || $length > 63) {
                return false;
            }

            // 2. Solo caracteres permitidos: a-z, 0-9, . y -
            if (!preg_match('/^[a-z0-9.-]+$/', $name)) {
                return false;
            }

            // 3. Debe empezar y terminar con letra o número
            if (!preg_match('/^[a-z0-9]/', $name) || !preg_match('/[a-z0-9]$/', $name)) {
                return false;
            }

            return true;
        }

        public function createBucket(string $bucketName): bool
        {
            // 1. Validar el nombre del bucket primero
            if (!$this->isValidS3BucketName($bucketName)) {
                throw new \Exception("El nombre del bucket no es válido. Debe tener entre 3 y 63 caracteres, solo minúsculas, números, puntos y guiones, y empezar/terminar con letra o número.");
            }

            try {
                $params = [
                    'Bucket' => $bucketName,
                ];

                // Si la región NO es us-east-1, es obligatorio indicar LocationConstraint
                $region = $_ENV['AWS_REGION'] ?? 'us-east-1';
                if ($region !== 'us-east-1') {
                    $params['CreateBucketConfiguration'] = [
                        'LocationConstraint' => $region,
                    ];
                }

                $this->s3->createBucket($params);

                // Esperar a que el bucket esté disponible
                $this->s3->waitUntil('BucketExists', [
                    'Bucket' => $bucketName,
                ]);

                return true;

            } catch (AwsException $e) {
                throw new \Exception("Error al crear el bucket: " . $e->getMessage());
            }
        }

        /**
         * Obtener todos los buckets de la cuenta
         */
        public function listBuckets(): array
        {
            try {
                $result = $this->s3->listBuckets();

                $buckets = [];
                if (isset($result['Buckets'])) {
                    foreach ($result['Buckets'] as $bucket) {
                        $buckets[] = [
                            'name'         => $bucket['Name'],
                            'creation_date'=> $bucket['CreationDate']->format('Y-m-d H:i:s'),
                        ];
                    }
                }

                return $buckets;

            } catch (AwsException $e) {
                throw new \Exception("Error al listar los buckets: " . $e->getMessage());
            }
        }
    
}
