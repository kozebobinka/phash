<?php

namespace App\Command;

use App\Entity\Picture;
use App\Service\Phash;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

#[AsCommand(
    name: 'app:load-images',
)]
class LoadImagesCommand extends Command
{
    public const PATH = __DIR__.'/../../public/images';

    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $phash = new Phash();

        $finder = new Finder();
        $finder->files()->in(self::PATH);

        foreach ($finder as $file) {
            $picture = (new Picture())
                ->setPath($file->getFilename())
                ->setHash($phash->getHash(self::PATH.'/'.$file->getFilename()));
            $this->entityManager->persist($picture);
        }

        $this->entityManager->flush();

        $io->success('Done.');

        return Command::SUCCESS;
    }
}
