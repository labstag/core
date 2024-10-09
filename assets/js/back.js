import '../scss/back.scss'
import Sortable from 'sortablejs';
import Wysiwyg from './wysiwyg';

function sortableElementSort() {
  document.querySelectorAll(".sort-list").forEach(element => {
    var elementSortable = document.getElementById(element.id);
    if (elementSortable != undefined) {
      Sortable.create(elementSortable, {
        onChange: function (event) {
          changePositionSortList(event);
        }
      });
    }
  });

  document.querySelectorAll("#paragraphs_list tbody").forEach(element => {
    var elementSortable = document.getElementById(element.id);
    if (elementSortable != undefined) {
      Sortable.create(elementSortable, {
        onEnd: function (event) {
          var paragraphs = [];
          document.querySelectorAll("#paragraphs_list tbody tr").forEach(tr => {
            paragraphs.push(tr.dataset.id);
          });
          var dataParagraph = document.querySelector('#paragraphs_list').closest('.paragraph_data');
          fetch(dataParagraph.dataset.urlUpdate, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({ paragraphs: paragraphs.join(',') })
          })
          .then(response => response.text())
          .then(returnParargaphsList);
        }
      });
    }
  });
}

function changePositionSortList(event) {
  document.querySelectorAll(".sort-list").forEach(element => {
    element.querySelectorAll(".sort_input").forEach((input, position) => {
      input.value = position + 1;
    });
  });
}

function returnParargaphsList(data) {
  document.querySelector('#paragraphs_list').innerHTML = data;
  paragraphsAction();
}

function paragraphsAction() {
  document.querySelectorAll('.paragraph-edit').forEach(element => {
    element.addEventListener('click', function (e) {

      document.querySelector(this.dataset.target).src = this.href;
    });
  });
}

function addWysiwygToElement(element) {
  element.classList.remove('wysiwyg');
  Wysiwyg.create(element, {
    language: 'fr'
  }).then(editor => {
    console.log('Editor was initialized', editor);
  }).catch(err => {
    console.error(err);
  });
}

function testWysiwyg() {
  document.querySelectorAll('.wysiwyg').forEach(element => {
    addWysiwygToElement(element);
  });
}

function BtnDeleteParagraph() {
  document.querySelectorAll('.paragraph-delete').forEach(element => {
    element.addEventListener('click', function (e) {
      e.preventDefault();
      var dataParagraph = document.querySelector('#paragraphs_list').closest('.paragraph_data');
      fetch(dataParagraph.dataset.urlDelete, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({ paragraph: this.closest('tr').dataset.id })
      })
      .then(response => response.text())
      .then(returnParargaphsList);
    });
  });
}

document.addEventListener('DOMContentLoaded', function () {
  document.querySelector('#paragraph_btn').addEventListener('click', function () {
    var dataParagraph = document.querySelector('#paragraphs_list').closest('.paragraph_data');
    fetch(dataParagraph.dataset.urlAdd, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams({ paragraph: document.querySelector('#paragraph_select').value })
    })
    .then(response => response.text())
    .then(returnParargaphsList);
  });

  BtnDeleteParagraph();

  document.querySelector('#paragraph_refresh').addEventListener('click', function (e) {
    e.preventDefault();
    var dataParagraph = document.querySelector('#paragraphs_list').closest('.paragraph_data');
    fetch(dataParagraph.dataset.urlList)
    .then(response => response.text())
    .then(returnParargaphsList);
  });

  paragraphsAction();

  document.querySelector('#paragraph_modal').addEventListener('hide.bs.modal', function (e) {
    document.querySelector('#paragraph_refresh').click();
    document.querySelector('#paragraph_iframe').src = document.querySelector('#paragraph_iframe').dataset.src;
  });

  document.querySelector('#modal-block-add').addEventListener('click', function (e) {
    e.preventDefault();
    document.querySelector('#modal-block form').submit();
  });

  changePositionSortList();
});

var MutationObserver = window.MutationObserver || window.WebKitMutationObserver || window.MozMutationObserver;
var observer = new MutationObserver(function (records, observer) {
  for (const record of records) {
    for (const addedNode of record.addedNodes) {
      testWysiwyg();
      sortableElementSort();
      BtnDeleteParagraph();
    }
  }
});

observer.observe(document.body, {
  childList: true,
  subtree: true,
});
