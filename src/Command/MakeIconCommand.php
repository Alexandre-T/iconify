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
        $this->name = filter_var ( $input->getArgument('name'), FILTER_SANITIZE_STRING);
        $this->nature = $this->getNature($input->getOption('nature'));
        $filename = __DIR__ ."/../../public/output/{$this->name}.png";

        $io->note(sprintf('You passed an argument: %s', $this->name));
        $io->note(sprintf('You have choose the style: %s', $this->nature));
        $io->note(sprintf('Your file will be store here: %s', $filename));
        $io->note(sprintf('The font used will be: %s', $this->getFontFilename()));

        $image     = imagecreatetruecolor($this->width, $this->height);
        imagealphablending($image,false);

        $transparent =imagecolorallocatealpha($image,255,255,255,127);
        imagefilledrectangle($image, 0, 0, 32, 32, $transparent);
        imagealphablending($image,true);

        $blue = imagecolorallocate($image, 0, 0, 255);
        list($x, $y) = $this->imageCenter($image, $this->getSymbol(), $this->getFontFilename(), 16, 0);
        imagettftext ( $image , 16, 0, $x, $y, $blue, $this->getFontFilename(),  $this->getSymbol());

        header("Content-type: image/png");
        imagepng($image, $filename, 0);
        imagedestroy($image);

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

    /**
     * Get the font.
     */
    private function getFontFilename(): string
    {
        $directory = __DIR__ . '/../../public/font';
        switch ($this->nature) {
            case self::BRAND:
                return $directory . '/fa-brands-400.ttf';
            case self::REGULAR:
                return $directory . '/fa-regular-400.ttf';
            default:
                return $directory . '/fa-solid-900.ttf';
        }
    }

    private function getSymbol(): string
    {
        //FIXME To complete.
        return "&#xf187;";
    }

    /**
     * Return the center of image
     */
    private function imageCenter($image, string $text, string $font, int $size, int $angle = 0)
    {
        $xi = imagesx($image);
        $yi = imagesy($image);

        $box = imagettfbbox($size, $angle, $font, $text);

        $xr = abs(max($box[2], $box[4]));
        $yr = abs(max($box[5], $box[7]));

        $x = intval(($xi - $xr) / 2);
        $y = intval(($yi + $yr) / 2);

        return array($x, $y);
    }

}
