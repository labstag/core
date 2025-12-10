export class Links {
  constructor() {
    const linksBlocks = document.querySelectorAll('.block_links');

    linksBlocks.forEach((block) => {
      this.initBlock(block);
    });
  }

  initBlock(block) {
    const menuItems = block.querySelectorAll('ul > li:has(> ul)');

    menuItems.forEach((menuItem) => {
      this.initMenuItem(menuItem, menuItems);
    });

    // Fermer les sous-menus au clic extérieur
    document.addEventListener('click', (e) => {
      if (!block.contains(e.target)) {
        menuItems.forEach((item) => item.classList.remove('is-open'));
      }
    });
  }

  initMenuItem(menuItem, menuItems) {
    const link = menuItem.querySelector(':scope > a');
    const submenu = menuItem.querySelector(':scope > ul');

    if (!link || !submenu) return;

    // Toggle au clic sur mobile
    link.addEventListener('click', (e) => {
      // Si c'est un vrai lien avec href différent de #, laisser le comportement normal sur desktop
      if (window.innerWidth >= 768 && link.getAttribute('href') !== '#') {
        return;
      }

      e.preventDefault();
      const isOpen = menuItem.classList.contains('is-open');

      // Fermer tous les autres sous-menus
      menuItems.forEach((item) => item.classList.remove('is-open'));

      // Toggle le sous-menu actuel
      if (!isOpen) {
        menuItem.classList.add('is-open');
      }
    });

    // Navigation au clavier
    link.addEventListener('keydown', (e) => {
      this.handleMainLinkKeydown(e, menuItem, submenu, link);
    });

    // Navigation dans le sous-menu
    this.initSubmenu(submenu, menuItem, link);
  }

  handleMainLinkKeydown(e, menuItem, submenu, link) {
    switch (e.key) {
      case 'ArrowDown':
        e.preventDefault();
        menuItem.classList.add('is-open');
        submenu.querySelector('a')?.focus();
        break;

      case 'Escape':
        e.preventDefault();
        menuItem.classList.remove('is-open');
        link.focus();
        break;
    }
  }

  initSubmenu(submenu, menuItem, mainLink) {
    const submenuLinks = submenu.querySelectorAll('a');
    
    submenuLinks.forEach((sublink, index) => {
      sublink.addEventListener('keydown', (e) => {
        this.handleSubmenuKeydown(e, submenuLinks, index, menuItem, mainLink);
      });
    });
  }

  handleSubmenuKeydown(e, submenuLinks, index, menuItem, mainLink) {
    switch (e.key) {
      case 'ArrowDown':
        e.preventDefault();
        submenuLinks[index + 1]?.focus();
        break;

      case 'ArrowUp':
        e.preventDefault();
        if (index === 0) {
          mainLink.focus();
        } else {
          submenuLinks[index - 1]?.focus();
        }
        break;

      case 'Escape':
        e.preventDefault();
        menuItem.classList.remove('is-open');
        mainLink.focus();
        break;
    }
  }
}

