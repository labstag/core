import Sortable from 'sortablejs';

export class Story {
  constructor()
  {
    this.actionSort()
  }
  actionSort() {
    document.querySelectorAll(".story-sort").forEach(
      (element) =>{
        var elementSortable = document.getElementById(element.id);
        if (elementSortable != undefined) {
          Sortable.create(
            elementSortable,
            {
              onChange: function (event) {
                document.querySelectorAll(".story-sort").forEach(
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
