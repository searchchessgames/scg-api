<?php

namespace SearchChessGames\GameBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AmyBoyd\PgnParser\PgnParser;
use \SearchChessGames\GameBundle\Entity\Game;

class ImportPgnCommand extends ContainerAwareCommand
{
    private $importedGamesCount = 0;

    protected function configure()
    {
        $this
            ->setName('chess:import-pgn')
            ->addArgument('filename', InputArgument::REQUIRED, 'Which .pgn file do you want to import?');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $filename = $input->getArgument('filename');
        if (!file_exists($filename)) {
            // Need the file to exist...
            $output->writeln('Error: file does not exist: ' . $filename);

            return;
        }

        // Make the user confirm that they want to import this file.
        $output->writeln('The file/directory is: ' . $filename);
        $dialog = $this->getHelperSet()->get('dialog');
        if (!$dialog->askConfirmation($output, 'Are you sure you want to continue? Enter y or n: ', false)) {
            return;
        }

        ini_set('memory_limit', -1);

        if (is_dir($filename)) {
            $filename = rtrim($filename, '/');
            $files = array();
            $dirHandle = opendir($filename);
            while (false !== ($fileInDir = readdir($dirHandle))) {
                if (array_search($fileInDir, array('.', '..')) === false) {
                    $this->importPgn($filename . '/' . $fileInDir);
                }
            }
            closedir($dirHandle);

            $output->writeln($this->importedGamesCount . ' new games in total');
        } else {
            $this->importPgn($filename);
        }
    }

    private function importPgn($filename)
    {
        $this->output->writeln('Starting ' . $filename);

        $pgnParser = new PgnParser($filename);
        $games = $pgnParser->getGames();

        foreach ($games as $i => $game) {
            $games[$i] = Game::createFromBaseGame($game);
        }

        $this->getContainer()
            ->get('doctrine')
            ->getRepository('SearchChessGamesGameBundle:Game')
            ->setSlugsAndSaveAll($games);

        $this->output->writeln('Imported ' . count($games) . ' games');
        $this->importedGamesCount += count($games);

        unset($pgnParser, $games);
    }

}
