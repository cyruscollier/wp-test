<?php

namespace WPTest\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use WPTest\Util\Util;


/**
 * Main command, initializes default config files in project root
 */
class InitCommand extends Command
{
    protected function configure()
    {
        $this->setName('init')
            ->setDescription('Initializes default config files in project root')
            ->setHelp(<<<EOF
The <info>%command.name%</info> initializes default config files in project root:

  <info>php %command.full_name%</info>

Will setup and copy test environment config files into the project's root directory.

This command will not overwrite any existing config files; 
to reset to the default files, you must delete the existing files first
EOF
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $Util = new Util();
        $project_dir = $Util->getProjectDirectory();
        $output->writeln($project_dir);
        $default_namespace = $Util->getPSR4Namespace();
        $default_src = $Util->getPSR4Source();

        $helper = $this->getHelper('question');

        $choices = [
            '1' => 'Basic (default): Run WordPress application using PHPUnit for unit and integration tests.',
            '2' => 'Advanced TDD: Dependency-free unit tests using phpspec. Default setup for integration tests.'
        ];
        $question = new ChoiceQuestion(
            'Choose Unit Testing Architecture:',
            $choices,
            '1'
        );
        $question->setErrorMessage('Invalid Selection');
        $advanced = $helper->ask($input, $output, $question);
        $advanced = array_flip($choices)[$advanced] == '2';
        $question = new Question("Project namespace (PSR-4) [$default_namespace]: ", $default_namespace);
        $namespace = $helper->ask($input, $output, $question);
        $suite = $namespace ? strtolower($namespace) : 'main';

        $question = new Question("Source files path [$default_src]: ", $default_src);
        $source_path = $helper->ask($input, $output, $question);

        $tests_path = dirname($source_path) . '/tests';
        $default_unit_path = $tests_path . '/' . ($advanced ? 'unit' : 'phpunit');
        $question = new Question("Path to unit tests [$default_unit_path]: ", $default_unit_path);
        $path_unit_tests = $helper->ask($input, $output, $question);

        if ($advanced) {
            $default_integration_path = $tests_path . '/integration';
            $question = new Question("Path to integration tests [$default_integration_path]: ", $default_integration_path);
            $path_integration_tests = $helper->ask($input, $output, $question);
        }
        $phpunit_path = $advanced ? $path_integration_tests : $path_unit_tests;
        $phpunit_bootstrap_path = dirname($phpunit_path);
        $path_parts = explode(DIRECTORY_SEPARATOR, $path_unit_tests);
        $unit_tests_path = $path_parts[0];
        $unit_tests_prefix = isset($path_parts[1]) ? $path_parts[1] : '';

        $default_wp_content = $Util->getWPContentDirectory();
        $question = new Question("Path to wp-content directory, relative to project root [$default_wp_content]: ", $default_wp_content);
        $path_wp_content = $helper->ask($input, $output, $question);

        $default_active_theme = $Util->getWPActiveTheme();
        $question = new Question("Active theme [$default_active_theme]: ", $default_active_theme);
        $active_theme = $helper->ask($input, $output, $question);

        $path_wp_develop = $Util->getWPDevelopDirectory();

        $wp_tests_dir = dirname(dirname(__DIR__));
        $template_dir = $wp_tests_dir . '/templates';
        $parts = explode($project_dir, $wp_tests_dir);
        $path_wp_tests = ltrim(end($parts), '/');
        if (empty($path_wp_tests)) {
            $path_wp_tests = '.';
        }
        $unit_test_full_path = "$project_dir/$path_unit_tests";
        if (!is_dir($unit_test_full_path)) {
            mkdir($unit_test_full_path, 0777, true);
        }
        if ($advanced) {
            $integration_test_full_path = "$project_dir/$path_integration_tests";
            if (!is_dir($integration_test_full_path)) {
                mkdir($integration_test_full_path, 0777, true);
            }

            $phpspec_config = new \Text_Template("$template_dir/phpspec.yml.tpl");
            $phpspec_config->setVar(compact('unit_tests_path', 'source_path', 'unit_tests_prefix', 'namespace', 'path_wp_tests', 'suite'));
            $phpspec_config->renderTo("$project_dir/phpspec.yml");

            $example_spec = new \Text_Template("$template_dir/ExampleSpec.php.tpl");
            $example_spec->setVar(compact('namespace', 'unit_tests_prefix'));
            $example_spec->renderTo("$project_dir/$path_unit_tests/ExampleSpec.php");
        }

        $phpunit_config = new \Text_Template("$template_dir/phpunit.xml.tpl");
        $phpunit_config->setVar(compact('path_unit_tests', 'path_wp_develop', 'path_wp_tests', 'active_theme', 'phpunit_bootstrap_path'));
        $phpunit_config->renderTo("$project_dir/phpunit.xml");
        
        $phpunit_bootstrap = new \Text_Template("$template_dir/phpunit.php.tpl");
        $phpunit_bootstrap->renderTo("$project_dir/$phpunit_bootstrap_path/phpunit.php");

        $example_test = new \Text_Template("$template_dir/ExampleTest.php.tpl");
        $example_test->setVar(compact('namespace', 'unit_tests_prefix'));
        $example_test->renderTo("$project_dir/$phpunit_path/ExampleTest.php");

        $wp_tests_config = new \Text_Template("$template_dir/wp-tests-config.php.tpl");
        $wp_tests_config->setVar(compact('path_wp_develop', 'path_wp_content'));
        $wp_tests_config->renderTo("$project_dir/wp-tests-config.php");

        if ($advanced) {
            $output->writeln('Next, install phpspec and the function mocking extension:');
            $output->writeln('> composer require phpspec/phpspec cyruscollier/phpspec-php-mock --dev');
        }


        return 0;
    }
}
