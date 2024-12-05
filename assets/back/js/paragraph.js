import Sortable from 'sortablejs';

export class Paragraphs {
  constructor()
  {
    this.actionDelete();
    this.actionModal();
    this.actionEdit();
    this.actionAdd();
    this.actionRefresh();
    this.actionSort()
  }
  actionSort()
  {
    document.querySelectorAll(".paragraphs-list").forEach(
      element => {
        // disable if element have class 'datagrid-empty'
        console.log(element.classList, element.classList.contains('datagrid-empty'));
        if (element.classList.contains('datagrid-empty')) {
          return;
        }

        var elementSortable = document.getElementById(element.id);
        if (elementSortable != undefined) {
          Sortable.create(elementSortable, {
            onChange: function (event) {
              document.querySelectorAll(".sort-list").forEach(element => {
                element.querySelectorAll(".sort_input").forEach((input, position) => {
                  input.value = position + 1;
                });
              });
            }
          });
        }
      }
    );
    document.querySelectorAll(".paragraphs-list tbody").forEach(
      element => {
        console.log(element.classList, element.classList.contains('body-empty'));
        // disable if element have class 'body-empty'
        if (element.classList.contains('body-empty')) {
          return;
        }

        var elementSortable = document.getElementById(element.id);
        if (elementSortable != undefined) {
          Sortable.create(elementSortable, {
            onEnd: async (event) => {
              var paragraphs = [];
              document.querySelectorAll(".paragraphs-list tbody tr").forEach(tr => {
                paragraphs.push(tr.dataset.id);
              });
              var dataParagraph = document.querySelector('.paragraphs-list').closest('.paragraph-data');
              const data = await fetch(dataParagraph.dataset.urlUpdate, {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({ paragraphs: paragraphs.join(',') })
              })
                .then(response => response.text());
              this.list(data);
            }
          });
        }
      }
    );
  }
  actionModal()
  {
    document.querySelectorAll('#paragraph-modal').forEach(
      element => {
        element.addEventListener(
          'hide.bs.modal',
          (event) => {
            document.querySelector('#paragraph-refresh').click();
            document.querySelector('#paragraph-iframe').src = document.querySelector('#paragraph-iframe').dataset.src;
          }
        );
      }
    );
  }
  actionEdit()
  {
    document.querySelectorAll('.paragraph-edit').forEach(element => {
      element.addEventListener(
        'click',
        (event) => {
          document.querySelector(element.dataset.target).src = element.href;
        }  
      );
    });
  }
  actionAdd()
  {
    document.querySelectorAll('.paragraph-btn').forEach(
      element => {
        element.addEventListener(
          'click',
          async (event) => {
            const dataParagraph = document.querySelector('.paragraphs-list').closest('.paragraph-data');
            const data = await fetch(dataParagraph.dataset.urlAdd, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
              },
              body: new URLSearchParams({ paragraph: document.querySelector('#paragraph-select').value })
            })
              .then(response => response.text());
            this.list(data);
          }
        );
      }
    );
  }
  actionDelete()
  {
    document.querySelectorAll('.paragraph-delete').forEach(
      element => {
        this.actionDeleteElement(element);
      }
    );
  }
  actionDeleteElement(element)
  {
    element.addEventListener(
      'click',
      async (event) => {
        event.preventDefault();
        var dataParagraph = document.querySelector('.paragraphs-list').closest('.paragraph-data');
        const data = await fetch(dataParagraph.dataset.urlDelete, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: new URLSearchParams({ paragraph: element.closest('tr').dataset.id })
        })
          .then(response => response.text());
        // Access to the class method list
        this.list(data);
      }
    );
  }
  actionRefresh()
  {
    document.querySelectorAll('#paragraph-refresh').forEach(
      element => {
        element.addEventListener(
          'click',
          async (event) => {
            event.preventDefault();
            var dataParagraph = document.querySelector('.paragraphs-list').closest('.paragraph-data');
            const data = await fetch(dataParagraph.dataset.urlList).then(response => response.text());
            this.list(data);
          }
        );
      }
    );
  }
  list(data)
  {
    document.querySelector('.paragraphs-list').innerHTML = data;
    this.actionDelete();
    this.actionEdit();
    this.actionSort();
  }
};