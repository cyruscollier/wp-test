<?php

namespace WPTest\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;


/**
 * Main command, initializes default config files in project root
 */
class InitCommand extends Command
{
    const PATH_WP_DEVELOP = 'cyruscollier/wordpress-develop';

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
        $project_dir = $this->getProjectDirectory();
        $output->writeln($project_dir);
        $composer_data = $project_dir ? json_decode(file_get_contents($project_dir . '/composer.json'), true) : [];
        $default_namespace = isset($composer_data['autoload']['psr-4']) ?
            str_replace('\\', '', key($composer_data['autoload']['psr-4'])) : '';
        $vendor_dir = isset($composer_data['config']['vendor-dir']) ?
            $composer_data['config']['vendor-dir'] : 'vendor';

        $helper = $this->getHelper('question');

        $question = new Question('Project namespace (PSR-4) [' . $default_namespace . ']: ', $default_namespace);
        $namespace = $helper->ask($input, $output, $question);
        $suite = $namespace ? strtolower($namespace) : 'main';

        $question = new Question('Path to unit tests, relative to project root [tests/unit]: ', 'tests/unit');
        $path_unit_tests = $helper->ask($input, $output, $question);
        $path_parts = explode(DIRECTORY_SEPARATOR, $path_unit_tests);
        $spec_path = $path_parts[0];
        $spec_prefix = isset($path_parts[1]) ? $path_parts[1] : '';

        $question = new Question('Path to integration tests, relative to project root [tests/integration]: ', 'tests/integration');
        $path_integration_tests = $helper->ask($input, $output, $question);
        $path_wp_develop = $vendor_dir . '/' . self::PATH_WP_DEVELOP;

        $template_dir = dirname(dirname(__DIR__)) . '/templates';

        $phpspec_config = new \Text_Template("$template_dir/phpspec.yml.tpl");
        $phpspec_config->setVar(compact('spec_path', 'spec_prefix', 'namespace', 'path_unit_tests', 'suite'));
        $phpspec_config->renderTo("$project_dir/phpspec.yml");

        $phpunit_config = new \Text_Template("$template_dir/phpunit.xml.tpl");
        $phpunit_config->setVar(compact('path_integration_tests', 'path_wp_develop'));
        $phpunit_config->renderTo("$project_dir/phpunit.xml");

        $wp_tests_config = new \Text_Template("$template_dir/wp-tests-config.php.tpl");
        $wp_tests_config->setVar(compact('path_wp_develop'));
        $wp_tests_config->renderTo("$project_dir/wp-test-config.php");

        return 0;
    }

    protected function getProjectDirectory()
    {
        if (is_file(getcwd() . '/composer.json')) {
            return getcwd();
        }
        $olddir = false;
        $dir = __DIR__;
        while ($dir != '/' && $dir != $olddir) {
            $olddir = $dir;
            $dir = dirname($dir);
            if (is_file($dir . '/composer.json')) {
                return $dir;
            }

        }
        return false;
    }
}