<?php

namespace Factory\InstallBundle\Command;

use Factory\InstallBundle\FactoryInstallBundle;
use Pimcore\Console\AbstractCommand;
use Pimcore\Model\DataObject;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Pimcore\Model\DataObject\ClassDefinition\Service;
use Symfony\Component\Finder\Finder;

class ImportClassCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('factory:classes:import')
            ->setDescription('This is a script for classes import');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->importClasses();
        $this->importObjectBricks();

        $this->io->success('Class successfully imported');
    }

    protected function importObjectBricks()
    {
        $objectBricksNames = $this->getObjectBrickNames();

        foreach ($objectBricksNames as $className => $file) {
            $handle = fopen($file,"r");
            $data = fread($handle,filesize($file));
            $objectBrick = new DataObject\Objectbrick\Definition();
            $objectBrick->setKey($className);

            $success = Service::importObjectBrickFromJson($objectBrick, $data);

            if (!$success) {
                throw new \Exception(sprintf(
                    'Failed to create object brick "%s"',
                    $className
                ));
            }
        }
    }

    protected function importClasses()
    {
        $classNames = $this->getClassNames();

        foreach ($classNames as $className => $file) {
            $handle = fopen($file,"r");
            $data = fread($handle,filesize($file));
            $class = new DataObject\ClassDefinition();
            $class->setName($className);

            $success = Service::importClassDefinitionFromJson($class, $data);

            if (!$success) {
                throw new \Exception(sprintf(
                    'Failed to create class "%s"',
                    $className
                ));
            }
        }
    }

    protected function getClassNames()
    {
        $directory = dirname(__DIR__, 1);

        $fieldCollections = $this->findInstallFiles(
            $directory . "/export/Classes",
            '/^class_(.*)_export\.json$/'
        );

        return $fieldCollections;
    }

    protected function getObjectBrickNames()
    {
        $directory = dirname(__DIR__, 1);

        $fieldCollections = $this->findInstallFiles(
            $directory . "/export/ObjectBricks",
            '/^class_(.*)_export\.json$/'
        );

        return $fieldCollections;
    }

    private function findInstallFiles(string $directory, string $pattern): array
    {
        $finder = new Finder();
        $finder
            ->files()
            ->in($directory)
            ->name($pattern);
        $results = [];
        foreach ($finder as $file) {
            if (preg_match($pattern, $file->getFilename(), $matches)) {
                $key = $matches[1];
                $results[$key] = $file->getRealPath();
            }
        }
        return $results;
    }
}
