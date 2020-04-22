<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Verification controller
 */
class VerificationController extends AbstractController
{
    /**
     * @Route("/verification", name="verification")
     */
    public function index()
    {
        $finder = new Finder();
        $files = $finder->name('*.png')->in(__DIR__ . '/../../public/output');
        $icons = [];

        foreach ($files as $file) {
            $icons[] = basename($file);
        }

        return $this->render('verification/index.html.twig', [
            'icons' => $icons,
        ]);
    }
}
