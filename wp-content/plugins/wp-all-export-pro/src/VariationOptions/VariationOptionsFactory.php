<?php

namespace Wpae\VariationOptions;


use Wpae\Pro\VariationOptions\VariationOptions as ProVariationOptions;

class VariationOptionsFactory
{
    public function createVariationOptions($pmxeEdition)
    {
        switch ($pmxeEdition){
            case 'free':
                return new VariationOptions();
                break;
            case 'paid':
                return new ProVariationOptions();
                break;
            default:
                throw new \Exception('Unknown PMXE edition');
        }
    }
}