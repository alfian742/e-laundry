/**
 *
 * You can write your JS code here, DO NOT touch the default style file
 * because it will make it harder for you to update.
 * 
 */

"use strict";

// Attach event listeners untuk scroll links
function attachScrollLinks(selector) {
    document.querySelectorAll(selector).forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                smoothScrollTo(target.offsetTop, 600); // scroll ke target selama 600ms
            }
        });
    });
}

// Fungsi untuk smooth scroll ke target
function smoothScrollTo(targetY, duration) {
    const startY = window.scrollY || window.pageYOffset;
    const distance = targetY - startY;
    const startTime = performance.now();

    function scroll(currentTime) {
        const timeElapsed = currentTime - startTime;
        const progress = Math.min(timeElapsed / duration, 1);
        const ease = easeInOutQuad(progress); // pakai easing

        window.scrollTo(0, startY + distance * ease);

        if (progress < 1) {
            requestAnimationFrame(scroll);
        }
    }

    requestAnimationFrame(scroll);
}

// Fungsi easing (bisa diganti sesuai selera)
function easeInOutQuad(t) {
    return t < 0.5
        ? 2 * t * t
        : -1 + (4 - 2 * t) * t;
}

// Memanggil fungsi untuk menghubungkan semua link dengan smooth scroll
attachScrollLinks('a.scroll-link[href^="#"]');

// ====================================================================

// Ambil elemen dengan id scrollToTopPage dan footer
const scrollToTopPage = document.getElementById('scrollToTopPage');
const footer = document.querySelector('footer');

// Fungsi untuk mengecek posisi scroll dan menambahkan/menyembunyikan class d-none
function checkScrollPosition() {
    const scrollPosition = window.scrollY;
    const footerPosition = footer.offsetTop + footer.offsetHeight;

    if (scrollPosition >= footerPosition - window.innerHeight) {
        scrollToTopPage.style.fontSize = '8rem';
    } else {
        scrollToTopPage.style.fontSize = '1rem';
    }

    if (scrollPosition === 0) {
        scrollToTopPage.style.opacity = '0';
        setTimeout(() => {
            scrollToTopPage.classList.add('d-none');
        }, 300);
    } else {
        scrollToTopPage.classList.remove('d-none');
        scrollToTopPage.style.opacity = '1';
    }
}


// Fungsi untuk mengubah ukuran font secara animasi (perbaikan untuk animasi yang lebih halus)
function animateFontSize(targetSize) {
    const currentSize = parseFloat(window.getComputedStyle(scrollToTopPage).fontSize);
    const targetValue = parseFloat(targetSize);
    const duration = 300;
    const steps = 20;
    const stepTime = duration / steps;
    const step = (targetValue - currentSize) / steps;

    let currentStep = 0;

    // Fungsi untuk animasi dengan setTimeout (lebih halus)
    function animate() {
        if (currentStep < steps) {
            const newSize = currentSize + step * currentStep;
            scrollToTopPage.style.fontSize = newSize + 'px';
            currentStep++;
            setTimeout(animate, stepTime);
        } else {
            scrollToTopPage.style.fontSize = targetSize;
        }
    }

    // Mulai animasi
    animate();
}

// Panggil fungsi saat scroll dan saat halaman pertama kali dimuat
window.addEventListener('scroll', checkScrollPosition);
window.addEventListener('load', checkScrollPosition);

// ====================================================================

$(document).ready(function () {
    // Tooltip khusus tombol salin, trigger-nya pakai 'click'
    $('.btn-clipboard').tooltip({
        trigger: 'click'
    });

    // Tooltip lainnya tetap default, tapi tidak termasuk tombol salin
    $('[data-toggle="tooltip"]').not('.btn-clipboard').tooltip();
});

function copyToClipboard(event) {
    const button = event.currentTarget; // Jauh lebih aman daripada event.target
    const container = button.closest('.clipboard');
    const textToCopy = container.querySelector('.clipboard-text').innerText;

    navigator.clipboard.writeText(textToCopy).then(function () {
        const icon = button.querySelector('i');

        icon.classList.remove('fa-clipboard');
        icon.classList.add('fa-clipboard-check');

        button.setAttribute('title', 'Berhasil disalin');
        $(button).tooltip('dispose').tooltip({
            trigger: 'click'
        }).tooltip('show');

        setTimeout(function () {
            $(button).tooltip('hide');
            icon.classList.remove('fa-clipboard-check');
            icon.classList.add('fa-clipboard');
            button.setAttribute('title', '');
        }, 2000);
    }).catch(function (error) {
        console.error('Gagal menyalin teks: ', error);
    });
}

// ====================================================================

// Helper: Ubah HEX ke RGBA
function hexToRgba(hex, alpha) {
    const bigint = parseInt(hex.replace('#', ''), 16);
    const r = (bigint >> 16) & 255;
    const g = (bigint >> 8) & 255;
    const b = bigint & 255;
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

// Helper: Gelapkan warna HEX
function darkenColor(hex, percent) {
    const num = parseInt(hex.replace('#', ''), 16);
    const amt = Math.round(2.55 * percent);
    const r = (num >> 16) - amt;
    const g = (num >> 8 & 0x00FF) - amt;
    const b = (num & 0x0000FF) - amt;
    return `rgb(${Math.max(r, 0)}, ${Math.max(g, 0)}, ${Math.max(b, 0)})`;
}

// Ambil inisial dari nama
function getInitials(name) {
    const parts = name.trim().split(' ');
    return (parts[0]?.[0] || '') + (parts[1]?.[0] || '');
}

// Pilih warna berdasarkan inisial
function getColorByInitials(initials) {
    const colors = ["#1abc9c", "#2ecc71", "#3498db", "#9b59b6", "#34495e",
        "#16a085", "#27ae60", "#2980b9", "#8e44ad", "#2c3e50",
        "#f1c40f", "#e67e22", "#e74c3c", "#95a5a6", "#f39c12",
        "#d35400", "#c0392b", "#bdc3c7", "#7f8c8d"];
    const index = (initials.charCodeAt(0) - 65) % colors.length;
    return colors[Math.max(0, index)];
}

// Gambar avatar ke canvas
function drawAvatarCanvas(initials, size, bgColor, textColor) {
    const canvas = document.createElement('canvas');
    canvas.width = canvas.height = size;
    const ctx = canvas.getContext('2d');

    // Lingkaran background
    ctx.beginPath();
    ctx.arc(size / 2, size / 2, size / 2, 0, Math.PI * 2);
    ctx.fillStyle = bgColor;
    ctx.fill();
    ctx.closePath();

    // Inisial
    ctx.font = `${size * 0.42}px Arial`;
    ctx.fillStyle = textColor;
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(initials, size / 2, size / 2);

    return canvas.toDataURL();
}

// Proses semua elemen avatar-initials
function renderAvatars() {
    $('.avatar-initials').each(function () {
        const $img = $(this);
        const name = $img.data('name') || 'A A';
        const size = parseInt($img.attr('width')) || 100;
        const initials = getInitials(name).toUpperCase();

        let bgColor, textColor;

        if ($img.hasClass('avatar-initial-default')) {
            // Default avatar warna tetap
            bgColor = '#E0EBFD';
            textColor = '#007BFF';
        } else {
            // Warna berdasarkan nama
            const baseColor = getColorByInitials(initials);
            bgColor = hexToRgba(baseColor, 0.7);
            textColor = darkenColor(baseColor, 30);
        }

        const dataURL = drawAvatarCanvas(initials, size, bgColor, textColor);
        $img.attr('src', dataURL);
    });
}

// Jalankan saat dokumen siap
$(document).ready(renderAvatars);
















