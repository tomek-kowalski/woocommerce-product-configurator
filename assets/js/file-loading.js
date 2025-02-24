document.addEventListener('DOMContentLoaded', () => {
    lazyLoadElements(document);
    console.log('loaded');
});

function lazyLoadElements(container) {
    if (!container) return;
    const lazyElements = container.querySelectorAll('img[src]');

    if ('IntersectionObserver' in window) {
        const lazyObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    const lazyElement = entry.target;
                    //console.log(entry.target);
                    const highResSrc = lazyElement.getAttribute('src');

                    if (highResSrc) {
                        lazyElement.src = highResSrc; 
                    }

                    lazyObserver.unobserve(lazyElement);
                }
            });
        });


        lazyElements.forEach((lazyElement) => {
            lazyObserver.observe(lazyElement);
        });
    } else {
        lazyElements.forEach((lazyElement) => {
            const highResSrc = lazyElement.getAttribute('src');
            if (highResSrc) {
                lazyElement.src = highResSrc;
            }
        });
    }
}
