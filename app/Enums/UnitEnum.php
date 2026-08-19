<?php

namespace App\Enums;

enum UnitEnum: string
{
    case BOX = 'box';
    case CARTON = 'carton';
    case BOTTLE = 'bottle';
    case CAN = 'can';
    case JAR = 'jar';
    case PACK = 'pack';
    case PIECE = 'piece';
    case PALLET = 'pallet';
    case KG = 'kg';
    case G = 'g';
    case L = 'l';
    case M = 'm';
    case TON = 'ton';
}