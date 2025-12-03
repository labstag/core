import WebFont from 'webfontloader';
import svg4everybody from 'svg4everybody';
import { Video } from './front/js/video';
import { Slider } from './front/js/slider';
import { Movie } from './front/js/movie';
import { ContextMenu } from './front/js/contextmenu';
import { Spoiler } from './front/js/spoiler';
import { Form } from './front/js/form';


WebFont.load({
  google: {
    families: ['Roboto:400,700']
  }
});


import './front/index.scss';
document.addEventListener('DOMContentLoaded', function () {
  svg4everybody();
  new Video();
  new ContextMenu();
  new Form();
  new Spoiler();
  new Slider(
    '.swiper-movie',
    '.swiper-movie-button-next',
    '.swiper-movie-button-prev',
    3
  );
  new Slider(
    '.swiper-game-video',
    '.swiper-game-video-button-next',
    '.swiper-game-video-button-prev',
    1
  );
  new Slider(
    '.swiper-game-artworks',
    '.swiper-game-artworks-button-next',
    '.swiper-game-artworks-button-prev',
    3
  );
  new Movie();
});
