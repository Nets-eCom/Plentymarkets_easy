<?php

namespace NetsEasyPay\Providers\Icon;

use Plenty\Plugin\Templates\Twig;


class IconProvider
{
    public function call(Twig $twig):string
    {
        return $twig->render('NetsEasyPay::Icon');
    }

}