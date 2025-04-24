/**
 * Common functionality for ROME Tenant Dashboard tabs
 */

import { debounce, formatPrice, showLoading, showError } from './utils.js';

// Initialize all tabs with common functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize dropdown toggles
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const dropdownMenu = this.nextElementSibling;
            dropdownMenu.classList.toggle('show');
        });
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.matches('.dropdown-toggle')) {
            const dropdowns = document.querySelectorAll('.dropdown-menu');
            dropdowns.forEach(dropdown => {
                if (dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                }
            });
        }
    });

    // Initialize search functionality with debounce
    const searchInputs = document.querySelectorAll('input[type="text"][id$="Search"]');
    searchInputs.forEach(input => {
        input.addEventListener('input', debounce(function() {
            const searchTerm = this.value.toLowerCase();
            const containerId = this.id.replace('Search', 'Container');
            const items = document.querySelectorAll(`#${containerId} .property-item`);
            
            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        }, 300));
    });

    // Initialize sort functionality
    const sortLinks = document.querySelectorAll('.sort-properties');
    sortLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const sortType = this.dataset.sort;
            const containerId = this.closest('.dropdown-menu').previousElementSibling.id.replace('Dropdown', 'Container');
            sortProperties(containerId, sortType);
        });
    });
});

/**
 * Sort properties in a container
 * @param {string} containerId - ID of container with property items
 * @param {string} sortType - Sorting type (price-low, price-high, newest)
 */
function sortProperties(containerId, sortType) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const items = Array.from(container.querySelectorAll('.property-item'));
    
    items.sort((a, b) => {
        switch (sortType) {
            case 'price-low':
                return parseInt(a.dataset.price) - parseInt(b.dataset.price);
            case 'price-high':
                return parseInt(b.dataset.price) - parseInt(a.dataset.price);
            case 'newest':
                return parseInt(b.dataset.id) - parseInt(a.dataset.id);
            default:
                return 0;
        }
    });

    // Clear and re-append sorted items
    container.innerHTML = '';
    items.forEach(item => container.appendChild(item));
}