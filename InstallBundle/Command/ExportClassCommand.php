<?php

namespace Factory\InstallBundle\Command;

use Pimcore\Bundle\CoreBundle\Command\Bundle\AbstractBundleCommand;
use Pimcore\Extension\Bundle\PimcoreBundleManager;
use Pimcore\Model\DataObject;
use ReflectionClass;
use Spyc;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Pimcore\Model\DataObject\ClassDefinition\Service;
use Symfony\Component\Finder\Finder;

class ExportClassCommand extends AbstractBundleCommand
{
    protected $bundle;

    public function __construct(PimcoreBundleManager $bundleManager)
    {
        parent::__construct($bundleManager);
    }
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('factory:classes:export')
             ->setDescription('This is a script for classes export')
             ->addArgument('bundle', InputArgument::REQUIRED);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $directory = dirname(__DIR__, 1);

        $this->bundle = $this->resolveBundleClass($input->getArgument('bundle'));

        if (!$this->bundle) {
            $this->io->error('The ' . $input->getArgument('bundle') . ' bundle does not exist');
            return;
        }

        $classes = $this->getClassesToExport();

        if ($classesToExport = $classes['imports']['classes']) {
            $classesExported = $this->exportClasses($classesToExport);
            $classesExported ? $this->io->success('Classes successfully exported') : $this->io->text('No classes to export');
        } else {
            $this->io->text('No classes to export');
        }

        if ($objectBricksToExport = $classes['imports']['object_bricks']) {
            $objectBricksExported = $this->exportObjectBricks($objectBricksToExport);
            $objectBricksExported ? $this->io->success('ObjectBricks successfully exported') : $this->io->text('No object bricks to export');
        } else {
            $this->io->text('No object bricks to export');
        }

        $this->io->text('Files location: ' . $directory . '/export/');
    }

    protected function exportClasses($classes)
    {
        $directory = dirname(__DIR__, 1);

        foreach ($classes as $class) {
            $classDefinition = DataObject\ClassDefinition::getByName($class);
            $classDefinitionJson = Service::generateClassDefinitionJson($classDefinition);

            $file = fopen($directory . "/export/Classes/class_" . $class . "_export.json", "w");

            fwrite(
                $file,
                $classDefinitionJson
            );

            fclose($file);
        }

        return true;
    }

    protected function exportObjectBricks($objectBricks)
    {
        $directory = dirname(__DIR__, 1);

        foreach ($objectBricks as $objectBrick) {
            $objectBrickDefinition = DataObject\Objectbrick\Definition::getByKey($objectBrick);
            $classDefinitionJson = Service::generateObjectBrickJson($objectBrickDefinition);

            $file = fopen($directory . "/export/ObjectBricks/object_brick_" . $objectBrick . "_export.json", "w");

            fwrite(
                $file,
                $classDefinitionJson
            );

            fclose($file);
        }

        return true;
    }

    protected function getClassesToExport()
    {
        $bundleDirectory = $this->getBundleDirectory();
        $filename = $bundleDirectory . '/imports.yml';

        if (file_exists($filename)) {
            $ymlContent = file_get_contents($filename);
            $classesToExportArray = Spyc::YAMLLoad($ymlContent);

            return $classesToExportArray;
        }

        return false;
    }

    protected function getBundleDirectory()
    {
        $reflector = new ReflectionClass($this->bundle);
        return dirname($reflector->getFileName());
    }

    protected function resolveBundleClass($input)
    {
        $bundleName = $this->normalizeBundleIdentifier($input);

        $mapping = $this->getAvailableBundleShortNameMapping($this->bundleManager);

        if (isset($mapping[$bundleName])) {
            return $mapping[$bundleName];
        } else {
            return false;
        }
    }

    private function findInstallFiles(string $directory, string $pattern): array
    {
        $finder = new Finder();
        $finder->files()->in($directory)->name($pattern);
        $results = [];
        foreach ($finder as $file) {
            if (preg_match($pattern, $file->getFilename(), $matches)) {
                $key = $matches[1];
                $results[$key] = $file->getRealPath();
            }
        }

        return $results;
    }

    /**
     * Maps short name without namespace to fully qualified name to avoid having to use the fully qualified name
     * as argument.
     *
     * e.g. PimcoreEcommerceFrameworkBundle => Pimcore\Bundle\EcommerceFrameworkBundle\PimcoreEcommerceFrameworkBundle
     *
     * @param PimcoreBundleManager $bundleManager
     *
     * @return array
     */
    private function getAvailableBundleShortNameMapping(PimcoreBundleManager $bundleManager): array
    {
        $availableBundles = $bundleManager->getAvailableBundles();

        $mapping = [];
        foreach ($availableBundles as $availableBundle) {
            $mapping[$this->getShortClassName($availableBundle)] = $availableBundle;
        }

        return $mapping;
    }
}
