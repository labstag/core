import Sortable from 'sortablejs';

export class SortableField {
  constructor() {
    this.initSort()
    this.whenItemAdd()
  }
  whenItemAdd() {
    document.addEventListener('ea.collection.item-added', (event) => {
      const container = event.target.closest('[data-controller="sortable"]');
      if (container) {
        // re-init sortable
        this.initSortableFor(container);
      }
    });
  }
  initSortableFor(container) {
    if (container.sortableInstance) {
      container.sortableInstance.destroy();
    }

    container.sortableInstance = Sortable.create(container, {
      animation: 150,
      draggable: '.field-collection-item',
      handle: '.accordion-header', // tu peux mettre '.accordion-item' si tu veux
      onEnd: () => this.updatePositions(container)
    });
  }
  updatePositions(container) {
    const items = Array.from(container.children).filter(child => child.classList.contains('field-collection-item'));

    items.forEach((item, index) => {
      item.classList.remove('field-collection-item-first');
      item.classList.remove('field-collection-item-last');
      if (index == 0) {
        item.classList.add('field-collection-item-first');
      } else if (index == items.length - 1) {
        item.classList.add('field-collection-item-last');
      }
      const posInput = item.querySelector('input[id$="_position"]');
      if (posInput) {
        posInput.value = index;
      }
    });

  }
  initSort() {
    document.querySelectorAll('[data-controller="sortable"]').forEach(container => {
      this.initSortableFor(container);
    });
  }
}