import Sortable from 'sortablejs';

export class Block {
  constructor()
  {
    this.actionSort()
  }
  actionSort() {
    document.querySelectorAll(".block-sort").forEach(
      (element) =>{
        var elementSortable = document.getElementById(element.id);
        if (elementSortable != undefined) {
          Sortable.create(
            elementSortable,
            {
              onChange: function () {
                document.querySelectorAll(".block-sort").forEach(
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
