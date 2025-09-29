<?php

namespace Labstag\Enum;

enum PageEnum: string{
    case HOME      = 'home';
    case POSTS     = 'post';
    case MOVIE     = 'movie';
    case STORIES   = 'story';
    case PAGE      = 'page';
}