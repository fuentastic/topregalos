<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class StringManipulationController extends AbstractController
{
    /**
     * @Route("/string-manipulate", name="string_manipulate")
     */
    public function index(Request $request): Response
    {
        $inputString = $request->query->get('input');
        $targetString = $request->query->get('target');
        $result = null;

        if ($inputString !== null && $targetString !== null) {
            $result = $this->validateStringFromInput($inputString, $targetString) ? 'True' : 'False';
        }

        return $this->render('string_manipulation/index.html.twig', [
            'result' => $result,
        ]);
    }


    public function validateStringFromInput(string $input, string $target): void {
        $t = intval(trim($target));

        for ($i = 0; $i < $t; $i++) {
            $s = trim($input);
            $t = trim($target);

            $a = str_split($s);
            $b = str_split($t);

            $a = array_reverse($a);
            $b = array_reverse($b);

            $c = [];
            while (count($b) !== 0 && count($a) !== 0) {
                if ($a[0] === $b[0]) {
                    $c[] = array_shift($b);
                    array_shift($a);
                } elseif ($a[0] !== $b[0] && count($a) !== 1) {
                    array_shift($a);
                    array_shift($a);
                } elseif ($a[0] !== $b[0] && count($a) === 1) {
                    array_shift($a);
                }
            }

            if (count($b) == 0) {
                echo "True";
            } else {
                echo "False";
            }
        }
    }

}

