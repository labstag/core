import { Paragraphs } from './back/paragraph';
import { Wysiwyg } from './back/wysiwyg';

document.addEventListener('DOMContentLoaded', function () {
  new Paragraphs();
  new Wysiwyg();
});