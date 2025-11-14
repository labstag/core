<?php

namespace Labstag\Enum;

enum PageEnum: string
{
    case CHANGEPASSWORD = 'changepassword';
    case CV             = 'cv';
    case HOME           = 'home';
    case LOGIN          = 'login';
    case LOSTPASSWORD   = 'lostpassword';
    case MOVIES         = 'movie';
    case PAGE           = 'page';
    case POSTS          = 'post';
    case SERIES         = 'series';
    case STORIES        = 'story';
}
