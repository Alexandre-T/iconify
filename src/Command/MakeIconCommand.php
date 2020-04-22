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
    private string $font = '';

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
        //TODO add an extension as an option

        $io->note(sprintf('You passed an argument: %s', $this->name));
        $io->note(sprintf('You have choose the style: %s', $this->nature));
        $io->note(sprintf('Your file will be store here: %s', $filename));
        $io->note(sprintf('The font used will be: %s', $this->getFontFilename()));

        header("Content-type: image/png");
        //FIXME call the good police
        $string = "\uf187";//FIXME call the name associated char
        $im     = imagecreatetruecolor($this->width, $this->height);
        $gray = imagecolorallocate($im, 100, 100, 100);
        $blue = imagecolorallocate($im, 0, 0, 255);
        imagefilledrectangle($im, 0, 0, 32, 32, $gray);
        list($x, $y) = $this->imageCenter($im, $this->getSymbol(), $this->getFontFilename(), 16, 0);
        imagettftext ( $im , 16, 0, $x, $y, $blue, $this->getFontFilename(),  $this->getSymbol());
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

    /**
     * Get offsets.
     *
     * @return array|float[]|int[]
     */
    private function getOffsets(): array
    {
        //Find the font height
        $fontHeight = imagefontheight($this->getFont()) * 2; //Why * 2 ????
        //Find the font width
        $fontWidth = imagefontwidth($this->getFont());

        return [
            ($this->height - $fontHeight) / 2,
            ($this->width - $fontWidth) / 2,
        ];
    }

    /**
     * Get the font.
     *
     * Load it if necessary.
     */
    private function getFont(): int
    {
        if (empty($this->font)) {
            $this->font = imageloadfont($this->getFontFilename());
        }

        if (false === $this->font) {
            throw new \InvalidArgumentException(sprintf('Font %s unavailable', $this->getFontFilename()));
        }

        return $this->font;
    }

    private function getSymbol(): string
    {
        //FIXME To complete.
        return "&#xf187;";
    }

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
