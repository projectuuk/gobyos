document.addEventListener('DOMContentLoaded', function() {
    // Get slug from URL path
    const pathParts = window.location.pathname.split('/');
    const slug = pathParts[pathParts.length - 1]; // Get the last part of the path

    if (slug && slug !== '') {
        fetch(`/backend/api/posts_mock.php?slug=${slug}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(post => {
                if (post) {
                    document.getElementById('article-title').innerText = post.title;
                    document.getElementById('article-excerpt').innerText = post.excerpt;
                    document.getElementById('article-content').innerHTML = post.content;
                    document.getElementById('article-meta').innerText = `Dipublikasikan pada ${new Date(post.created_at).toLocaleDateString()}`;

                    // Update SEO Meta Tags
                    document.getElementById('page-title').innerText = post.meta_title || post.title;
                    document.getElementById('page-description').setAttribute('content', post.meta_description || post.excerpt);
                    document.getElementById('page-keywords').setAttribute('content', post.meta_keywords || '');
                    document.getElementById('canonical-url').setAttribute('href', `/${post.slug}`);
                    document.getElementById('og-title').setAttribute('content', post.meta_title || post.title);
                    document.getElementById('og-description').setAttribute('content', post.meta_description || post.excerpt);
                    document.getElementById('og-image').setAttribute('content', post.featured_image || '/assets/images/blog-og.jpg');
                    document.getElementById('og-url').setAttribute('content', `/${post.slug}`);
                    document.getElementById('twitter-title').setAttribute('content', post.meta_title || post.title);
                    document.getElementById('twitter-description').setAttribute('content', post.meta_description || post.excerpt);
                    document.getElementById('twitter-image').setAttribute('content', post.featured_image || '/assets/images/blog-og.jpg');

                    // Update Structured Data
                    const structuredData = {
                        "@context": "https://schema.org",
                        "@type": "Article",
                        "headline": post.title,
                        "description": post.excerpt,
                        "image": post.featured_image || '/assets/images/blog-og.jpg',
                        "datePublished": post.created_at,
                        "author": {
                            "@type": "Person",
                            "name": "Fio Trans Cargo"
                        },
                        "publisher": {
                            "@type": "Organization",
                            "name": "Fio Trans Cargo",
                            "logo": {
                                "@type": "ImageObject",
                                "url": "/assets/images/logo.png"
                            }
                        },
                        "mainEntityOfPage": {
                            "@type": "WebPage",
                            "@id": `/artikel/${post.slug}`
                        }
                    };
                    document.getElementById('structured-data').innerHTML = JSON.stringify(structuredData, null, 2);

                } else {
                    document.getElementById('article-title').innerText = 'Artikel Tidak Ditemukan';
                    document.getElementById('article-excerpt').innerText = 'Maaf, artikel yang Anda cari tidak ditemukan.';
                }
            })
            .catch(error => {
                console.error('Error fetching article:', error);
                document.getElementById('article-title').innerText = 'Terjadi Kesalahan';
                document.getElementById('article-excerpt').innerText = 'Gagal memuat artikel. Silakan coba lagi nanti.';
            });
    } else {
        document.getElementById('article-title').innerText = 'Artikel Tidak Ditemukan';
        document.getElementById('article-excerpt').innerText = 'Maaf, artikel yang Anda cari tidak ditemukan.';
    }
});


