<?php

namespace App\Command;

use App\Repository\PictureRepository;
use App\Service\Phash;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:check-image',
)]
class CheckImageCommand extends Command
{
    public function __construct(private PictureRepository $pictureRepository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('im', InputArgument::REQUIRED, 'Image for check')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $im = $input->getArgument('im');

        $phash = new Phash();
        $hashForCheck = $phash->getHash(LoadImagesCommand::PATH.'/'.$im, false);

        $pictures = $this->pictureRepository->findAll();
        foreach ($pictures as $picture) {
            $io->writeln($picture->getPath() . '::: ' . $phash->getSimilarity($hashForCheck, $picture->getHash(), 'BITS'));
        }

//        $io->success(\sprintf('Same image is %s', $filename));

        return Command::SUCCESS;
    }
}
