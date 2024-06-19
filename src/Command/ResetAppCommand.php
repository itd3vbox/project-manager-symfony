<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


#[AsCommand(
    name: 'app:reset',
    description: 'Reset the entire database and delete project folders.',
)]
class ResetAppCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private Filesystem $filesystem;
    private ParameterBagInterface $parameterBag;

    public function __construct(
        EntityManagerInterface $entityManager, 
        Filesystem $filesystem,
        ParameterBagInterface $parameterBag,
    )
    {
        $this->entityManager = $entityManager;
        $this->filesystem = $filesystem;
        $this->parameterBag = $parameterBag;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Reset the entire database and delete project folders.')
            ->setHelp('This command deletes all data from all tables in the database and deletes project folders.');
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $allMetadata = $this->entityManager->getMetadataFactory()->getAllMetadata();

        foreach ($allMetadata as $metadata) 
        {
            $className = $metadata->getName();
            $repository = $this->entityManager->getRepository($className);
            $entities = $repository->findAll();

            foreach ($entities as $entity) {
                $this->entityManager->remove($entity);
                $io->writeln("Deleted entity of class: $className");
            }
        }

        $this->entityManager->flush();
        
        $projectsFolderPath = $this->parameterBag->get('kernel.project_dir') . '/public/projects';
        if ($this->filesystem->exists($projectsFolderPath)) {
            $this->filesystem->remove($projectsFolderPath);
            $io->writeln("Deleted project folders at: $projectsFolderPath");
        }

        $automatesFolderPath = $this->parameterBag->get('kernel.project_dir') . '/public/automates';
        if ($this->filesystem->exists($automatesFolderPath)) {
            $this->filesystem->remove($automatesFolderPath);
            $io->writeln("Deleted automate folders at: $automatesFolderPath");
        }

        $io->success('Database reset and project folders deleted successfully.');

        return Command::SUCCESS;
    }
}
