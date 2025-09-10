// Fungsi inisialisasi panorama viewer
function initPanoramaViewer(containerId, imagePath) {
    const panorama = new PANOLENS.ImagePanorama(imagePath);
    const viewer = new PANOLENS.Viewer({
        container: document.getElementById(containerId),
        autoRotate: false,
        autoRotateSpeed: 0.3,
        controlBar: false,
        controlButtons: []
    });
    viewer.add(panorama);
    return viewer;
}

// Fungsi untuk toggle menu mobile
function setupMobileMenu() {
    const burgerMenu = document.querySelector('.burger-menu');
    const navLinks = document.querySelector('header nav ul');
    
    burgerMenu.addEventListener('click', () => {
        burgerMenu.classList.toggle('active');
        navLinks.classList.toggle('active');
    });
}

// Inisialisasi saat halaman selesai dimuat
document.addEventListener('DOMContentLoaded', function() {
    // Setup mobile menu
    setupMobileMenu();
    
    // Inisialisasi panorama viewers
    initPanoramaViewer('panorama1', 'image/rumah1.jpg');
    initPanoramaViewer('panorama2', 'image/rumah2.jpg');
    initPanoramaViewer('panorama3', 'image/rumah3.jpg');
});