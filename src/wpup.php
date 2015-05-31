<?php

/**
 * Wordpress Installer
 * @author Samet Aydemir <sametaydemir@yandex.com>
 */

namespace wpup;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use GuzzleHttp\ClientInterface;
use ZipArchive;

class wpup extends Command{

    private $client;

    public function __construct(ClientInterface $client){
        $this->client = $client;
        parent::__construct();

    }


    public function configure(){

        $this->setName('new')
             ->setDescription('Create a new Wordpress blog.')
             ->addArgument('name', InputArgument::REQUIRED);
    }


    public function execute(InputInterface $input, OutputInterface $output){

        $dir = getcwd() . '/' . $input->getArgument('name');

        $output->writeln('<info>Downloading WordPress...</info>');

        $this->notExist($dir,$output);

        $this->downloadZipFile($zip = $this->fileName())
             ->extractZipFile($zip, $dir)
             ->cleanZipFile($zip);

        $output->writeln('<comment>Wordpress blog ready!</comment>');

    }


    private function notExist($dir, OutputInterface $output){

        if(is_dir($dir)){

            $output->writeln('<error>Application already exists!</error>');
            exit(1);

        }

    }


    private function fileName(){
        return getcwd() . '/wpup_' . md5(time().uniqid()) . '.zip';
    }


    private function downloadZipFile($zip){
        // https://wordpress.org/latest.zip
        $request = $this->client->get('https://wordpress.org/latest.zip')->getBody();
        file_put_contents($zip,$request);
        return $this;
    }


    private function extractZipFile($zip, $dir){
    	
    	$temp = 'temp'.md5(time().uniqid());

        $arch = new ZipArchive;

        $arch->open($zip);
        $arch->extractTo($temp);
        $arch->close();
        
        rename($temp.DIRECTORY_SEPARATOR.'wordpress', $dir);
        @rmdir($temp);

        return $this;
    }


    private function cleanZipFile($zip){
        @chmod($zip,0777);
        @unlink($zip);

        return $this;
    }

}