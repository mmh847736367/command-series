<?php


namespace Acme;


use GuzzleHttp\ClientInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ZipArchive;

class newCommand extends Command
{
    private $client;

    public function __construct(ClientInterface $client, $name = null)
    {
        parent::__construct($name);

        $this->client = $client;
    }

    public function configure()
    {
        $this->setName('newCommand')
            ->setDescription('Create a new laravel application')
            ->addArgument('name', InputArgument::REQUIRED);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $directory = getcwd() . '/' . $input->getArgument('name');
        $this->assertApplicationDoesNotExits($directory, $output);
        $this->download($zipFile =  $this->makeFileName())
             ->extract($zipFile, $directory)
             ->cleanUp($zipFile);

        $output->writeln('<comment>Application ready!</comment>');
    }

    private function assertApplicationDoesNotExits($directory, OutputInterface $output)
    {
        if(is_dir($directory)) {
            $output->writeln('<error>Application already exists!</error>');
            exit(1);
        }
    }

    private function download($zipFile)
    {
        $response = $this->client->get('http://cabinet.laravel.com/latest.zip')->getBody();
        file_put_contents($zipFile, $response);

        return $this;
    }

    private function makeFileName()
    {
        return getcwd() . '/laravel_' . md5(time().uniqid( )) . '.zip';
    }

    /**
     * @param $zipFile
     * @param $directory
     * @return $this
     */
    private function extract($zipFile, $directory)
    {
        $archive = new ZipArchive();

        $archive->open($zipFile);

        $archive->extractTo($directory);

        $archive->close();

        return $this;
    }

    /**
     * @param $zipFile
     * @return $this
     */
    private function cleanUp($zipFile)
    {
        @chmod($zipFile, 0777);
        @unlink($zipFile);

        return $this;
    }
}



