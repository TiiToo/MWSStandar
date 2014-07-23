<?php

namespace MwsStandar\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;



class Command extends ContainerAwareCommand
{
    
    protected function configure()
    {
        $this
            ->setName('MWS:Standar')
            ->setDescription('Instala Los Elementos necesarios para la Utilizacion de Code Sniffer y Pre-Commit')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
            $pearDir = exec('pear config-get php_dir');
            $phpDir = substr($pearDir,0,-4);
            $MWSHook = __DIR__. '/../StandarScript/hook.php';
            $defaulHook = __DIR__.'/../../../../../../.git/hooks/pre-commit';
           
            $output->writeln('Instalando PHP_CodeSniffer');
            exec('pear install PHP_CodeSniffer');
            
            $output->writeln('Configurando Dependencias PHP_CodeSniffer');
            exec('pear channel-discover pear.phpmd.org');
            exec('pear channel-discover pear.pdepend.org');
            
            $output->writeln('Instalando PHPMD');
            exec('pear install --alldeps phpmd/PHP_PMD');
            
            $output->writeln('Clonando Standar de Symfony2');
            exec('git clone git://github.com/opensky/Symfony2-coding-standard.git ' . $pearDir . '/PHP/CodeSniffer/Standards/Symfony2');
            
            $output->writeln('Estableciendo Standar Symfony2 como Principal');
            exec($phpDir . 'phpcs --config-set default_standard Symfony2');
            $output->writeln('Listo!');
            
            if (!copy($MWSHook, $defaulHook)) {
                $output->writeln("Error al Actualizar Pre-Commit MWS HOOK\n");
            }else{
                $output->writeln("Pre-Commit MWS HOOK Actualizado Correctamente\n");
            }
    }
  }

?>
