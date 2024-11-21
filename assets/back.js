import { Paragraphs } from './back/paragraph';
import { Wysiwyg } from './back/wysiwyg';
import { Block } from './back/block';
import { History } from './back/history';
import './back.scss';

document.addEventListener('DOMContentLoaded', function () {
  new Paragraphs();
  new Wysiwyg();
  new Block();
  new History();
});