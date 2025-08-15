// Blog page JavaScript functionality

// Define API base URL
const API_BASE_URL = '';

let currentPage = 1;
let isLoading = false;
let hasMorePosts = true;
let allPosts = [];
let filteredPosts = [];

// Initialize blog page
document.addEventListener('DOMContentLoaded', function() {
    loadCategories();
    loadBlogPosts();
    setupEventListeners();
    
    // Track page view for analytics
    trackPageView();
});

function setupEventListeners() {
    // Search functionality
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(handleSearch, 300));
    }
    
    // Category filter
    const categoryFilter = document.getElementById('category-filter');
    if (categoryFilter) {
        categoryFilter.addEventListener('change', handleCategoryFilter);
    }
    
    // Sort filter
    const sortFilter = document.getElementById('sort-filter');
    if (sortFilter) {
        sortFilter.addEventListener('change', handleSortFilter);
    }
    
    // Load more button
    const loadMoreBtn = document.getElementById('load-more-btn');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', loadMorePosts);
    }
    
    // Newsletter form
    const newsletterForm = document.getElementById('newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', handleNewsletterSubmit);
    }
}

function loadCategories() {
    fetch(`/backend/api/categories.php`)
        .then(response => response.json())
        .then(data => {
            const categoryFilter = document.getElementById('category-filter');
            if (data.records && categoryFilter) {
                data.records.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.id;
                    option.textContent = category.name;
                    categoryFilter.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error loading categories:', error);
        });
}

function loadBlogPosts(reset = true) {
    if (isLoading) return;
    
    isLoading = true;
    showLoading(true);
    
    if (reset) {
        currentPage = 1;
        allPosts = [];
        filteredPosts = [];
    }
    
    fetch(`/backend/api/posts_mock.php?page=${currentPage}&limit=12`)
        .then(response => response.json())
        .then(data => {
            if (data.records) {
                if (reset) {
                    allPosts = data.records;
                } else {
                    allPosts = allPosts.concat(data.records);
                }
                
                filteredPosts = [...allPosts];
                renderBlogPosts(reset);
                
                // Check if there are more posts
                hasMorePosts = data.records.length === 12;
                updateLoadMoreButton();
                
                // Load featured article if it's the first page
                if (reset && data.records.length > 0) {
                    renderFeaturedArticle(data.records[0]);
                }
            } else {
                if (reset) {
                    showNoResults(true);
                }
            }
        })
        .catch(error => {
            console.error('Error loading blog posts:', error);
            showAlert('Gagal memuat artikel blog', 'error');
        })
        .finally(() => {
            isLoading = false;
            showLoading(false);
        });
}

function renderFeaturedArticle(post) {
    const featuredContainer = document.getElementById('featured-article');
    if (!featuredContainer || !post) return;
    
    const featuredHtml = `
        <div class="bg-white rounded-lg shadow-xl overflow-hidden">
            <div class="md:flex">
                <div class="md:w-1/2">
                    ${post.featured_image ? `
                        <img src="${post.featured_image}" alt="${post.title}" class="w-full h-64 md:h-full object-cover">
                    ` : `
                        <div class="w-full h-64 md:h-full bg-gradient-to-r from-blue-400 to-blue-600 flex items-center justify-center">
                            <i class="fas fa-newspaper text-white text-6xl"></i>
                        </div>
                    `}
                </div>
                <div class="md:w-1/2 p-8">
                    <div class="flex items-center mb-4">
                        <span class="bg-blue-100 text-blue-600 px-3 py-1 rounded-full text-sm font-semibold">Featured</span>
                        ${post.category_name ? `<span class="ml-2 text-gray-500 text-sm">${post.category_name}</span>` : ''}
                    </div>
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-800 mb-4">${post.title}</h2>
                    <p class="text-gray-600 mb-6">${post.excerpt || ''}</p>
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-500">
                            <i class="fas fa-calendar mr-1"></i>
                            ${formatDate(post.created_at)}
                        </div>
                            <a href="/${post.slug}" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                            Baca Selengkapnya
                        </a>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    featuredContainer.innerHTML = featuredHtml;
}

function renderBlogPosts(reset = true) {
    const blogContainer = document.getElementById('blog-posts');
    if (!blogContainer) return;
    
    if (reset) {
        blogContainer.innerHTML = '';
    }
    
    if (filteredPosts.length === 0) {
        showNoResults(true);
        return;
    }
    
    showNoResults(false);
    
    // Skip the first post if it's featured and we're on the first page
    const postsToRender = currentPage === 1 ? filteredPosts.slice(1) : filteredPosts;
    
    postsToRender.forEach(post => {
        const postHtml = `
            <article class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition duration-300">
                ${post.featured_image ? `
                    <img src="${post.featured_image}" alt="${post.title}" class="w-full h-48 object-cover">
                ` : `
                    <div class="w-full h-48 bg-gradient-to-r from-blue-400 to-blue-600 flex items-center justify-center">
                        <i class="fas fa-newspaper text-white text-3xl"></i>
                    </div>
                `}
                <div class="p-6">
                    ${post.category_name ? `
                        <div class="mb-3">
                            <span class="bg-blue-100 text-blue-600 px-2 py-1 rounded text-sm">${post.category_name}</span>
                        </div>
                    ` : ''}
                    <h3 class="text-xl font-semibold mb-3 line-clamp-2">
                        <a href="/${post.slug}" class="text-gray-800 hover:text-blue-600 transition duration-300">
                            ${post.title}
                        </a>
                    </h3>
                    <p class="text-gray-600 text-sm mb-4 line-clamp-3">${post.excerpt || ''}</p>
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <div>
                            <i class="fas fa-calendar mr-1"></i>
                            ${formatDate(post.created_at)}
                        </div>
                        <a href="/${post.slug}" class="text-blue-600 hover:text-blue-700 font-semibold">
                            Baca <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
            </article>
        `;
        
        blogContainer.insertAdjacentHTML('beforeend', postHtml);
    });
}

function handleSearch() {
    const searchTerm = document.getElementById('search-input').value.toLowerCase().trim();
    
    if (searchTerm === '') {
        filteredPosts = [...allPosts];
    } else {
        filteredPosts = allPosts.filter(post => 
            post.title.toLowerCase().includes(searchTerm) ||
            (post.excerpt && post.excerpt.toLowerCase().includes(searchTerm)) ||
            (post.content && post.content.toLowerCase().includes(searchTerm))
        );
    }
    
    renderBlogPosts(true);
    updateLoadMoreButton();
}

function handleCategoryFilter() {
    const categoryId = document.getElementById('category-filter').value;
    
    if (categoryId === '') {
        filteredPosts = [...allPosts];
    } else {
        filteredPosts = allPosts.filter(post => post.category_id == categoryId);
    }
    
    renderBlogPosts(true);
    updateLoadMoreButton();
}

function handleSortFilter() {
    const sortBy = document.getElementById('sort-filter').value;
    
    switch (sortBy) {
        case 'newest':
            filteredPosts.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
            break;
        case 'oldest':
            filteredPosts.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
            break;
        case 'popular':
            // For now, sort by ID (assuming higher ID = more recent = more popular)
            filteredPosts.sort((a, b) => b.id - a.id);
            break;
    }
    
    renderBlogPosts(true);
}

function loadMorePosts() {
    currentPage++;
    loadBlogPosts(false);
}

function updateLoadMoreButton() {
    const loadMoreBtn = document.getElementById('load-more-btn');
    if (loadMoreBtn) {
        if (hasMorePosts && filteredPosts.length === allPosts.length) {
            loadMoreBtn.classList.remove('hidden');
        } else {
            loadMoreBtn.classList.add('hidden');
        }
    }
}

function handleNewsletterSubmit(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const email = formData.get('email');
    
    // Here you would typically send the email to your backend
    // For now, just show a success message
    showAlert('Terima kasih! Anda telah berlangganan newsletter kami.', 'success');
    event.target.reset();
}

function showLoading(show) {
    const loadingIndicator = document.getElementById('loading-indicator');
    if (loadingIndicator) {
        if (show) {
            loadingIndicator.classList.remove('hidden');
        } else {
            loadingIndicator.classList.add('hidden');
        }
    }
}

function showNoResults(show) {
    const noResults = document.getElementById('no-results');
    if (noResults) {
        if (show) {
            noResults.classList.remove('hidden');
        } else {
            noResults.classList.add('hidden');
        }
    }
}

function trackPageView() {
    if (typeof trackAnalytics === 'function') {
        trackAnalytics({
            page_url: window.location.pathname,
            page_title: document.title
        });
    }
}

// Utility function for debouncing
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Update page SEO dynamically
function updatePageSEO(seoData) {
    if (seoData.meta_title) {
        document.title = seoData.meta_title;
        document.getElementById('page-title').content = seoData.meta_title;
        document.getElementById('og-title').content = seoData.og_title || seoData.meta_title;
    }
    
    if (seoData.meta_description) {
        document.getElementById('page-description').content = seoData.meta_description;
        document.getElementById('og-description').content = seoData.og_description || seoData.meta_description;
    }
    
    if (seoData.meta_keywords) {
        document.getElementById('page-keywords').content = seoData.meta_keywords;
    }
    
    if (seoData.canonical_url) {
        document.getElementById('canonical-url').href = seoData.canonical_url;
    }
    
    if (seoData.og_image) {
        document.getElementById('og-image').content = seoData.og_image;
    }
    
    if (seoData.schema_markup) {
        try {
            const schemaData = JSON.parse(seoData.schema_markup);
            document.getElementById('structured-data').textContent = JSON.stringify(schemaData);
        } catch (e) {
            console.error('Invalid schema markup:', e);
        }
    }
}

// Load SEO data for blog page
function loadPageSEO() {
    fetch(`/backend/api/seo?page_type=page&page_id=blog`)
        .then(response => response.json())
        .then(data => {
            if (data && !data.message) {
                updatePageSEO(data);
            }
        })
        .catch(error => {
            console.error('Error loading SEO data:', error);
        });
}

// Load SEO data when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadPageSEO();
});

// Utility function for formatting dates
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// Utility function for showing alerts
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm ${
        type === 'success' ? 'bg-green-500 text-white' :
        type === 'error' ? 'bg-red-500 text-white' :
        'bg-blue-500 text-white'
    }`;
    
    alertDiv.innerHTML = `
        <div class="flex items-center justify-between">
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentElement) {
            alertDiv.remove();
        }
    }, 5000);
}

