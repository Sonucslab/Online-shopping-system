// js/main.js

document.addEventListener('DOMContentLoaded', () => {
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                e.preventDefault();
                targetElement.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });

    // Simple interaction for category cards
    const categoryCards = document.querySelectorAll('.grid-cols-4 .glass-card');
    categoryCards.forEach(card => {
        card.addEventListener('click', () => {
            alert('Category filtering will be implemented with PHP backend!');
        });
    });
});
