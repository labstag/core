import { Paragraphs } from './back/js/paragraph';
import { Wysiwyg } from './back/js/wysiwyg';
import { Block } from './back/js/block';
import { Story } from './back/js/story';
import { Modal } from './back/js/modal';
import { Search } from './back/js/search';
import { Permission } from './back/js/permission';
import { SortableField } from './back/js/sortablefield';
import './back/index.scss';

document.addEventListener('DOMContentLoaded', function () {
  new Paragraphs();
  new Wysiwyg();
  new Block();
  new Modal();
  new Story();
  new Search();
  new Permission();
  new SortableField();
});