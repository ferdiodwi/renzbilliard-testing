<template>
  <div class="relative flex min-h-screen w-full flex-col overflow-x-hidden bg-[#f6f6f8] text-[#0e121b] font-display">
    <!-- Navigation -->
    <header class="fixed top-0 z-50 w-full bg-[#f6f6f8]/80 backdrop-blur-md border-b border-[#e7ebf3]">
      <div class="mx-auto flex max-w-[1440px] items-center justify-between px-6 lg:px-10 py-5">
        <div class="flex items-center gap-2">
          <img src="/images/logo.png" alt="Renz Billiard & Cafe" class="h-12 w-auto" />
          <span class="text-base sm:text-xl font-extrabold tracking-tighter uppercase">Renz Billiard & Cafe</span>
        </div>
        <nav class="hidden md:flex items-center gap-8 lg:gap-12">
          <a class="text-xs font-bold uppercase tracking-widest hover:text-[#01026E] transition-colors" href="#price">Harga</a>
          <a class="text-xs font-bold uppercase tracking-widest hover:text-[#01026E] transition-colors" href="#services">Fasilitas</a>
          <a class="text-xs font-bold uppercase tracking-widest hover:text-[#01026E] transition-colors" href="#location">Lokasi</a>
          <a 
            href="https://wa.me/6285258838664?text=Halo,%20saya%20ingin%20reservasi%20meja"
            target="_blank"
            class="bg-[#01026E] text-white px-6 py-2.5 rounded-full text-xs font-bold uppercase tracking-widest transition-all hover:bg-[#010255]"
          >
            Reservasi
          </a>
        </nav>
        <!-- Hamburger Button -->
        <button 
          @click="mobileMenuOpen = !mobileMenuOpen"
          class="md:hidden flex flex-col justify-center items-center w-10 h-10 gap-1.5"
          aria-label="Toggle menu"
        >
          <span 
            class="block w-6 h-0.5 bg-[#01026E] transition-all duration-300"
            :class="mobileMenuOpen ? 'rotate-45 translate-y-2' : ''"
          ></span>
          <span 
            class="block w-6 h-0.5 bg-[#01026E] transition-all duration-300"
            :class="mobileMenuOpen ? 'opacity-0' : ''"
          ></span>
          <span 
            class="block w-6 h-0.5 bg-[#01026E] transition-all duration-300"
            :class="mobileMenuOpen ? '-rotate-45 -translate-y-2' : ''"
          ></span>
        </button>
      </div>
      
      <!-- Mobile Menu -->
      <div 
        class="md:hidden overflow-hidden transition-all duration-300 bg-[#f6f6f8]"
        :class="mobileMenuOpen ? 'max-h-96 border-t border-[#e7ebf3]' : 'max-h-0'"
      >
        <nav class="flex flex-col px-6 py-4 gap-4">
          <a 
            @click="mobileMenuOpen = false"
            class="text-sm font-bold uppercase tracking-widest hover:text-[#01026E] transition-colors py-2" 
            href="#price"
          >Harga</a>
          <a 
            @click="mobileMenuOpen = false"
            class="text-sm font-bold uppercase tracking-widest hover:text-[#01026E] transition-colors py-2" 
            href="#services"
          >Fasilitas</a>
          <a 
            @click="mobileMenuOpen = false"
            class="text-sm font-bold uppercase tracking-widest hover:text-[#01026E] transition-colors py-2" 
            href="#location"
          >Lokasi</a>
          <a 
            href="https://wa.me/6285258838664?text=Halo,%20saya%20ingin%20reservasi%20meja"
            target="_blank"
            class="bg-[#01026E] text-white px-6 py-3 rounded-lg text-sm font-bold uppercase tracking-widest text-center transition-all hover:bg-[#010255]"
          >
            Reservasi
          </a>
          <router-link 
            to="/login" 
            class="text-sm font-bold uppercase tracking-widest text-gray-500 hover:text-[#01026E] transition-colors py-2 text-center"
          >Staff Login</router-link>
        </nav>
      </div>
    </header>

    <!-- Hero Section -->
    <section class="relative pt-28 lg:pt-32 pb-16 lg:pb-20 px-6 lg:px-10 max-w-[1440px] mx-auto w-full">
      <div class="flex flex-col lg:flex-row items-center gap-10 lg:gap-16">
        <!-- Content -->
        <div class="w-full lg:w-5/12 order-2 lg:order-1">
          <span class="text-[#01026E] font-bold tracking-[0.3em] uppercase text-[10px] block mb-4 hero-animate">Premium Billiard Center</span>
          <h1 class="text-4xl lg:text-6xl xl:text-7xl font-light leading-[1.1] mb-6 lg:mb-8 hero-animate-delay-1">
            Pengalaman <br/>
            <span class="font-extrabold text-[#01026E] italic">Bermain</span> Terbaik
          </h1>
          <p class="text-base lg:text-lg text-gray-600 font-light leading-relaxed max-w-md mb-8 lg:mb-10 hero-animate-delay-2">
            Nikmati meja billiard berkualitas tinggi dengan suasana nyaman dan pelayanan premium di Bondowoso.
          </p>
          <div class="flex flex-col sm:flex-row gap-4 hero-animate-delay-3">
            <a 
              href="https://wa.me/6285258838664?text=Halo,%20saya%20ingin%20reservasi%20meja%20billiard"
              target="_blank"
              class="bg-[#01026E] text-white px-8 py-4 rounded-lg font-bold text-sm uppercase tracking-widest text-center hover:bg-[#010255] transition-colors btn-pulse"
            >
              Reservasi Sekarang
            </a>
            <a 
              href="#price"
              class="border border-[#e7ebf3] px-8 py-4 rounded-lg font-bold text-sm uppercase tracking-widest hover:bg-[#e7ebf3] transition-colors text-center"
            >
              Lihat Harga
            </a>
          </div>
        </div>
        <!-- Hero Image -->
        <div class="w-full lg:w-7/12 order-1 lg:order-2">
          <div class="relative rounded-2xl overflow-hidden shadow-2xl aspect-[4/3] bg-gray-200 animate-float">
            <div 
              class="absolute inset-0 bg-cover bg-center transition-transform hover:scale-105 duration-1000"
              style="background-image: url('/images/hero.jpg');"
            ></div>
            <div class="absolute bottom-4 lg:bottom-6 left-4 lg:left-6 bg-white/10 backdrop-blur-md p-3 lg:p-4 rounded border border-white/20">
              <p class="text-white text-[10px] uppercase tracking-widest">Buka Setiap Hari • 08:00 - Tutup</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Pricing Section -->
    <section class="py-16 lg:py-24 bg-white" id="price">
      <div class="max-w-[1440px] mx-auto px-6 lg:px-10">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-12 lg:mb-16 border-b border-[#e7ebf3] pb-6 lg:pb-8 gap-4 animate-on-scroll">
          <div>
            <h2 class="text-2xl lg:text-3xl font-extrabold tracking-tight mb-2">Daftar Harga</h2>
            <p class="text-gray-500 font-light">Pilih sesuai kebutuhan Anda.</p>
          </div>
          <a 
            href="https://wa.me/6285258838664"
            target="_blank"
            class="text-[#01026E] text-xs font-bold uppercase tracking-widest flex items-center gap-2 group"
          >
            Tanya Kami
            <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
            </svg>
          </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
          <!-- 8 Feet -->
          <div class="group cursor-pointer animate-on-scroll card-lift">
            <div class="aspect-[4/3] overflow-hidden rounded-xl bg-gray-100 mb-6 relative">
              <div 
                class="absolute inset-0 bg-cover bg-center transition-transform duration-700 group-hover:scale-110"
                style="background-image: url('/images/price-8feet.jpg');"
              ></div>
              <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors"></div>
              <div class="absolute bottom-4 left-4 bg-white/90 backdrop-blur-sm px-4 py-2 rounded-lg">
                <p class="text-2xl font-extrabold text-[#01026E]">Rp 15.000<span class="text-sm font-normal text-gray-500">/jam</span></p>
              </div>
            </div>
            <h3 class="text-xl font-bold mb-1">Meja 8 Feet</h3>
            <p class="text-sm text-gray-500 uppercase tracking-widest font-medium">Ukuran Standar</p>
          </div>

          <!-- 9 Feet -->
          <div class="group cursor-pointer md:translate-y-8 animate-on-scroll delay-200 card-lift">
            <div class="aspect-[4/3] overflow-hidden rounded-xl bg-gray-100 mb-6 relative">
              <div 
                class="absolute inset-0 bg-cover bg-center transition-transform duration-700 group-hover:scale-110"
                style="background-image: url('/images/price-9feet.jpg');"
              ></div>
              <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors"></div>
              <div class="absolute top-4 right-4 bg-[#01026E] text-white px-3 py-1 rounded-full text-[10px] uppercase tracking-widest font-bold">Popular</div>
              <div class="absolute bottom-4 left-4 bg-white/90 backdrop-blur-sm px-4 py-2 rounded-lg">
                <p class="text-2xl font-extrabold text-[#01026E]">Rp 20.000<span class="text-sm font-normal text-gray-500">/jam</span></p>
              </div>
            </div>
            <h3 class="text-xl font-bold mb-1">Meja 9 Feet</h3>
            <p class="text-sm text-gray-500 uppercase tracking-widest font-medium">Ukuran Profesional</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Services Section -->
    <section class="py-20 lg:py-32 bg-[#f6f6f8]" id="services">
      <div class="max-w-[1440px] mx-auto px-6 lg:px-10">
        <div class="text-center mb-16 lg:mb-24 animate-on-scroll">
          <span class="text-[#01026E] font-bold tracking-[0.4em] uppercase text-[10px] block mb-4">Fasilitas Lengkap</span>
          <h2 class="text-3xl lg:text-4xl font-light">Pelayanan <span class="font-extrabold italic">Premium</span></h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-px bg-[#e7ebf3] border border-[#e7ebf3] rounded-2xl overflow-hidden animate-on-scroll delay-100">
          <!-- Service 1 -->
          <div class="bg-[#f6f6f8] p-8 lg:p-12 flex flex-col items-center text-center transition-colors hover:bg-white">
            <svg class="w-10 h-10 text-[#01026E] mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h4 class="text-sm font-bold uppercase tracking-widest mb-4">Meja Premium</h4>
            <p class="text-xs text-gray-500 leading-relaxed font-light">Meja billiard standar turnamen dengan kain premium berkualitas tinggi.</p>
          </div>
          <!-- Service 2 -->
          <div class="bg-[#f6f6f8] p-8 lg:p-12 flex flex-col items-center text-center transition-colors hover:bg-white">
            <svg class="w-10 h-10 text-[#01026E] mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            <h4 class="text-sm font-bold uppercase tracking-widest mb-4">WiFi</h4>
            <p class="text-xs text-gray-500 leading-relaxed font-light">WiFi gratis untuk kenyamanan bermain Anda.</p>
          </div>
          <!-- Service 3 -->
          <div class="bg-[#f6f6f8] p-8 lg:p-12 flex flex-col items-center text-center transition-colors hover:bg-white">
            <svg class="w-10 h-10 text-[#01026E] mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h4 class="text-sm font-bold uppercase tracking-widest mb-4">Buka Setiap Hari</h4>
            <p class="text-xs text-gray-500 leading-relaxed font-light">Siap melayani Anda setiap hari mulai pukul 08:00 hingga tutup.</p>
          </div>
          <!-- Service 4 -->
          <div class="bg-[#f6f6f8] p-8 lg:p-12 flex flex-col items-center text-center transition-colors hover:bg-white">
            <svg class="w-10 h-10 text-[#01026E] mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
            </svg>
            <h4 class="text-sm font-bold uppercase tracking-widest mb-4">Stick Premium</h4>
            <p class="text-xs text-gray-500 leading-relaxed font-light">Stick billiard berkualitas dengan berbagai pilihan untuk permainan optimal.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Quote Section -->
    <section class="relative py-16 lg:py-20 overflow-hidden bg-white">
      <div class="max-w-[1440px] mx-auto px-6 lg:px-10 flex flex-col md:flex-row items-center">
        <div class="w-full md:w-1/2 p-6 lg:p-20 md:border-r border-[#e7ebf3] animate-on-scroll">
          <p class="text-xl lg:text-2xl xl:text-3xl font-light italic leading-snug text-gray-700">
            "Bermain billiard bukan hanya tentang permainan, tapi tentang momen bersama teman dan relaksasi dari rutinitas."
          </p>
          <div class="mt-6 lg:mt-8 flex items-center gap-4">
            <div class="h-px w-12 bg-[#01026E]"></div>
            <span class="text-xs font-bold uppercase tracking-widest">Renz Billiard</span>
          </div>
        </div>
        <div class="w-full md:w-1/2 h-[300px] lg:h-[400px] relative mt-8 md:mt-0 animate-on-scroll delay-200">
          <img 
            src="/images/fasilitas.jpg"
            alt="Renz Billiard"
            class="absolute inset-0 w-full h-full object-cover rounded-2xl md:rounded-none"
          />
          <div class="absolute inset-0 bg-[#01026E]/10 mix-blend-overlay rounded-2xl md:rounded-none"></div>
        </div>
      </div>
    </section>

    <!-- Location Section -->
    <section class="py-16 lg:py-24 bg-[#f6f6f8]" id="location">
      <div class="max-w-[1440px] mx-auto px-6 lg:px-10">
        <div class="text-center mb-12 lg:mb-16 animate-on-scroll">
          <span class="text-[#01026E] font-bold tracking-[0.4em] uppercase text-[10px] block mb-4">Temukan Kami</span>
          <h2 class="text-3xl lg:text-4xl font-light">Lokasi <span class="font-extrabold italic">Kami</span></h2>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
          <div class="h-[300px] lg:h-[400px] rounded-2xl overflow-hidden shadow-lg animate-on-scroll">
            <iframe
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3951.8093738197927!2d113.82048337588803!3d-7.914971678787356!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd6dd000582e2e7%3A0x39826f60fac12b7f!2sRENZ%20Billiard%20%26%20Cafe!5e0!3m2!1sid!2sid!4v1768119802868!5m2!1sid!2sid"
              width="100%"
              height="100%"
              style="border:0;"
              allowfullscreen=""
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"
            ></iframe>
          </div>

          <div class="space-y-4 lg:space-y-6 animate-on-scroll delay-200">
            <div class="bg-white p-6 lg:p-8 rounded-xl border border-[#e7ebf3]">
              <div class="flex items-start gap-4">
                <div class="w-12 h-12 bg-[#e6e6f2] rounded-lg flex items-center justify-center flex-shrink-0">
                  <svg class="w-6 h-6 text-[#01026E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                  </svg>
                </div>
                <div>
                  <h4 class="text-sm font-bold uppercase tracking-widest mb-2">Alamat</h4>
                  <p class="text-gray-600 font-light">Jl. Letnan Sudiono No. 5<br/>Bondowoso, Jawa Timur, Indonesia</p>
                </div>
              </div>
            </div>

            <div class="bg-white p-6 lg:p-8 rounded-xl border border-[#e7ebf3]">
              <div class="flex items-start gap-4">
                <div class="w-12 h-12 bg-[#e6e6f2] rounded-lg flex items-center justify-center flex-shrink-0">
                  <svg class="w-6 h-6 text-[#01026E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <div>
                  <h4 class="text-sm font-bold uppercase tracking-widest mb-2">Jam Operasional</h4>
                  <p class="text-gray-600 font-light">Setiap Hari<br/>08:00 - Tutup</p>
                </div>
              </div>
            </div>

            <div class="bg-white p-6 lg:p-8 rounded-xl border border-[#e7ebf3]">
              <div class="flex items-start gap-4">
                <div class="w-12 h-12 bg-[#e6e6f2] rounded-lg flex items-center justify-center flex-shrink-0">
                  <svg class="w-6 h-6 text-[#01026E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                  </svg>
                </div>
                <div>
                  <h4 class="text-sm font-bold uppercase tracking-widest mb-2">Kontak</h4>
                  <a href="tel:+6285258838664" class="text-[#01026E] font-semibold text-lg hover:text-brand-600 transition-colors">+62 852-5883-8664</a>
                </div>
              </div>
            </div>

            <a 
              href="https://wa.me/6285258838664?text=Halo%20Renz%20Billiard,%20saya%20ingin%20reservasi%20meja"
              target="_blank"
              class="block w-full bg-[#01026E] text-white text-center px-8 py-4 rounded-xl font-bold text-sm uppercase tracking-widest hover:bg-[#010255] transition-colors"
            >
              Hubungi via WhatsApp
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white border-t border-[#e7ebf3] pt-16 lg:pt-24 pb-8 lg:pb-12">
      <div class="max-w-[1440px] mx-auto px-6 lg:px-10">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 lg:gap-12 mb-12 lg:mb-20">
          <div class="col-span-1 md:col-span-2">
            <div class="flex items-center gap-3 mb-6">
              <img src="/images/logo.png" alt="Renz Billiard & Cafe" class="h-14 w-auto" />
              <span class="text-xl font-extrabold tracking-tighter uppercase">Renz Billiard</span>
            </div>
            <p class="text-sm text-gray-500 font-light max-w-xs leading-relaxed mb-8">
              Tempat bermain billiard premium di Bondowoso dengan fasilitas terbaik dan pelayanan profesional.
            </p>
            <div class="flex gap-4">
              <a 
                href="https://wa.me/6285258838664"
                target="_blank"
                class="w-10 h-10 flex items-center justify-center rounded-full border border-[#e7ebf3] hover:bg-[#01026E] hover:text-white hover:border-[#01026E] transition-all"
              >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
              </a>
              <a 
                href="https://www.instagram.com/3r_billiard_sport_bondowoso"
                target="_blank"
                class="w-10 h-10 flex items-center justify-center rounded-full border border-[#e7ebf3] hover:bg-[#01026E] hover:text-white hover:border-[#01026E] transition-all"
              >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                </svg>
              </a>
            </div>
          </div>
          <div>
            <h4 class="text-xs font-bold uppercase tracking-widest mb-6">Navigasi</h4>
            <ul class="space-y-4 text-sm text-gray-500 font-light">
              <li><a class="hover:text-[#01026E] transition-colors" href="#price">Daftar Harga</a></li>
              <li><a class="hover:text-[#01026E] transition-colors" href="#services">Fasilitas</a></li>
              <li><a class="hover:text-[#01026E] transition-colors" href="#location">Lokasi</a></li>
            </ul>
          </div>
          <div>
            <h4 class="text-xs font-bold uppercase tracking-widest mb-6">Kontak</h4>
            <ul class="space-y-4 text-sm text-gray-500 font-light">
              <li>Jl. Letnan Sudiono No. 5</li>
              <li>Bondowoso, Jawa Timur</li>
              <li class="text-[#01026E] font-semibold">+62 852-5883-8664</li>
            </ul>
          </div>
        </div>
        <div class="flex flex-col md:flex-row justify-between items-center pt-6 lg:pt-8 border-t border-[#e7ebf3] gap-4">
          <p class="text-[10px] text-gray-400 uppercase tracking-[0.2em]">© 2024 Renz Billiard. All Rights Reserved.</p>
          <router-link to="/login" class="text-[10px] text-gray-400 uppercase tracking-[0.2em] hover:text-[#01026E] transition-colors">Staff Login</router-link>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

// Mobile menu state
const mobileMenuOpen = ref(false)

// Scroll animation observer
let observer = null

onMounted(() => {
  // Create intersection observer for scroll animations
  observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('animate-visible')
      }
    })
  }, {
    threshold: 0.05,
    rootMargin: '50px 0px 0px 0px'
  })

  // Small delay to ensure DOM is ready, then observe elements
  setTimeout(() => {
    document.querySelectorAll('.animate-on-scroll').forEach(el => {
      observer.observe(el)
    })
  }, 100)
})

onUnmounted(() => {
  if (observer) {
    observer.disconnect()
  }
})
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Manrope:wght@200;300;400;500;600;700;800&display=swap');

.font-display {
  font-family: 'Manrope', sans-serif;
}

/* Scroll animations */
.animate-on-scroll {
  opacity: 0;
  transform: translateY(30px);
  transition: opacity 0.8s ease-out, transform 0.8s ease-out;
}

.animate-on-scroll.animate-visible {
  opacity: 1;
  transform: translateY(0);
}

/* Stagger delay classes */
.delay-100 { transition-delay: 0.1s; }
.delay-200 { transition-delay: 0.2s; }
.delay-300 { transition-delay: 0.3s; }
.delay-400 { transition-delay: 0.4s; }

/* Hero text animation */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(40px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.hero-animate {
  animation: fadeInUp 1s ease-out forwards;
}

.hero-animate-delay-1 {
  opacity: 0;
  animation: fadeInUp 1s ease-out 0.2s forwards;
}

.hero-animate-delay-2 {
  opacity: 0;
  animation: fadeInUp 1s ease-out 0.4s forwards;
}

.hero-animate-delay-3 {
  opacity: 0;
  animation: fadeInUp 1s ease-out 0.6s forwards;
}

/* Float animation for hero image */
@keyframes float {
  0%, 100% {
    transform: translateY(0px);
  }
  50% {
    transform: translateY(-10px);
  }
}

.animate-float {
  animation: float 4s ease-in-out infinite;
}

/* Pulse animation for buttons */
@keyframes pulse-glow {
  0%, 100% {
    box-shadow: 0 0 0 0 rgba(1, 2, 110, 0.4);
  }
  50% {
    box-shadow: 0 0 20px 5px rgba(1, 2, 110, 0.2);
  }
}

.btn-pulse:hover {
  animation: pulse-glow 2s ease-in-out infinite;
}

/* Card hover lift */
.card-lift {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card-lift:hover {
  transform: translateY(-8px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

/* Smooth scroll */
html {
  scroll-behavior: smooth;
}

/* Parallax effect for sections */
.parallax-section {
  background-attachment: fixed;
  background-position: center;
  background-repeat: no-repeat;
  background-size: cover;
}
</style>
