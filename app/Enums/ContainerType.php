<?php

namespace App\Enums;

enum ContainerType: string
{
    case BOX = 'box';
    case CARTON = 'carton';
    case BOTTLE = 'bottle';
    case CAN = 'can';
    case JAR = 'jar';
    case PACK = 'pack';
    case PIECE = 'piece';
    case PALLET = 'pallet';
}