export class ContextMenu {
  constructor() {
    if (!document.getElementById('contextMenu')) {
      return;
    }
    const contextMenu = document.getElementById("contextMenu");
    const menuItems = document.getElementById("menuItems");
    document.addEventListener("contextmenu", (event) => {
      event.preventDefault();

      const blockdiv = event.target.closest('.block');
      let options = [];
      options.push(
        {
          'url': blockdiv.dataset.context_url,
          'text': blockdiv.dataset.context_text,
        }
      );
      console.log(blockdiv);
      console.log(blockdiv.querySelectorAll('.paragraph').length);
      blockdiv.querySelectorAll('.paragraph').forEach((paragraphdiv) => {
        options.push(
          {
            'url': paragraphdiv.dataset.context_url,
            'text': paragraphdiv.dataset.context_text,
          }
        )
      });

      console.log(options);

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