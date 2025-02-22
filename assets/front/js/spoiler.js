export class Spoiler
{
  constructor()
  {
    const lazyBackgrounds = document.querySelectorAll(".spoiler");

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                let div = entry.target;
                let fullImage = div.getAttribute("data-bg");

                let img = new Image();
                img.src = fullImage;
                img.onload = () => {
                    div.style.backgroundImage = `url('${fullImage}')`;
                    div.classList.add("loaded");
                };

                observer.unobserve(div);
            }
        });
    }, {
        root: null,
        threshold: 0.1
    });

    lazyBackgrounds.forEach(div => observer.observe(div));
  }
}