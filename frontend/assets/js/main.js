// Main JavaScript file for Fio Trans Cargo website

// API Base URL - adjust this based on your backend location
const API_BASE_URL = '../backend';

// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }
    
    // Load blog posts on page load
    loadBlogPosts();
    
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
                if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
                    mobileMenu.classList.add('hidden');
                }
            }
        });
    });
    
    // Booking form submission
    const bookingForm = document.getElementById('booking-form');
    if (bookingForm) {
        bookingForm.addEventListener('submit', handleBookingSubmit);
    }
});

// Track package function
function trackPackage() {
    const trackingInput = document.getElementById('tracking-input');
    const trackingNumber = trackingInput.value.trim();
    
    if (!trackingNumber) {
        showAlert('Silakan masukkan nomor resi', 'error');
        return;
    }
    
    // Show loading state
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mencari...';
    button.disabled = true;
    
    // Make API call to track package
    fetch(`${API_BASE_URL}/api/bookings?tracking_number=${encodeURIComponent(trackingNumber)}`)
        .then(response => response.json())
        .then(data => {
            if (data.id) {
                showTrackingResult(data);
            } else {
                showAlert('Nomor resi tidak ditemukan', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Terjadi kesalahan saat melacak paket', 'error');
        })
        .finally(() => {
            // Restore button state
            button.innerHTML = originalText;
            button.disabled = false;
        });
}

// Show tracking result
function showTrackingResult(data) {
    const modal = createModal('Hasil Pelacakan', `
        <div class="space-y-4">
            <div class="bg-blue-50 p-4 rounded-lg">
                <h4 class="font-semibold text-blue-800">Nomor Resi: ${data.tracking_number}</h4>
                <p class="text-blue-600">Status: <span class="font-semibold">${getStatusText(data.status)}</span></p>
            </div>
            
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <h5 class="font-semibold mb-2">Pengirim:</h5>
                    <p>${data.sender_name}</p>
                    <p class="text-sm text-gray-600">${data.sender_phone}</p>
                </div>
                <div>
                    <h5 class="font-semibold mb-2">Penerima:</h5>
                    <p>${data.receiver_name}</p>
                    <p class="text-sm text-gray-600">${data.receiver_phone}</p>
                </div>
            </div>
            
            <div>
                <h5 class="font-semibold mb-2">Detail Pengiriman:</h5>
                <p><strong>Layanan:</strong> ${data.service_type}</p>
                <p><strong>Deskripsi:</strong> ${data.item_description || '-'}</p>
                <p><strong>Berat:</strong> ${data.weight || '-'}</p>
                <p><strong>Dimensi:</strong> ${data.dimensions || '-'}</p>
            </div>
            
            ${data.notes ? `<div class="bg-yellow-50 p-3 rounded-lg">
                <h5 class="font-semibold text-yellow-800 mb-1">Catatan:</h5>
                <p class="text-yellow-700">${data.notes}</p>
            </div>` : ''}
            
            <div class="text-sm text-gray-500">
                <p>Dibuat: ${formatDate(data.created_at)}</p>
                <p>Diperbarui: ${formatDate(data.updated_at)}</p>
            </div>
        </div>
    `);
    
    document.body.appendChild(modal);
}

// Load blog posts
function loadBlogPosts() {
    const blogContainer = document.getElementById('blog-posts');
    if (!blogContainer) return;
    
    fetch(`${API_BASE_URL}/api/posts`)
        .then(response => response.json())
        .then(data => {
            if (data.records && data.records.length > 0) {
                blogContainer.innerHTML = data.records.slice(0, 4).map(post => `
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                        ${post.featured_image ? `
                            <img src="${post.featured_image}" alt="${post.title}" class="w-full h-48 object-cover">
                        ` : `
                            <div class="w-full h-48 bg-gradient-to-r from-blue-400 to-blue-600 flex items-center justify-center">
                                <i class="fas fa-newspaper text-white text-4xl"></i>
                            </div>
                        `}
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-2">${post.title}</h3>
                            <p class="text-gray-600 text-sm mb-4">${post.excerpt || ''}</p>
                            <div class="flex justify-between items-center text-sm text-gray-500">
                                <span>${formatDate(post.created_at)}</span>
                                ${post.category_name ? `<span class="bg-blue-100 text-blue-600 px-2 py-1 rounded">${post.category_name}</span>` : ''}
                            </div>
                        </div>
                    </div>
                `).join('');
            } else {
                blogContainer.innerHTML = `
                    <div class="col-span-full text-center py-12">
                        <i class="fas fa-newspaper text-gray-300 text-6xl mb-4"></i>
                        <p class="text-gray-500">Belum ada artikel blog</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading blog posts:', error);
            blogContainer.innerHTML = `
                <div class="col-span-full text-center py-12">
                    <i class="fas fa-exclamation-triangle text-red-300 text-6xl mb-4"></i>
                    <p class="text-red-500">Gagal memuat artikel blog</p>
                </div>
            `;
        });
}

// Handle booking form submission
function handleBookingSubmit(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const bookingData = Object.fromEntries(formData.entries());
    
    // Validate required fields
    const requiredFields = ['sender_name', 'sender_phone', 'receiver_name', 'receiver_phone', 'service_type'];
    const missingFields = requiredFields.filter(field => !bookingData[field]);
    
    if (missingFields.length > 0) {
        showAlert('Silakan lengkapi semua field yang wajib diisi', 'error');
        return;
    }
    
    // Show loading state
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mengirim...';
    submitBtn.disabled = true;
    
    // Send booking data to API
    fetch(`${API_BASE_URL}/api/bookings`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(bookingData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.tracking_number) {
            showAlert(`Booking berhasil dibuat! Nomor resi Anda: ${data.tracking_number}`, 'success');
            event.target.reset();
        } else {
            showAlert(data.message || 'Gagal membuat booking', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Terjadi kesalahan saat membuat booking', 'error');
    })
    .finally(() => {
        // Restore button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// Utility functions
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

function createModal(title, content) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50';
    modal.innerHTML = `
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-screen overflow-y-auto">
            <div class="flex items-center justify-between p-6 border-b">
                <h3 class="text-xl font-semibold">${title}</h3>
                <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="p-6">
                ${content}
            </div>
        </div>
    `;
    
    // Close modal when clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.remove();
        }
    });
    
    return modal;
}

function getStatusText(status) {
    const statusMap = {
        'pending': 'Menunggu Konfirmasi',
        'confirmed': 'Dikonfirmasi',
        'in_transit': 'Dalam Perjalanan',
        'delivered': 'Terkirim',
        'cancelled': 'Dibatalkan'
    };
    return statusMap[status] || status;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

