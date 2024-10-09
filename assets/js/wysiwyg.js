import { ClassicEditor as ClassicEditorBase } from '@ckeditor/ckeditor5-editor-classic';
import { Alignment } from '@ckeditor/ckeditor5-alignment';
import { Autoformat } from '@ckeditor/ckeditor5-autoformat';
import { Autosave } from '@ckeditor/ckeditor5-autosave';
import {
  Bold,
  Code,
  Italic,
  Strikethrough,
  Subscript,
  Superscript,
  Underline
} from '@ckeditor/ckeditor5-basic-styles';
import { BlockQuote } from '@ckeditor/ckeditor5-block-quote';
import { CodeBlock } from '@ckeditor/ckeditor5-code-block';
import { Essentials } from '@ckeditor/ckeditor5-essentials';
import { FindAndReplace } from '@ckeditor/ckeditor5-find-and-replace';
import {
  FontBackgroundColor,
  FontColor,
  FontFamily,
  FontSize
} from '@ckeditor/ckeditor5-font';
import { Heading } from '@ckeditor/ckeditor5-heading';
import { Highlight } from '@ckeditor/ckeditor5-highlight';
import { HorizontalLine } from '@ckeditor/ckeditor5-horizontal-line';
import { HtmlEmbed } from '@ckeditor/ckeditor5-html-embed';
import {
  DataFilter,
  DataSchema,
  GeneralHtmlSupport,
  HtmlComment
} from '@ckeditor/ckeditor5-html-support';
import {
  AutoImage,
  Image,
  ImageCaption,
  ImageInsert,
  ImageResize,
  ImageStyle,
  ImageToolbar,
  ImageUpload
} from '@ckeditor/ckeditor5-image';
import {
  Indent,
  IndentBlock
} from '@ckeditor/ckeditor5-indent';
import { TextPartLanguage } from '@ckeditor/ckeditor5-language';
import {
  AutoLink,
  Link,
  LinkImage
} from '@ckeditor/ckeditor5-link';
import {
  DocumentList,
  DocumentListProperties,
  TodoDocumentList
} from '@ckeditor/ckeditor5-list';
import {
  MediaEmbed,
  MediaEmbedToolbar
} from '@ckeditor/ckeditor5-media-embed';
import { Mention } from '@ckeditor/ckeditor5-mention';
import { PageBreak } from '@ckeditor/ckeditor5-page-break';
import { Paragraph } from '@ckeditor/ckeditor5-paragraph';
import { PasteFromOffice } from '@ckeditor/ckeditor5-paste-from-office';
import { RemoveFormat } from '@ckeditor/ckeditor5-remove-format';
import { StandardEditingMode } from '@ckeditor/ckeditor5-restricted-editing';
import { SelectAll } from '@ckeditor/ckeditor5-select-all';
import { ShowBlocks } from '@ckeditor/ckeditor5-show-blocks';
import { SourceEditing } from '@ckeditor/ckeditor5-source-editing';
import {
  SpecialCharacters,
  SpecialCharactersArrows,
  SpecialCharactersCurrency,
  SpecialCharactersEssentials,
  SpecialCharactersLatin,
  SpecialCharactersMathematical,
  SpecialCharactersText
} from '@ckeditor/ckeditor5-special-characters';
import { Style } from '@ckeditor/ckeditor5-style';
import {
  Table,
  TableCaption,
  TableCellProperties,
  TableColumnResize,
  TableProperties,
  TableToolbar
} from '@ckeditor/ckeditor5-table';
import { TextTransformation } from '@ckeditor/ckeditor5-typing';
import { Base64UploadAdapter } from '@ckeditor/ckeditor5-upload';
import { WordCount } from '@ckeditor/ckeditor5-word-count';
export default class Wysiwyg extends ClassicEditorBase { }

Wysiwyg.builtinPlugins = [
  Alignment,
  AutoImage,
  AutoLink,
  Autoformat,
  Autosave,
  Base64UploadAdapter,
  BlockQuote,
  Bold,
  Code,
  CodeBlock,
  DataFilter,
  DataSchema,
  DocumentList,
  DocumentListProperties,
  Essentials,
  FindAndReplace,
  FontBackgroundColor,
  FontColor,
  FontFamily,
  FontSize,
  GeneralHtmlSupport,
  Heading,
  Highlight,
  HorizontalLine,
  HtmlComment,
  HtmlEmbed,
  Image,
  ImageCaption,
  ImageInsert,
  ImageResize,
  ImageStyle,
  ImageToolbar,
  ImageUpload,
  Indent,
  IndentBlock,
  Italic,
  Link,
  LinkImage,
  MediaEmbed,
  MediaEmbedToolbar,
  Mention,
  PageBreak,
  Paragraph,
  PasteFromOffice,
  RemoveFormat,
  SelectAll,
  ShowBlocks,
  SourceEditing,
  SpecialCharacters,
  SpecialCharactersArrows,
  SpecialCharactersCurrency,
  SpecialCharactersEssentials,
  SpecialCharactersLatin,
  SpecialCharactersMathematical,
  SpecialCharactersText,
  StandardEditingMode,
  Strikethrough,
  Style,
  Subscript,
  Superscript,
  Table,
  TableCaption,
  TableCellProperties,
  TableColumnResize,
  TableProperties,
  TableToolbar,
  TextPartLanguage,
  TextTransformation,
  TodoDocumentList,
  Underline,
  WordCount
];
Wysiwyg.defaultConfig = {
  toolbar: {
      items: [
        'undo',
				'redo',
				'|',
				'code',
				'findAndReplace',
				'|',
				'pageBreak',
				'selectAll',
				'showBlocks',
				'sourceEditing',
				'restrictedEditingException',
				'-',
				'bold',
				'italic',
				'underline',
				'strikethrough',
				'removeFormat',
				'|',
				'fontColor',
				'fontBackgroundColor',
				'fontFamily',
				'fontSize',
				'highlight',
				'|',
				'subscript',
				'superscript',
				'specialCharacters',
				'|',
				'alignment',
				'|',
				'bulletedList',
				'numberedList',
				'|',
				'outdent',
				'indent',
				'-',
				'link',
				'blockQuote',
				'codeBlock',
				'|',
				'horizontalLine',
				// '|',
				// 'imageInsert',
				// 'imageUpload',
				'|',
				'insertTable',
				'mediaEmbed',
				'htmlEmbed',
				'|',
				'heading',
				'style',
				'textPartLanguage'
    ],
    shouldNotGroupWhenFull: true
  },
  language: 'fr',
  image: {
    toolbar: [
      'imageTextAlternative',
      'toggleImageCaption',
      'imageStyle:inline',
      'imageStyle:block',
      'imageStyle:side',
      'linkImage'
    ]
  },
  table: {
    contentToolbar: [
      'tableColumn',
      'tableRow',
      'mergeTableCells',
      'tableCellProperties',
      'tableProperties'
    ]
  }
};
