<?php

namespace App\Enums;

enum ContainerType : string
{
    case box = 'box';
    case barrel = 'barrel';
    case pallet = 'pallet';
    case carton = 'carton';
    case bag = 'bag';
    case bottle = 'bottle';
    case crate = 'crate';
}
