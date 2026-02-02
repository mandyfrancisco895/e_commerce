
// Featured Collection
let currentCategory = 'all';
let currentMinPrice = 0;
let currentMaxPrice = 200;
let allProducts = [];
let currentSort = 'default';

// Initialize
document.addEventListener('DOMContentLoaded', function() {
// Only select product cards that are NOT in the new-arrivals section
allProducts = Array.from(document.querySelectorAll('.product-card'))
    .filter(card => 
        !card.classList.contains('no-results') && 
        !card.closest('.new-arrivals') // Exclude cards inside new-arrivals section
    );

setupPriceSlider();
setupCategoryFilters();
setupSortDropdown();
updateResultsCount();
});

// Setup sort dropdown listener
function setupSortDropdown() {
const sortDropdown = document.getElementById('sortDropdown');
if (sortDropdown) {
    sortDropdown.addEventListener('change', function() {
        currentSort = this.value;
        filterAndArrangeProducts();
    });
}
}

// Setup category filter listeners
function setupCategoryFilters() {
document.querySelectorAll('.category-filter').forEach(filter => {
    filter.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Remove active class from all filters
        document.querySelectorAll('.category-filter').forEach(f => {
            f.classList.remove('active');
        });
        
        // Add active class to clicked filter
        this.classList.add('active');
        
        // Get category value
        const categoryValue = this.getAttribute('data-category') || 
                            this.querySelector('input')?.value || 
                            this.textContent.toLowerCase().replace(' ', '');
        currentCategory = categoryValue;
        
        // Update radio button if it exists
        const radioInput = this.querySelector('input[type="radio"]');
        if (radioInput) {
            radioInput.checked = true;
        }
        
        filterAndArrangeProducts();
    });
});

// Handle radio button changes
document.querySelectorAll('input[name="category"]').forEach(radio => {
    radio.addEventListener('change', function() {
        currentCategory = this.value;
        
        document.querySelectorAll('.category-filter').forEach(f => {
            f.classList.remove('active');
        });
        this.closest('.category-filter')?.classList.add('active');
        
        filterAndArrangeProducts();
    });
});
}

// Enhanced filter and arrange function
function filterAndArrangeProducts() {
let visibleProducts = [];

// Step 1: Filter products based on category and price (excluding new-arrivals)
allProducts.forEach(card => {
    const cardCategory = card.getAttribute('data-category');
    const cardPrice = parseInt(card.getAttribute('data-price'));
    
    const categoryMatch = currentCategory === 'all' || cardCategory === currentCategory;
    const priceMatch = cardPrice >= currentMinPrice && cardPrice <= currentMaxPrice;
    
    if (categoryMatch && priceMatch) {
        card.classList.remove('hidden');
        visibleProducts.push(card);
    } else {
        card.classList.add('hidden');
    }
});

// Step 2: Sort visible products
sortVisibleProducts(visibleProducts);

// Step 3: Rearrange in DOM for proper grid alignment
arrangeProductsInGrid(visibleProducts);

// Step 4: Update UI
updateResultsCount(visibleProducts.length);
handleNoResultsDisplay(visibleProducts.length);

// Step 5: Trigger grid reflow for proper alignment
triggerGridReflow();
}

// Sort visible products based on current sort option
function sortVisibleProducts(products) {
products.sort((a, b) => {
    switch (currentSort) {
        case 'price-low':
            return parseInt(a.getAttribute('data-price')) - parseInt(b.getAttribute('data-price'));
        case 'price-high':
            return parseInt(b.getAttribute('data-price')) - parseInt(a.getAttribute('data-price'));
        case 'name':
            return a.getAttribute('data-name').localeCompare(b.getAttribute('data-name'));
        case 'newest':
            // Assuming products with 'new' badge or recent data-date attribute
            const aIsNew = a.querySelector('.new-badge') || a.getAttribute('data-special') === 'limited';
            const bIsNew = b.querySelector('.new-badge') || b.getAttribute('data-special') === 'limited';
            if (aIsNew && !bIsNew) return -1;
            if (!aIsNew && bIsNew) return 1;
            return 0;
        default:
            // Default order (original DOM order)
            return 0;
    }
});
}

// Arrange products in grid for proper alignment
function arrangeProductsInGrid(visibleProducts) {
// Only target the main products grid, NOT the new-arrivals grid
const grid = document.querySelector('.featured-grid:not(.new-arrivals .products-grid)') || 
            document.querySelector('.products-grid:not(.new-arrivals .products-grid)') || 
            document.querySelector('.product-grid:not(.new-arrivals .products-grid)') ||
            // Fallback: target main product grid by excluding new-arrivals
            Array.from(document.querySelectorAll('.products-grid, .featured-grid, .product-grid'))
                .find(grid => !grid.closest('.new-arrivals'));

if (!grid) return;

const noResults = document.querySelector('.no-results');

// Remove all filterable products from grid first
allProducts.forEach(product => {
    if (product.parentNode === grid) {
        product.remove();
    }
});

// Add visible products back in correct order
visibleProducts.forEach(product => {
    if (noResults) {
        grid.insertBefore(product, noResults);
    } else {
        grid.appendChild(product);
    }
});

// Add hidden products at the end (but keep them hidden)
allProducts.forEach(product => {
    if (product.classList.contains('hidden') && !grid.contains(product)) {
        grid.appendChild(product);
    }
});
}

// Trigger CSS grid reflow for proper alignment
function triggerGridReflow() {
// Only trigger reflow on the main products grid, not new-arrivals
const grid = Array.from(document.querySelectorAll('.featured-grid, .products-grid, .product-grid'))
    .find(grid => !grid.closest('.new-arrivals'));

if (grid) {
    // Force reflow by temporarily changing display
    grid.style.display = 'none';
    grid.offsetHeight; // Trigger reflow
    grid.style.display = '';
    
    // Add smooth transition for better UX
    grid.style.transition = 'all 0.3s ease';
    
    // Remove transition after animation
    setTimeout(() => {
        grid.style.transition = '';
    }, 300);
}
}

// Handle no results display
function handleNoResultsDisplay(visibleCount) {
const noResults = document.querySelector('.no-results');
if (noResults) {
    if (visibleCount === 0) {
        noResults.style.display = 'block';
        noResults.style.gridColumn = '1 / -1'; // Span full width
        noResults.style.textAlign = 'center';
        noResults.style.padding = '3rem';
    } else {
        noResults.style.display = 'none';
    }
}
}

// Auto sort ascending (your existing function, enhanced)
function autoSortAscending() {
currentSort = 'price-low';
const sortDropdown = document.getElementById('sortDropdown');
if (sortDropdown) {
    sortDropdown.value = 'price-low';
}
filterAndArrangeProducts();
}

// Price slider setup (enhanced)
function setupPriceSlider() {
const slider = document.getElementById('priceSlider');
const minInput = document.getElementById('minPrice');
const maxInput = document.getElementById('maxPrice');

if (slider) {
    slider.addEventListener('input', function() {
        currentMaxPrice = parseInt(this.value);
        if (maxInput) maxInput.value = currentMaxPrice;
        updatePriceDisplay();
        filterAndArrangeProducts(); // Use enhanced function
    });
}

if (minInput) {
    minInput.addEventListener('input', function() {
        currentMinPrice = parseInt(this.value) || 0;
        updatePriceDisplay();
        filterAndArrangeProducts(); // Use enhanced function
    });
}

if (maxInput) {
    maxInput.addEventListener('input', function() {
        currentMaxPrice = parseInt(this.value) || 200;
        if (slider) slider.value = currentMaxPrice;
        updatePriceDisplay();
        filterAndArrangeProducts(); // Use enhanced function
    });
}

updatePriceDisplay();
}

function updatePriceDisplay() {
const display = document.getElementById('priceDisplay');
if (display) {
    display.textContent = `$${currentMinPrice} - $${currentMaxPrice}`;
}
}

function updateResultsCount(count = null) {
if (count === null) {
    count = allProducts.filter(card => !card.classList.contains('hidden')).length;
}
const resultsCount = document.getElementById('resultsCount');
if (resultsCount) {
    resultsCount.textContent = `Showing ${count} product${count !== 1 ? 's' : ''}`;
}
}

// Sort products (enhanced)
function sortProducts() {
const sortDropdown = document.getElementById('sortDropdown');
if (sortDropdown) {
    currentSort = sortDropdown.value;
    filterAndArrangeProducts();
}
}

// Clear all filters (enhanced)
function clearAllFilters() {
// Reset category
document.querySelectorAll('.category-filter').forEach(filter => {
    filter.classList.remove('active');
});
const firstFilter = document.querySelector('.category-filter');
if (firstFilter) firstFilter.classList.add('active');

const allCategoryRadio = document.querySelector('input[name="category"][value="all"]');
if (allCategoryRadio) allCategoryRadio.checked = true;
currentCategory = 'all';

// Reset price
currentMinPrice = 0;
currentMaxPrice = 200;
const minInput = document.getElementById('minPrice');
const maxInput = document.getElementById('maxPrice');
const priceSlider = document.getElementById('priceSlider');

if (minInput) minInput.value = 0;
if (maxInput) maxInput.value = 200;
if (priceSlider) priceSlider.value = 200;
updatePriceDisplay();

// Reset sort
currentSort = 'default';
const sortDropdown = document.getElementById('sortDropdown');
if (sortDropdown) sortDropdown.value = 'default';

filterAndArrangeProducts();
}

// Mobile filter toggle
function toggleFilters() {
const sidebar = document.getElementById('filtersSidebar');
const button = document.querySelector('.mobile-filter-toggle');

if (sidebar) {
    sidebar.classList.toggle('show');
    if (button) {
        button.textContent = sidebar.classList.contains('show') ? 'Hide Filters' : 'Show Filters';
    }
}
}

// Optional: Add smooth animations for better UX (excluding new-arrivals)
function addProductAnimations() {
const style = document.createElement('style');
style.textContent = `
    .product-card:not(.new-arrivals .product-card) {
        transition: all 0.3s ease;
    }
    .product-card.hidden {
        opacity: 0;
        transform: scale(0.8);
        pointer-events: none;
    }
    .featured-grid:not(.new-arrivals .products-grid), 
    .products-grid:not(.new-arrivals .products-grid), 
    .product-grid:not(.new-arrivals .products-grid) {
        transition: all 0.3s ease;
    }
`;
document.head.appendChild(style);
}

// Initialize animations
document.addEventListener('DOMContentLoaded', addProductAnimations);


const swiper = new Swiper(".hero-swiper", {
loop: true,
autoplay: {
delay: 4000,
disableOnInteraction: false,
},
effect: "fade", // smooth fade transition
speed: 800
});








    // Mobile menu toggle
    const mobileToggle = document.getElementById('mobile-toggle');
    const navMenu = document.getElementById('nav-menu');

    mobileToggle.addEventListener('click', () => {
        navMenu.classList.toggle('active');
    });

    // Smooth scrolling for navigation links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                // Close mobile menu if open
                navMenu.classList.remove('active');
            }
        });
    });

    // Navbar scroll effect
    window.addEventListener('scroll', () => {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 100) {
            navbar.style.background = 'rgba(255, 255, 255, 0.95)';
        } else {
            navbar.style.background = 'var(--primary-white)';
        }
    });

    

    // Hide scroll hints after user interacts
    const featuredGrid = document.querySelector('.featured-grid');
    const productsGrid = document.querySelector('.products-grid');
    const scrollHints = document.querySelectorAll('.scroll-hint');

    function hideScrollHint(grid, hintIndex) {
        grid.addEventListener('scroll', () => {
            if (scrollHints[hintIndex]) {
                scrollHints[hintIndex].style.opacity = '0';
                setTimeout(() => {
                    if (scrollHints[hintIndex]) {
                        scrollHints[hintIndex].style.display = 'none';
                    }
                }, 300);
            }
        }, { once: true });
    }

    if (window.innerWidth <= 768) {
        hideScrollHint(featuredGrid, 0);
        hideScrollHint(productsGrid, 1);
    }

    // Responsive behavior on window resize
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
            navMenu.classList.remove('active');
            scrollHints.forEach(hint => {
                hint.style.display = 'none';
            });
        } else {
            scrollHints.forEach(hint => {
                hint.style.display = 'block';
                hint.style.opacity = '1';
            });
        }
    });






// MODAL JS - FIXED VERSION

document.addEventListener("DOMContentLoaded", () => {
    // Add to Cart buttons
    document.querySelectorAll(".add-to-cart").forEach(btn => {
      btn.addEventListener("click", e => {
        e.preventDefault();
  
        // Get product name dynamically
        const productCard = btn.closest(".product-card");
        const productName = productCard.getAttribute("data-name");
  
        Swal.fire({
          title: 'Login Required',
          text: `You need to login first to add "${productName}" to your cart.`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Go to Login',
          cancelButtonText: 'Cancel',
          confirmButtonColor: '#007bff',
          cancelButtonColor: '#6c757d'
        }).then((result) => {
          if (result.isConfirmed) {
            // Fixed redirect URL - use relative path or window.location
            window.location.href = "?page=login"; // This will go through your routing
          }
        });
      });
    });
  
    // Cart button
    const cartBtn = document.querySelector(".cart-btn");
    if (cartBtn) {
      cartBtn.addEventListener("click", e => {
        e.preventDefault();
  
        Swal.fire({
          title: 'Login Required',
          text: 'You need to login first to view your cart.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Go to Login',
          cancelButtonText: 'Cancel',
          confirmButtonColor: '#007bff',
          cancelButtonColor: '#6c757d'
        }).then((result) => {
          if (result.isConfirmed) {
            // Fixed redirect URL
            window.location.href = "?page=login";
          }
        });
      });
    }
  }); // REMOVED THE EXTRA CLOSING BRACE





// OUR STORY  



// Auto-changing image slider
class ImageSlider {
    constructor() {
        this.slides = document.querySelectorAll('.slide');
        this.dots = document.querySelectorAll('.progress-dot');
        this.currentSlide = 0;
        this.slideInterval = null;
        this.init();
    }

    init() {
        this.startSlideShow();
        this.addEventListeners();
    }

    startSlideShow() {
        this.slideInterval = setInterval(() => {
            this.nextSlide();
        }, 4000); // Change every 4 seconds
    }

    stopSlideShow() {
        clearInterval(this.slideInterval);
    }

    nextSlide() {
        this.slides[this.currentSlide].classList.remove('active');
        this.dots[this.currentSlide].classList.remove('active');
        
        this.currentSlide = (this.currentSlide + 1) % this.slides.length;
        
        this.slides[this.currentSlide].classList.add('active');
        this.dots[this.currentSlide].classList.add('active');
    }

    goToSlide(index) {
        this.slides[this.currentSlide].classList.remove('active');
        this.dots[this.currentSlide].classList.remove('active');
        
        this.currentSlide = index;
        
        this.slides[this.currentSlide].classList.add('active');
        this.dots[this.currentSlide].classList.add('active');
    }

    addEventListeners() {
        // Click on dots to go to specific slide
        this.dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                this.stopSlideShow();
                this.goToSlide(index);
                // Restart slideshow after 2 seconds
                setTimeout(() => {
                    this.startSlideShow();
                }, 2000);
            });
        });

        // Pause on hover
        const slider = document.querySelector('.image-slider');
        slider.addEventListener('mouseenter', () => {
            this.stopSlideShow();
        });

        slider.addEventListener('mouseleave', () => {
            this.startSlideShow();
        });
    }
}

// Initialize slider when page loads
document.addEventListener('DOMContentLoaded', () => {
    new ImageSlider();
});




// new arrivals  JS -->


// Create modal dynamically
const modal = document.createElement('div');
modal.classList.add('image-modal');
modal.innerHTML = `
  <span class="close-btn">&times;</span>
  <img src="" alt="Expanded Image">
`;
document.body.appendChild(modal);

const modalImg = modal.querySelector('img');
const closeBtn = modal.querySelector('.close-btn');

// Open modal when image is clicked
document.querySelectorAll('.clickable-img').forEach(img => {
  img.addEventListener('click', () => {
    modal.style.display = 'flex';
    modalImg.src = img.dataset.large; // show large image
  });
});

// Close when X is clicked
closeBtn.addEventListener('click', () => {
  modal.style.display = 'none';
});

// Close when clicking outside image
modal.addEventListener('click', (e) => {
  if (e.target === modal) {
    modal.style.display = 'none';
  }
});




  // newsletter  JS 


document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('newsletterForm');
    const emailInput = document.getElementById('emailInput');
    const submitBtn = document.getElementById('submitBtn');
    const errorMessage = document.getElementById('errorMessage');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const email = emailInput.value.trim();
        
        // Clear previous error state
        emailInput.classList.remove('error');
        errorMessage.classList.remove('show');
        
        // Basic email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (!email) {
            showError('Please enter your email address');
            return;
        }
        
        if (!emailRegex.test(email)) {
            showError('Please enter a valid email address');
            return;
        }
        
        // Show loading state
        submitBtn.classList.add('loading');
        submitBtn.textContent = 'Subscribing..';
        submitBtn.disabled = true;
        
        // Simulate API call
        setTimeout(() => {
            submitBtn.classList.remove('loading');
            submitBtn.classList.add('success');
            submitBtn.textContent = 'Subscribed!';
            
            // Reset form after success
            setTimeout(() => {
                form.reset();
                submitBtn.classList.remove('success');
                submitBtn.textContent = 'Subscribe';
                submitBtn.disabled = false;
            }, 2000);
        }, 1500);
    });
    
    function showError(message) {
        emailInput.classList.add('error');
        errorMessage.textContent = message;
        errorMessage.classList.add('show');
    }
    
    // Clear error on input
    emailInput.addEventListener('input', function() {
        if (emailInput.classList.contains('error')) {
            emailInput.classList.remove('error');
            errorMessage.classList.remove('show');
        }
    });
});



  