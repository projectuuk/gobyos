// Admin Posts Management JavaScript
class PostsManager {
    constructor() {
        this.apiBase = '../backend/api';
        this.currentPage = 1;
        this.postsPerPage = 10;
        this.posts = [];
        this.categories = [];
        this.editingPost = null;
        
        this.init();
    }
    
    async init() {
        await this.loadCategories();
        await this.loadPosts();
        this.setupEventListeners();
        this.updateStats();
    }
    
    setupEventListeners() {
        // Search functionality
        document.getElementById('searchPosts').addEventListener('input', (e) => {
            this.searchPosts(e.target.value);
        });
        
        // Filter functionality
        document.getElementById('filterStatus').addEventListener('change', (e) => {
            this.filterPosts();
        });
        
        document.getElementById('filterCategory').addEventListener('change', (e) => {
            this.filterPosts();
        });
        
        // Form submission
        document.getElementById('postForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.savePost();
        });
        
        // Auto-generate slug from title
        document.getElementById('postTitle').addEventListener('input', (e) => {
            if (!document.getElementById('postSlug').value) {
                document.getElementById('postSlug').value = this.generateSlug(e.target.value);
            }
        });
        
        // Select all functionality
        document.getElementById('selectAll').addEventListener('change', (e) => {
            const checkboxes = document.querySelectorAll('tbody input[type="checkbox"]');
            checkboxes.forEach(cb => cb.checked = e.target.checked);
        });
    }
    
    async loadCategories() {
        try {
            const response = await fetch(`${this.apiBase}/categories.php`);
            if (response.ok) {
                const data = await response.json();
                this.categories = data.records || [];
                this.populateCategorySelects();
            }
        } catch (error) {
            console.error('Error loading categories:', error);
            // Add default categories if API fails
            this.categories = [
                { id: 1, name: 'Tips Pengiriman' },
                { id: 2, name: 'Berita Logistik' },
                { id: 3, name: 'Tutorial' },
                { id: 4, name: 'Informasi Umum' }
            ];
            this.populateCategorySelects();
        }
    }
    
    populateCategorySelects() {
        const selects = ['postCategory', 'filterCategory'];
        
        selects.forEach(selectId => {
            const select = document.getElementById(selectId);
            if (select) {
                // Clear existing options (except first one for filter)
                if (selectId === 'filterCategory') {
                    select.innerHTML = '<option value="">Semua Kategori</option>';
                } else {
                    select.innerHTML = '<option value="">Pilih Kategori</option>';
                }
                
                this.categories.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.id;
                    option.textContent = category.name;
                    select.appendChild(option);
                });
            }
        });
    }
    
    async loadPosts() {
        try {
            // Try to load from real API first
            const response = await fetch(`${this.apiBase}/posts.php`);
            if (response.ok) {
                const data = await response.json();
                this.posts = data.records || [];
                this.renderPosts();
                this.updateStats();
                return;
            }
        } catch (error) {
            console.error('Error loading posts from API:', error);
        }
        
        // Fallback to sample data if API fails
        this.posts = this.getSamplePosts();
        this.renderPosts();
        this.updateStats();
    }
    
    getSamplePosts() {
        return [
            {
                id: 1,
                title: 'Tips Mengemas Barang Pecah Belah untuk Pengiriman Aman',
                slug: 'tips-mengemas-barang-pecah-belah',
                excerpt: 'Panduan lengkap cara mengemas barang pecah belah agar aman selama proses pengiriman.',
                category_name: 'Tips Pengiriman',
                status: 'published',
                created_at: '2024-01-15 10:30:00',
                views: 1250
            },
            {
                id: 2,
                title: 'Perkembangan Industri Logistik di Indonesia 2024',
                slug: 'perkembangan-industri-logistik-indonesia-2024',
                excerpt: 'Analisis mendalam tentang tren dan perkembangan industri logistik Indonesia.',
                category_name: 'Berita Logistik',
                status: 'published',
                created_at: '2024-01-14 14:20:00',
                views: 890
            },
            {
                id: 3,
                title: 'Cara Melacak Paket dengan Mudah',
                slug: 'cara-melacak-paket-dengan-mudah',
                excerpt: 'Tutorial step-by-step untuk melacak status pengiriman paket Anda.',
                category_name: 'Tutorial',
                status: 'draft',
                created_at: '2024-01-13 09:15:00',
                views: 0
            }
        ];
    }
    
    renderPosts() {
        const tbody = document.getElementById('postsTableBody');
        if (!tbody) return;
        
        if (this.posts.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                        Belum ada artikel. <button onclick="openPostModal()" class="text-blue-600 hover:text-blue-800">Buat artikel pertama</button>
                    </td>
                </tr>
            `;
            return;
        }
        
        const startIndex = (this.currentPage - 1) * this.postsPerPage;
        const endIndex = startIndex + this.postsPerPage;
        const paginatedPosts = this.posts.slice(startIndex, endIndex);
        
        tbody.innerHTML = paginatedPosts.map(post => `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                    <input type="checkbox" class="rounded" value="${post.id}">
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900">${post.title}</div>
                    <div class="text-sm text-gray-500">${post.excerpt || 'Tidak ada ringkasan'}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        ${post.category_name || 'Tidak ada kategori'}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${this.getStatusClass(post.status)}">
                        ${this.getStatusText(post.status)}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${post.views || 0}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${this.formatDate(post.created_at)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button onclick="postsManager.editPost(${post.id})" class="text-indigo-600 hover:text-indigo-900 mr-3">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="postsManager.viewPost('${post.slug}')" class="text-green-600 hover:text-green-900 mr-3">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button onclick="postsManager.deletePost(${post.id})" class="text-red-600 hover:text-red-900">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
        
        this.updatePagination();
    }
    
    getStatusClass(status) {
        switch (status) {
            case 'published': return 'bg-green-100 text-green-800';
            case 'draft': return 'bg-yellow-100 text-yellow-800';
            case 'pending': return 'bg-blue-100 text-blue-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    }
    
    getStatusText(status) {
        switch (status) {
            case 'published': return 'Published';
            case 'draft': return 'Draft';
            case 'pending': return 'Pending';
            default: return 'Unknown';
        }
    }
    
    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }
    
    updateStats() {
        const totalPosts = this.posts.length;
        const publishedPosts = this.posts.filter(p => p.status === 'published').length;
        const draftPosts = this.posts.filter(p => p.status === 'draft').length;
        const totalViews = this.posts.reduce((sum, p) => sum + (p.views || 0), 0);
        
        document.getElementById('totalPosts').textContent = totalPosts;
        document.getElementById('publishedPosts').textContent = publishedPosts;
        document.getElementById('draftPosts').textContent = draftPosts;
        document.getElementById('totalViews').textContent = totalViews.toLocaleString();
    }
    
    updatePagination() {
        const totalPages = Math.ceil(this.posts.length / this.postsPerPage);
        const pagination = document.getElementById('pagination');
        
        if (totalPages <= 1) {
            pagination.innerHTML = '';
            return;
        }
        
        let paginationHTML = '';
        
        // Previous button
        if (this.currentPage > 1) {
            paginationHTML += `<button onclick="postsManager.changePage(${this.currentPage - 1})" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50">Previous</button>`;
        }
        
        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            if (i === this.currentPage) {
                paginationHTML += `<button class="px-3 py-2 text-sm font-medium text-white bg-blue-600 border border-blue-600">${i}</button>`;
            } else {
                paginationHTML += `<button onclick="postsManager.changePage(${i})" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 hover:bg-gray-50">${i}</button>`;
            }
        }
        
        // Next button
        if (this.currentPage < totalPages) {
            paginationHTML += `<button onclick="postsManager.changePage(${this.currentPage + 1})" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50">Next</button>`;
        }
        
        pagination.innerHTML = paginationHTML;
        
        // Update showing info
        const startIndex = (this.currentPage - 1) * this.postsPerPage + 1;
        const endIndex = Math.min(this.currentPage * this.postsPerPage, this.posts.length);
        
        document.getElementById('showingFrom').textContent = startIndex;
        document.getElementById('showingTo').textContent = endIndex;
        document.getElementById('totalEntries').textContent = this.posts.length;
    }
    
    changePage(page) {
        this.currentPage = page;
        this.renderPosts();
    }
    
    searchPosts(query) {
        if (!query.trim()) {
            this.loadPosts();
            return;
        }
        
        const filteredPosts = this.posts.filter(post => 
            post.title.toLowerCase().includes(query.toLowerCase()) ||
            (post.excerpt && post.excerpt.toLowerCase().includes(query.toLowerCase()))
        );
        
        this.posts = filteredPosts;
        this.currentPage = 1;
        this.renderPosts();
    }
    
    filterPosts() {
        const statusFilter = document.getElementById('filterStatus').value;
        const categoryFilter = document.getElementById('filterCategory').value;
        
        this.loadPosts().then(() => {
            let filteredPosts = [...this.posts];
            
            if (statusFilter) {
                filteredPosts = filteredPosts.filter(post => post.status === statusFilter);
            }
            
            if (categoryFilter) {
                filteredPosts = filteredPosts.filter(post => post.category_id == categoryFilter);
            }
            
            this.posts = filteredPosts;
            this.currentPage = 1;
            this.renderPosts();
        });
    }
    
    generateSlug(title) {
        return title
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim('-');
    }
    
    async savePost() {
        const formData = new FormData(document.getElementById('postForm'));
        const postData = {};
        
        // Get form data
        for (let [key, value] of formData.entries()) {
            postData[key] = value;
        }
        
        // Get content from editor
        postData.content = document.getElementById('postContent').innerHTML;
        
        // Auto-generate slug if empty
        if (!postData.slug) {
            postData.slug = this.generateSlug(postData.title);
        }
        
        // Auto-generate meta title if empty
        if (!postData.meta_title) {
            postData.meta_title = postData.title;
        }
        
        try {
            const url = this.editingPost ? `${this.apiBase}/posts.php` : `${this.apiBase}/posts.php`;
            const method = this.editingPost ? 'PUT' : 'POST';
            
            if (this.editingPost) {
                postData.id = this.editingPost.id;
            }
            
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(postData)
            });
            
            if (response.ok) {
                this.showNotification('Artikel berhasil disimpan!', 'success');
                this.closePostModal();
                await this.loadPosts();
            } else {
                const error = await response.json();
                this.showNotification(error.message || 'Gagal menyimpan artikel', 'error');
            }
        } catch (error) {
            console.error('Error saving post:', error);
            this.showNotification('Terjadi kesalahan saat menyimpan artikel', 'error');
        }
    }
    
    editPost(postId) {
        const post = this.posts.find(p => p.id === postId);
        if (!post) return;
        
        this.editingPost = post;
        
        // Fill form with post data
        document.getElementById('postId').value = post.id;
        document.getElementById('postTitle').value = post.title;
        document.getElementById('postSlug').value = post.slug;
        document.getElementById('postExcerpt').value = post.excerpt || '';
        document.getElementById('postFeaturedImage').value = post.featured_image || '';
        document.getElementById('postCategory').value = post.category_id || '';
        document.getElementById('postStatus').value = post.status;
        document.getElementById('postContent').innerHTML = post.content || '<p>Mulai menulis artikel Anda di sini...</p>';
        document.getElementById('metaTitle').value = post.meta_title || '';
        document.getElementById('metaDescription').value = post.meta_description || '';
        
        document.getElementById('modalTitle').textContent = 'Edit Artikel';
        this.openPostModal();
    }
    
    async deletePost(postId) {
        if (!confirm('Apakah Anda yakin ingin menghapus artikel ini?')) {
            return;
        }
        
        try {
            const response = await fetch(`${this.apiBase}/posts.php`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: postId })
            });
            
            if (response.ok) {
                this.showNotification('Artikel berhasil dihapus!', 'success');
                await this.loadPosts();
            } else {
                const error = await response.json();
                this.showNotification(error.message || 'Gagal menghapus artikel', 'error');
            }
        } catch (error) {
            console.error('Error deleting post:', error);
            this.showNotification('Terjadi kesalahan saat menghapus artikel', 'error');
        }
    }
    
    viewPost(slug) {
        window.open(`../${slug}`, '_blank');
    }
    
    openPostModal() {
        document.getElementById('postModal').classList.add('show');
    }
    
    closePostModal() {
        document.getElementById('postModal').classList.remove('show');
        document.getElementById('postForm').reset();
        document.getElementById('postContent').innerHTML = '<p>Mulai menulis artikel Anda di sini...</p>';
        document.getElementById('modalTitle').textContent = 'Artikel Baru';
        this.editingPost = null;
    }
    
    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg ${
            type === 'success' ? 'bg-green-500 text-white' :
            type === 'error' ? 'bg-red-500 text-white' :
            'bg-blue-500 text-white'
        }`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
}

