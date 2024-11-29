<?php

namespace WPTest\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;
use WPTest\Util\Util;

/**
 * Resets all generated setup and example files
 */
class ResetCommand extends Command
{
    protected function configure()
    {
        $this->setName('reset')
            ->setDescription('Removes all generated setup and example files')
            ->setHelp(<<<EOF
The <info>%command.name%</info> removes all generated setup and example files:

  <info>php %command.full_name%</info>

EOF
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $Util = new Util();
        $project_dir = $Util->getProjectDirectory();
        $output->writeln($project_dir);
        $helper = $this->getHelper('question');
        $question = new Question("Are you sure you want to remove ALL generated files created by wp-test init? [type Yes to confirm]: ", '');
        $confirm = strtolower($helper->ask($input, $output, $question));
        if ($confirm != 'yes') {
            $output->writeln('Exited without removing files');
            return 0;
        }
        $output->writeln('Deleting files:');
        $phpspec_config_file = "$project_dir/phpspec.yml";
        $advanced = false;
        if (file_exists($phpspec_config_file)) {
            $advanced = true;
            $phpspec_config = Yaml::parseFile($phpspec_config_file);
            $suites = (array) $phpspec_config['suites'];
            $suite = reset($suites);
            $path_unit_tests = $suite['spec_path'] . DIRECTORY_SEPARATOR . $suite['spec_prefix'];
            $this->deleteFile("$project_dir/$path_unit_tests/ExampleSpec.php", $output);
            $this->deleteFile("$project_dir/{$suite['spec_path']}/phpspec.php", $output);
            $this->deleteFile($phpspec_config_file, $output);
            @rmdir("$project_dir/$path_unit_tests");
        }
        $phpunit_config_file = "$project_dir/phpunit.xml";
        if (file_exists($phpunit_config_file)) {
            $phpunit_config = simplexml_load_file($phpunit_config_file);
            $phpunit_path = (string) $phpunit_config->xpath('//testsuite[@name=\'default\']/directory')[0];
            $phpunit_bootstrap_path = dirname($phpunit_config->xpath('/phpunit')[0]['bootstrap']);
            $this->deleteFile("$project_dir/$phpunit_path/ExampleTest.php", $output);
            $this->deleteFile("$project_dir/$phpunit_bootstrap_path/phpunit.php", $output);
            $this->deleteFile($phpunit_config_file, $output);
            @rmdir("$project_dir/$phpunit_path");
            @rmdir("$project_dir/$phpunit_bootstrap_path");
        }

        $this->deleteFile("$project_dir/phpunit-watcher.yml", $output);
        $this->deleteFile("$project_dir/.phpspec-watcher.yml", $output);
        $this->deleteFile("$project_dir/wp-tests-config.php", $output);

        $output->writeln('Removing additional dependencies:');
        $packages = $advanced ? ['phpspec/phpspec', 'fetzi/phpspec-watcher', 'cyruscollier/phpspec-php-mock'] : ['spatie/phpunit-watcher'];
        $packages[] = $Util::WP_CORE_PACKAGE;
        $command = sprintf('composer remove %s --dev', implode(' ', $packages));
        $output->write($command . "\n");
        shell_exec($command);

        return 0;
    }

    /**
     * @param string $name
     * @param OutputInterface $output
     */
    protected function deleteFile(string $name, OutputInterface $output)
    {
        if (!file_exists($name)) {
            return;
        }
        unlink($name);
        $output->writeLn($name);
    }
}
