import { Paragraphs } from './back/paragraph';
import { Wysiwyg } from './back/wysiwyg';
import { Block } from './back/block';

document.addEventListener('DOMContentLoaded', function () {
  new Paragraphs();
  new Wysiwyg();
  new Block();
});