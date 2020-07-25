<?php

namespace WPTest\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use WPTest\Util\Util;


/**
 * Main command, initializes default config files in project root
 */
class InitCommand extends Command
{
    protected $Util;
    protected $project_dir;
    protected $wp_tests_dir;
    protected $templates_dir;
    protected $path_wp_tests;
    protected $vendor_path;
    protected $path_wp_develop;
    protected $unit_tests_prefix = 'Test';

    public function __construct(string $name = null)
    {
        parent::__construct($name);
        $this->Util = new Util();
        $this->project_dir = $this->Util->getProjectDirectory();
        $this->wp_tests_dir = dirname(dirname(__DIR__));
        $this->templates_dir = $this->wp_tests_dir . '/templates';
        $parts = explode($this->project_dir, $this->wp_tests_dir);
        $this->path_wp_tests = ltrim(end($parts), '/') ?: '.';
        $this->vendor_path = $this->Util->getVendorDirectory();
        $this->path_wp_develop = $this->Util->getWPDevelopDirectory();
    }

    protected function configure()
    {
        $this->setName('init')
            ->setDescription('Initializes default config files in project root')
            ->setHelp(<<<EOF
The <info>%command.name%</info> initializes default config files in project root:

  <info>php %command.full_name%</info>

Will setup and copy test environment config files into the project's root directory.

This command WILL overwrite any existing config files.
EOF
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln($this->project_dir);
        $default_namespace = $this->Util->getPSR4Namespace();
        $default_src = $this->Util->getPSR4Source();

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
        $namespace_relative = $namespace ? '\\' . $namespace : '';
        $suite = $namespace ? strtolower($namespace) : 'main';

        $question = new Question("Source files path [$default_src]: ", $default_src);
        $source_path = $helper->ask($input, $output, $question);

        $tests_path = ltrim(dirname($source_path) . '/tests', './');
        $default_unit_path = $tests_path . '/' . ($advanced ? 'unit' : 'phpunit');
        $question = new Question("Path to unit tests [$default_unit_path]: ", $default_unit_path);
        $path_unit_tests = $helper->ask($input, $output, $question);
        $path_integration_tests = '';

        if ($advanced) {
            $default_integration_path = $tests_path . '/integration';
            $question = new Question("Path to integration tests [$default_integration_path]: ", $default_integration_path);
            $path_integration_tests = $helper->ask($input, $output, $question);
        }
        $phpunit_path = $advanced ? $path_integration_tests : $path_unit_tests;
        $phpunit_bootstrap_path = dirname($phpunit_path);

        $default_wp_content = $this->Util->getWPContentDirectory();
        $question = new Question("Path to wp-content directory, relative to project root [$default_wp_content]: ", $default_wp_content);
        $path_wp_content = $helper->ask($input, $output, $question);

        $default_active_theme = $this->Util->getWPActiveTheme();
        $question = new Question("Active theme [$default_active_theme]: ", $default_active_theme);
        $active_theme = $helper->ask($input, $output, $question);

        $this->makeDirectory("$this->project_dir/$path_unit_tests");
        $output->writeln('Generating files:');
        if ($advanced) {
            $this->makeDirectory("$this->project_dir/$path_integration_tests");
            $this->generateFile("$this->project_dir/phpspec.yml", $output, compact(
                'path_unit_tests', 'source_path', 'namespace', 'suite'
            ));
            $this->generateFile("$this->project_dir/$path_unit_tests/ExampleSpec.php", $output, compact(
                'namespace', 'namespace_relative'
            ));
        }

        $this->generateFile("$this->project_dir/phpunit.xml", $output, compact(
            'path_unit_tests', 'active_theme', 'phpunit_bootstrap_path'
        ));
        $this->generateFile("$this->project_dir/$phpunit_bootstrap_path/phpunit.php", $output);

        $this->generateFile("$this->project_dir/phpunit-watcher.yml", $output, compact(
            'path_unit_tests', 'source_path'
        ));
        $this->generateFile("$this->project_dir/$phpunit_path/ExampleTest.php", $output, compact('namespace_relative'));
        $this->generateFile("$this->project_dir/wp-tests-config.php", $output, compact('path_wp_content'));

        $output->writeln('');
        if ($advanced) {
            $output->writeln('Next, install phpspec and the function mocking extension:');
            $output->writeln('> composer require phpspec/phpspec cyruscollier/phpspec-php-mock --dev');
        }

        return 0;
    }

    /**
     * @param string $name
     * @param OutputInterface $output
     * @param array $vars
     */
    protected function generateFile(string $name, OutputInterface $output, array $vars = [])
    {
        $template_file = $this->templates_dir . DIRECTORY_SEPARATOR . basename($name) . '.tpl';
        $template = new \Text_Template($template_file);
        $template_vars = get_object_vars($this);
        unset($template_vars['Util']);
        $template->setVar(array_merge($template_vars, $vars));
        $template->renderTo($name);
        $output->writeln($name);
    }

    /**
     * @param string $name
     */
    protected function makeDirectory(string $name)
    {
        if (!is_dir($name)) {
            mkdir($name, 0777, true);
        }
    }
}
