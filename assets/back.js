import { Paragraphs } from './back/js/paragraph';
import { Wysiwyg } from './back/js/wysiwyg';
import { Block } from './back/js/block';
import { History } from './back/js/history';
import './back/index.scss';

document.addEventListener('DOMContentLoaded', function () {
  new Paragraphs();
  new Wysiwyg();
  new Block();
  new History();
});