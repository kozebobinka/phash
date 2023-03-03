<?php

namespace App\Command;

use App\Entity\Picture;
use Doctrine\ORM\EntityManagerInterface;
use Jenssegers\ImageHash\ImageHash;
use Jenssegers\ImageHash\Implementations\DifferenceHash;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

#[AsCommand(
    name: 'app:load-images',
)]
class LoadImagesCommand extends Command
{
    public const PATH = __DIR__.'/../../public/_img';

    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $finder = new Finder();
        $finder->files()->in(self::PATH);

        $progressBar = new ProgressBar($output, $finder->count());
        $progressBar->setFormat('debug');
        $finder->count();

        $hasher = new ImageHash(new DifferenceHash());

        $i = 0;
        $progressBar->start();

        foreach ($finder as $file) {
            $picture = (new Picture())
                ->setPath($file->getFilename())
                ->setHash($hasher->hash(self::PATH.'/'.$file->getFilename())->toHex());
            $this->entityManager->persist($picture);

            if ($i++ % 1000 === 0) {
                $this->entityManager->flush();
                $this->entityManager->clear();
            }

            $progressBar->advance();
        }

        $progressBar->finish();

        $this->entityManager->flush();

        $io->success('Done');

        return Command::SUCCESS;
    }
}
