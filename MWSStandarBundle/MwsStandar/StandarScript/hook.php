#!/usr/bin/php
<?php
require __DIR__ . '/../../../../../../vendor/autoload.php';
 
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Console\Application;
 
class CodeQualityTool extends Application
{
    private $output;
    private $input;
 
    private $phpDir;
    
    const PHP_FILES_IN_SRC = '/^src\/(.*)(\.php)$/';
    const PHP_FILES_IN_CLASSES = '/^classes\/(.*)(\.php)$/';
 
    public function __construct()
    {
        parent::__construct('MWS Code Standar Tool', '1.0');
        
        $pearDir = exec('pear config-get php_dir');
        $this->phpDir = substr($pearDir,0,-5);
            
    }
 
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
 
        $output->writeln('<fg=white;options=bold;bg=green> Code Quality Tool</fg=white;options=bold;bg=white>');
        $output->writeln('<info>Obteniendo Archivos Modificados</info>');
        $files = $this->extractCommitedFiles();
 
        $output->writeln('<info>Comprobando composer</info>');
        $this->checkComposer($files);
 
        $output->writeln('<info>Executando PHPLint</info>');
        if (!$this->phpLint($files)) {
            throw new Exception('ERROR de Syntaxis PHP!');
        }
 /*
        $output->writeln('<info>Comprobando Code Style</info>');
        if (!$this->codeStyle($files)) {
            throw new Exception(sprintf('Error en la Validacion!'));
        }
 */
        $output->writeln('<info>Comprobando Code Style con PHPCS</info>');
        if (!$this->codeStylePsr($files)) {
            throw new Exception(sprintf('Error en la Validacion PHPCS!'));
        }
 
        $output->writeln('<info>Comprobando Code Style con PHPMD</info>');
        if (!$this->phPmd($files)) {
            throw new Exception(sprintf('Error en la Validacion PHPMD!'));
        }
/* 
        $output->writeln('<info>Executando Unit</info>');
        if (!$this->unitTests()) {
            throw new Exception('Error en PhpUnit!');
        }
 */
        $output->writeln('<info>Las Validaciones en el Codigo son Correctas!</info>');
    }
 
    private function checkComposer($files)
    {
        $composerJsonDetected = false;
        $composerLockDetected = false;
 
        foreach ($files as $file) {
            if ($file === 'composer.json') {
                $composerJsonDetected = true;
            }
 
            if ($file === 'composer.lock') {
                $composerLockDetected = true;
            }
        }
 
        if ($composerJsonDetected && !$composerLockDetected) {
            throw new Exception('composer.lock debe ser commited si se modifica composer.json!');
        }
    }
 
    private function extractCommitedFiles()
    {
        $output = array();
        $rc = 0;
 
        exec('git rev-parse --verify HEAD 2> /dev/null', $output, $rc);
 
        $against = '4b825dc642cb6eb9a060e54bf8d69288fbee4904';
        if ($rc == 0) {
            $against = 'HEAD';
        }
 
        exec("git diff-index --cached --name-status $against | grep '^(A|M)' | awk '{print $2;}'", $output);
 
        return $output;
    }
 
    private function phpLint($files)
    {
        $needle = '/(\.php)|(\.inc)$/';
        $succeed = true;
 
        foreach ($files as $file) {
            if (!preg_match($needle, $file)) {
                continue;
            }
 
            $processBuilder = new ProcessBuilder(array('php', '-l', $file));
            $process = $processBuilder->getProcess();
            $process->run();
 
            if (!$process->isSuccessful()) {
                $this->output->writeln($file);
                $this->output->writeln(sprintf('<error>%s</error>', trim($process->getErrorOutput())));
 
                if ($succeed) {
                    $succeed = false;
                }
            }
        }
 
        return $succeed;
    }
 
    private function phPmd($files)
    {
        $needle = self::PHP_FILES_IN_SRC;
        $succeed = true;
        $rootPath = realpath(__DIR__ . '/../../');
 
        foreach ($files as $file) {
            if (!preg_match($needle, $file)) {
                continue;
            }
            
            if (strncasecmp(PHP_OS, 'WIN', 3) == 0) {
                $processBuilder = new ProcessBuilder(['php', $this->phpDir . '/phpmd', $file, 'text', 'controversial']);
            }else{
                $processBuilder = new ProcessBuilder(['php', '/bin/phpmd', $file, 'text', 'controversial']);
            }
            
            $processBuilder->setWorkingDirectory($rootPath);
            $process = $processBuilder->getProcess();
            $process->run();
 
            if (!$process->isSuccessful()) {
                $this->output->writeln($file);
                $this->output->writeln(sprintf('<error>%s</error>', trim($process->getErrorOutput())));
                $this->output->writeln(sprintf('<info>%s</info>', trim($process->getOutput())));
                if ($succeed) {
                    $succeed = false;
                }
            }
        }
 
        return $succeed;
    }
 
    private function unitTests()
    {
        if (strncasecmp(PHP_OS, 'WIN', 3) == 0) {
            $processBuilder = new ProcessBuilder(array('php', $this->phpDir . '/phpunit'));
        }else{
            $processBuilder = new ProcessBuilder(array('php', '/bin/phpunit'));
        }
        $processBuilder->setWorkingDirectory(__DIR__ . '/../..');
        $processBuilder->setTimeout(3600);
        $phpunit = $processBuilder->getProcess();
 
        $phpunit->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });
 
        return $phpunit->isSuccessful();
    }
 
    private function codeStyle(array $files)
    {
        $succeed = true;
 
        foreach ($files as $file) {
            $classesFile = preg_match(self::PHP_FILES_IN_CLASSES, $file);
            $srcFile = preg_match(self::PHP_FILES_IN_SRC, $file);
 
            if (!$classesFile && !$srcFile) {
                continue;
            }
 
            $fixers = '-psr0';
            if ($classesFile) {
                $fixers = 'eof_ending,indentation,linefeed,lowercase_keywords,trailing_spaces,short_tag,php_closing_tag,extra_empty_lines,elseif,function_declaration';
            }
            if (strncasecmp(PHP_OS, 'WIN', 3) == 0) {
                $processBuilder = new ProcessBuilder(array('php', $this->phpDir . '/php-cs-fixer', '--dry-run', '--verbose', 'fix', $file, '--fixers='.$fixers));
            }else{
                $processBuilder = new ProcessBuilder(array('php', '/bin/php-cs-fixer', '--dry-run', '--verbose', 'fix', $file, '--fixers='.$fixers));

            }
            
            $processBuilder->setWorkingDirectory(__DIR__ . '/../../');
            $phpCsFixer = $processBuilder->getProcess();
            $phpCsFixer->run();
 
            if (!$phpCsFixer->isSuccessful()) {
                $this->output->writeln(sprintf('<error>%s</error>', trim($phpCsFixer->getOutput())));
 
                if ($succeed) {
                    $succeed = false;
                }
            }
        }
 
        return $succeed;
    }
 
    private function codeStylePsr(array $files)
    {
        $succeed = true;
        $needle = self::PHP_FILES_IN_SRC;
 
        foreach ($files as $file) {
            if (!preg_match($needle, $file)) {
                continue;
            }
 
            if (strncasecmp(PHP_OS, 'WIN', 3) == 0) {
                $processBuilder = new ProcessBuilder(array('php', $this->phpDir . '/phpcs', '--standard=Symfony2', $file));
            }else{
                $processBuilder = new ProcessBuilder(array('php', '/bin/phpcs', '--standard=Symfony2', $file));
            }
            $processBuilder->setWorkingDirectory(__DIR__ . '/../../');
            $phpCsFixer = $processBuilder->getProcess();
            $phpCsFixer->run();
 
            if (!$phpCsFixer->isSuccessful()) {
                $this->output->writeln(sprintf('<error>%s</error>', trim($phpCsFixer->getOutput())));
 
                if ($succeed) {
                    $succeed = false;
                }
            }
        }
 
        return $succeed;
    }
}
 
$console = new CodeQualityTool();
$console->run();