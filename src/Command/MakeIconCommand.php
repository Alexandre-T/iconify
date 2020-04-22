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
    private int $finalSize = 72;
    private int $size = 720;

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

        $io->note(sprintf('You passed an argument: %s', $this->name));
        $io->note(sprintf('You have choose the style: %s', $this->nature));
        $io->note(sprintf('The font used will be: %s', $this->getFontFilename()));

        $this->createIdleIcon();
        $io->note(sprintf('The idle icon is created: %s', $this->getIdleFilename()));

//        $this->createHoverIcon();
//        $io->note(sprintf('The hover icon is created: %s', $this->getHoverFilename()));
//
//        $this->createInsensitiveIcon();
//        $io->note(sprintf('The hover icon is created: %s', $this->getInsentiveFilename()));

        $io->success('Well done! Your file were created in output directory');

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

    /**
     * Filename for idle icon.
     */
    private function getIdleFilename()
    {
        return __DIR__ ."/../../public/output/{$this->name}_idle.png";
    }

    /**
     * Create the icon with the idle status.
     */
    private function createIdleIcon()
    {
        //Step1: Image creation
        $image     = imagecreatetruecolor($this->size, $this->size);
        imageantialias($image, true);

        //Step2: transparency
        $transparent = imagecolorallocatealpha($image,255,255,255,127);
        imagefill($image, 0, 0, $transparent);

        //Step3: Shadow Circle
        $shadow = imagecolorallocatealpha($image, 0, 0, 0,90);
        imagefilledellipse($image, $this->size /2, $this->size /2, $this->size - 105, $this->size -105, $shadow);
        imagefilter($image, IMG_FILTER_GAUSSIAN_BLUR);

        //Step4: White Circle
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefilledellipse($image, $this->size /2, $this->size /2, $this->size - 100, $this->size -100, $white);

        //Step5: Gray Circle
        $gray = imagecolorallocate($image, 200, 200, 200);
        imagefilledellipse($image, $this->size /2, $this->size /2, $this->size - 160, $this->size -160, $gray);

        //Step6: Image resized
        $smallImage = imagecreatetruecolor($this->finalSize, $this->finalSize);
        $transparent = imagecolorallocatealpha($smallImage,255,255,255,127);
        imagefill($smallImage, 0, 0, $transparent);
        imageantialias($smallImage, true);
        imagesavealpha($smallImage, true);
        imagecopyresampled($smallImage, $image, 0, 0, 0, 0, $this->finalSize, $this->finalSize, $this->size, $this->size);

        //memory
        imagedestroy($image);
        unset($image, $white, $gray, $shadow, $black);

        //Step7: Icons color
//        $white = imagecolorallocate($smallImage, 255, 255, 255);
//        $nuancedGray = imagecolorallocate($smallImage, 220, 220, 220);
        $nuancedWhite = imagecolorallocate($smallImage, 230, 230, 230);
        list($x, $y) = $this->imageCenter($smallImage, $this->getSymbol(), $this->getFontFilename(), $this->finalSize / 3, 0);
        imagettftext ( $smallImage , $this->finalSize / 3, 0, $x, $y, $nuancedWhite, $this->getFontFilename(),  $this->getSymbol());
        imagepng($smallImage, $this->getIdleFilename(), 0);
        imagedestroy($smallImage);
    }

}
