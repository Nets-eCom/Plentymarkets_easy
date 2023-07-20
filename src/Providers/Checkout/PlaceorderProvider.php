<?php

namespace NetsEasyPay\Providers\Checkout;

use Plenty\Plugin\Templates\Twig;


class PlaceorderProvider
{
    public function call(Twig $twig):string
    {
        return $twig->render('NetsEasyPay::PlaceOrder');
    }

}