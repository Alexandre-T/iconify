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

    private string $name = 'toto';
    private string $nature = self::DEFAULT_NATURE;
    private int $width = 32;
    private int $height = 32;

    /**
     * Configuration.
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Create an icon from its font awesome name.')
            ->addArgument('name', InputArgument::REQUIRED, 'Font awesome name')
            ->addOption('nature', null, InputOption::VALUE_OPTIONAL, 'Brand (b), regular (r) or solid (s)', self::DEFAULT_NATURE)
            //TODO add a size as an option
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
        //TODO add an extension as an option

        $io->note(sprintf('You passed an argument: %s', $name));
        $io->note(sprintf('You have choose the style: %s', $nature));
        $io->note(sprintf('Your file will be store here: %s', $filename));

        header("Content-type: image/png");
        //FIXME call the good police
        $string = 'P';//FIXME call the name associated char
        $im     = imagecreatetruecolor($this->width, $this->height);
        $white = imagecolorallocate($im, 255, 255, 255);
        list($x, $y) = $this->getOffsets();
        imagestring($im, 3, $x, $y, $string, $white);
        imagepng($im, $filename, 0);
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

    private function getOffsets(): array
    {
        //Find the font height
        $fontHeight = imagefontheight(3) * 2; //Why * 2 ????
        //Find the font width
        $fontWidth = imagefontwidth(3);

        return [
            ($this->height - $fontHeight) / 2,
            ($this->width - $fontWidth) / 2,
        ];
    }
}
