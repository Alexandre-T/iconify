<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class MakeIconCommand
 *
 * Create an icon from its Font-Awesome name
 */
class MakeIconCommand extends Command
{
    private const BRAND = 'b';
    private const REGULAR = 'r';
    private const SOLID = 's';
    private const DEFAULT_NATURE = self::SOLID;

    protected static $defaultName = 'app:make:icon';

    /**
     * Configuration.
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Create an icon from its font awesome name.')
            ->addArgument('name', InputArgument::REQUIRED, 'Font awesome name')
            ->addOption('nature', null, InputOption::VALUE_OPTIONAL, 'Brand (b), regular (r) or solid (s)', self::DEFAULT_NATURE)
        ;
    }

    /**
     * Execution.
     *
     * @param InputInterface  $input  the input console
     * @param OutputInterface $output the output console
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $name = filter_var ( $input->getArgument('name'), FILTER_SANITIZE_STRING);
        $nature = $this->getNature($input->getOption('nature'));
        $filename = __DIR__ ."/../../public/output/$name.png";
        $width = $height = 32; //TODO add a size as an option
        //TODO add an extension as an option

        $io->note(sprintf('You passed an argument: %s', $name));
        $io->note(sprintf('You have choose the style: %s', $nature));
        $io->note(sprintf('Your file will be store here: %s', $filename));

        header("Content-type: image/png");
        //FIXME call the police
        $string = 'toto';//FIXME call the name associated char
        $im     = imagecreate($width, $height);
        $orange = imagecolorallocate($im, 220, 210, 60);
        $px     = (imagesx($im) - 7.5 * strlen($string)) / 2;
        imagestring($im, 3, $px, 9, $string, $orange);
        imagepng($im, $filename);
        imagedestroy($im);

        $io->success('Well done!');

        return 0;
    }

    /**
     * Nature filter.
     *
     * @param string $option the option sent by user.
     */
    private function getNature(string $option): string
    {
        switch ($option) {
            case self::REGULAR:
            case self::SOLID:
            case self::BRAND:
                return $option;
            default:
                return self::DEFAULT_NATURE;
        }
    }
}
