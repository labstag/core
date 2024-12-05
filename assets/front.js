import WebFont from 'webfontloader';
import svg4everybody from 'svg4everybody';
import { Video } from './front/js/video';

WebFont.load({
  google: {
    families: ['Roboto:400,700']
  }
});


import './front/index.scss';
document.addEventListener('DOMContentLoaded', function () {
  svg4everybody();
  new Video();
});
