<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

// Get the kernel
$kernel = new \App\Kernel('test', true);
$kernel->boot();

// Drop and recreate the test database schema
$entityManager = $kernel->getContainer()->get('doctrine')->getManager();
$connection = $entityManager->getConnection();

// Drop all tables
$schemaManager = $connection->createSchemaManager();
$tables = $schemaManager->listTableNames();
foreach ($tables as $table) {
    $connection->executeStatement('DROP TABLE IF EXISTS ' . $table . ' CASCADE');
}

// Create schema
$metadata = $entityManager->getMetadataFactory()->getAllMetadata();
$schemaTool = new \Doctrine\ORM\Tools\SchemaTool($entityManager);
$schemaTool->createSchema($metadata);

// Close the kernel
$kernel->shutdown();
