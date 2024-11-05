import Sortable from 'sortablejs';

export class History {
  constructor()
  {
    this.actionSort()
  }
  actionSort() {
    document.querySelectorAll(".history-sort").forEach(
      (element) =>{
        var elementSortable = document.getElementById(element.id);
        if (elementSortable != undefined) {
          Sortable.create(
            elementSortable,
            {
              onChange: function (event) {
                document.querySelectorAll(".history-sort").forEach(
                  (sortList) => {
                  sortList.querySelectorAll("input").forEach(
                    (input, position) => {
                    input.value = position + 1;
                    }
                  );
                  }
                );
              }
            }
          );
        }
    });
  }
}
