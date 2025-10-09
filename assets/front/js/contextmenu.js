export class ContextMenu {

  static ctrlPressed = false;
  constructor() {
    if (!document.getElementById('contextMenu')) {
      return;
    }
    const contextMenu = document.getElementById("contextMenu");
    const menuItems = document.getElementById("menuItems");
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Control') {
            this.ctrlPressed = true;
        }
    });
    
    document.addEventListener('keyup', (event) => {
        if (event.key === 'Control') {
            this.ctrlPressed = false;
        }
    });
    document.addEventListener("contextmenu", (event) => {
      if (!this.ctrlPressed) {
        return;
      }
      
      event.preventDefault();

      const blockdiv = event.target.closest('.block');
      let options = [];
      if (blockdiv.dataset === undefined || blockdiv.dataset.context_url === undefined) {
        console.error('No context menu data found for this block', blockdiv);
        return;
      }
      options.push(
        {
          'url': blockdiv.dataset.context_url,
          'text': blockdiv.dataset.context_text,
        }
      );
      
      blockdiv.querySelectorAll('.paragraph').forEach((paragraphdiv) => {
        if (paragraphdiv.dataset === undefined || paragraphdiv.dataset.context_url === undefined) {
          console.error('No context menu data found for this paragraph', paragraphdiv);
          return;
        }
        options.push(
          {
            'url': paragraphdiv.dataset.context_url,
            'text': paragraphdiv.dataset.context_text,
          }
        )
      });

      menuItems.innerHTML = options.map(option => `<li><a href="${option.url}" target="_blank">${option.text}</a></li>`).join("");
      contextMenu.style.display = "block";
      contextMenu.style.left = `${event.pageX}px`;
      contextMenu.style.top = `${event.pageY}px`;

      
    });
    // Cacher le menu lorsqu'on clique ailleurs
    document.addEventListener("click", function () {
        contextMenu.style.display = "none";
    });
  }
}