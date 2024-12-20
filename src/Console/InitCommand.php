<?php

namespace WPTest\Console;

use SebastianBergmann\Template\Template;
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
    protected $vendor_path;
    protected $path_wp_tests;
    protected $tests_include_path;

    public function __construct(string $name = null)
    {
        parent::__construct($name);
        $this->Util = new Util();
        $this->project_dir = $this->Util->getProjectDirectory();
        $this->wp_tests_dir = dirname(dirname(__DIR__));
        $this->templates_dir = $this->wp_tests_dir . '/templates';
        $parts = explode($this->project_dir, $this->wp_tests_dir);
        $this->path_wp_tests = ltrim(end($parts), '/') ?: '.';
        $this->vendor_path = $this->Util->getVendorPath();
        $this->tests_include_path = $this->Util->getTestsIncludesPath();
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
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln($this->project_dir);
        $default_namespace = $this->Util->getPSR4Namespace();
        $default_src = $this->Util->getPSR4Source();
        $helper = $this->getHelper('question');

        $choices = [
            '1' => 'Basic (default): Run WordPress application using PHPUnit for unit and integration tests.',
            '2' => 'Advanced TDD: Dependency-free unit tests using phpspec. Default setup for integration tests.'
        ];
        $question = new ChoiceQuestion('Choose Unit Testing Architecture:', $choices, '1');
        $question->setErrorMessage('Invalid Selection');
        $advanced = $helper->ask($input, $output, $question);
        $advanced = array_flip($choices)[$advanced] == '2';
        $question = new Question("Project namespace (PSR-4) [$default_namespace]: ", $default_namespace);
        if ($advanced) {
            $question->setValidator(function ($value) {
                if (trim($value) == '') {
                    throw new \Exception('Advanced TDD architecture requires a namespace.');
                }
                return $value;
            });
        }
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


        $default_wp_core = $this->Util->getWPCorePath();
        $question = new Question("Path to WordPress Core directory, relative to project root [$default_wp_core]: ", $default_wp_core);
        $wp_core_path = $helper->ask($input, $output, $question);

        $default_wp_content = $this->Util->getWPContentPath($wp_core_path);
        $question = new Question("Path to wp-content directory, relative to project root [$default_wp_content]: ", $default_wp_content);
        $wp_content_path = $helper->ask($input, $output, $question);

        $default_active_theme = $this->Util->getWPActiveTheme();
        $question = new Question("Active theme [$default_active_theme]: ", $default_active_theme);
        $active_theme = $helper->ask($input, $output, $question);

        $this->makeDirectory("$this->project_dir/$path_unit_tests");
        $output->writeln('Generating files:');
        if ($advanced) {
            $this->makeDirectory("$this->project_dir/$path_integration_tests");
            list($spec_path, $spec_prefix) = explode(DIRECTORY_SEPARATOR, $path_unit_tests);
            $this->generateFile("$this->project_dir/phpspec.yml", $output, compact(
                'spec_path', 'spec_prefix', 'source_path', 'namespace', 'suite'
            ));
            $this->generateFile("$this->project_dir/$spec_path/phpspec.php", $output);
            $this->generateFile("$this->project_dir/$path_unit_tests/ExampleSpec.php", $output, compact(
                'spec_prefix', 'namespace', 'namespace_relative'
            ));
        }

        $this->generateFile("$this->project_dir/phpunit.xml", $output, compact(
            'phpunit_path', 'active_theme', 'phpunit_bootstrap_path', 'wp_core_path'
        ));
        $this->generateFile("$this->project_dir/$phpunit_bootstrap_path/phpunit.php", $output);

        $watcher = $advanced ? '.phpspec-watcher' : 'phpunit-watcher';
        $this->generateFile("$this->project_dir/$watcher.yml", $output, compact(
            'path_unit_tests', 'source_path'
        ));
        $this->generateFile("$this->project_dir/$phpunit_path/ExampleTest.php", $output, compact('namespace_relative'));
        $this->generateFile("$this->project_dir/wp-tests-config.php", $output, compact('wp_core_path', 'wp_content_path'));

        $output->writeln('Installing additional dependencies:');
        $packages = $advanced ? ['phpspec/phpspec', 'fetzi/phpspec-watcher', 'cyruscollier/phpspec-php-mock'] : ['spatie/phpunit-watcher'];
        if (!$this->Util->isWPCoreRequired()) {
            $packages[] = $this->Util::WP_CORE_PACKAGE;
        }
        $command = "composer config extra.wordpress-install-dir $wp_core_path";
        $output->write($command . "\n");
        shell_exec($command);
        $command = sprintf('composer require %s --dev', implode(' ', $packages));
        $output->write($command . "\n");
        shell_exec($command);
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
        $template = new Template($template_file);
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
