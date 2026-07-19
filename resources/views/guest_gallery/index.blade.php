<x-app-layout>

    <!-- Page Title Slot -->
    <x-slot name="title">
        Guest Shared Photos — Lumos Studio
    </x-slot>

    <!-- Page CSS Slot -->
    <x-slot name="styles">
        <link rel="stylesheet" href="{{ asset('css/guest_gallery.css') }}?v=1.0">
    </x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 2.2rem; font-weight: 600;">Guest Shared Photos (අමුත්තන් එවූ ඡායාරූප)</h2>
    </div>

    <!-- Status Alerts -->
    @if (session('status'))
        <div class="alert alert-success bg-success bg-opacity-10 text-success border-success border-opacity-20 rounded-3 mb-4" style="color: #86efac !important; font-size: 0.87rem; border-radius: 12px; padding: 13px 18px; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-check-circle"></i> {{ session('status') }}
        </div>
    @endif

    @if (!$hasGuestGallery)
        <!-- 🔒 UPGRADE LOCK BANNER -->
        <div class="card locked-upgrade-card">
            <i class="fas fa-lock text-warning" style="font-size: 3rem; margin-bottom: 20px;"></i>
            <h4 class="fw-bold mb-2">Guest Gallery Support is Locked</h4>
            <p class="mb-4">අමුත්තන්ට සජීවීව පින්තූර අප්ලෝඩ් කිරීමට සහ ඒවා කළමනාකරණය කිරීමට ඇති මෙම සුවිශේෂී පහසුකම ක්‍රියාත්මක වන්නේ <strong>Premium Plan</strong> එකෙහි හෝ <strong>Guest Gallery Add-on</strong> එක මිලදී ගත් අයට පමණි.</p>
            <a href="#" class="topbar-btn-gold">
                <i class="fas fa-arrow-circle-up"></i> Upgrade Plan / Activate Add-on
            </a>
        </div>

    @else
        <!-- 📸 ACTUALLY ACTIVE: PHOTO GRID DISPLAY -->
        <div class="card p-4">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h5 class="m-0" style="font-size: 0.95rem; font-weight: 600;">Shared Moments (<span id="gallery-photo-count">{{ $guestImages->count() }}</span> Photos)</h5>
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20" style="font-size: 0.72rem; padding: 5px 12px; color: #86efac !important;"><i class="fas fa-check-circle"></i> Live Sharing Active</span>
            </div>

            <div id="gallery-photos-wrapper">
            @if ($guestImages->count() > 0)
                <div class="gallery-grid">
                    @foreach ($guestImages as $g_pic)
                        <div class="gallery-item-card">
                            <div class="img-container">
                                <!-- asset helper used to resolve uploads path in laravel storage/public folder -->
                                <img src="{{ asset($g_pic->image_path) }}" alt="Guest upload">
                            </div>
                            <div class="meta-container">
                                <div class="meta-uploader" title="{{ $g_pic->guest_name }}">
                                    <i class="far fa-user me-1"></i> By {{ $g_pic->guest_name }}
                                </div>
                                <div class="meta-date">
                                    <i class="far fa-clock me-1"></i> {{ \Carbon\Carbon::parse($g_pic->created_at)->format('d M, h:i A') }}
                                </div>
                            </div>
                            
                            <!-- Action buttons row (Download JPG සහ Delete) -->
                            <div class="action-button-row">
                                <!-- 📥 WebP to JPG Stream Converter trigger -->
                                <a href="{{ route('guest-gallery.download', $g_pic->id) }}" class="btn-download-moment" title="Download as high-quality JPG image">
                                    <i class="fas fa-download"></i> JPG
                                </a>
                                
                                <!-- 🗑️ Secure Delete Form -->
                                <form action="{{ route('guest-gallery.destroy', $g_pic->id) }}" method="POST" onsubmit="return confirm('මෙම ඡායාරූපය සදහටම මකා දැමීමට අවශ්‍ය බව විශ්වාසද?');" style="flex:1;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-delete-moment" title="Permanently delete from platform">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center text-muted py-5" style="font-style: italic;">
                    <i class="fas fa-camera-retro mb-2" style="font-size: 2.2rem; opacity: 0.3; display:block; margin: 0 auto; color: #c9a96e;"></i>
                    තවමත් කිසිදු අමුත්තෙක් ඡායාරූපයක් අප්ලෝඩ් කර නැත. <br>විවාහ උත්සවය දවසේදී අමුත්තන් ලින්ක් එකෙන් පින්තූර එවූ පසු ඒවා මෙහි දිස්වේවි!
                </div>
            @endif
            </div>
        </div>
    @endif


    <!-- Page Scripts Slot -->
    <x-slot name="scripts">
        @if ($hasGuestGallery)
        <script>
        // =====================================================================
        // 🔥 සජීවීව අමුත්තන් එවූ අලුත් ඡායාරූප Check කිරීම — 6s Polling
        // =====================================================================
        let lastKnownPhotoIds = @json($guestImages->pluck('id'));

        function renderGalleryCard(img) {
            const csrfToken = "{{ csrf_token() }}";
            
            // Generate clean delete route URL manually for vanilla JS render
            const deleteRouteUrl = `{{ url('/dashboard/guest-gallery') }}/${img.id}`;
            const downloadRouteUrl = `{{ url('/dashboard/guest-gallery/download') }}/${img.id}`;

            return `
                <div class="gallery-item-card">
                    <div class="img-container">
                        <img src="{{ asset('') }}${img.image_path}" alt="Guest upload">
                    </div>
                    <div class="meta-container">
                        <div class="meta-uploader" title="${img.guest_name}">
                            <i class="far fa-user me-1"></i> By ${img.guest_name}
                        </div>
                        <div class="meta-date">
                            <i class="far fa-clock me-1"></i> ${img.uploaded_at_formatted}
                        </div>
                    </div>
                    <div class="action-button-row">
                        <a href="${downloadRouteUrl}" class="btn-download-moment" title="Download as high-quality JPG image">
                            <i class="fas fa-download"></i> JPG
                        </a>
                        
                        <form action="${deleteRouteUrl}" method="POST" onsubmit="return confirm('මෙම ඡායාරූපය සදහටම මකා දැමීමට අවශ්‍ය බව විශ්වාසද?');" style="flex:1;">
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn-delete-moment" title="Permanently delete from platform">
                                <i class="fas fa-trash-alt"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>`;
        }

        function fetchGuestGalleryLive() {
            fetch("{{ route('guest-gallery.live-check') }}")
                .then(r => r.json())
                .then(data => {
                    if (!data.images) return;

                    const newIds = data.images.map(i => i.id);
                    const sameAsBefore = newIds.length === lastKnownPhotoIds.length
                        && newIds.every((id, idx) => id === lastKnownPhotoIds[idx]);
                    if (sameAsBefore) return;

                    lastKnownPhotoIds = newIds;
                    document.getElementById('gallery-photo-count').textContent = data.images.length;

                    const wrapper = document.getElementById('gallery-photos-wrapper');
                    if (data.images.length === 0) {
                        wrapper.innerHTML = `<div class="text-center text-muted py-5" style="font-style: italic;">
                            <i class="fas fa-camera-retro mb-2" style="font-size: 2.2rem; opacity: 0.3; display:block; margin: 0 auto; color: #c9a96e;"></i>
                            තවමත් කිසිදු අමුත්තෙක් ඡායාරූපයක් අප්ලෝඩ් කර නැත. <br>විවාහ උත්සවය දවසේදී අමුත්තන් ලින්ක් එකෙන් පින්තූර එවූ පසු ඒවා මෙහි දිස්වේවි!
                        </div>`;
                        return;
                    }

                    let html = '<div class="gallery-grid">';
                    data.images.forEach(img => {
                        const d = new Date(img.uploaded_at.replace(' ', 'T'));
                        img.uploaded_at_formatted = isNaN(d.getTime()) ? '' : d.toLocaleString('en-US', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit', hour12: true });
                        html += renderGalleryCard(img);
                    });
                    html += '</div>';
                    wrapper.innerHTML = html;
                })
                .catch(err => console.error('Error syncing guest gallery:', err));
        }
        setInterval(fetchGuestGalleryLive, 6000);
        </script>
        @endif
    </x-slot>

</x-app-layout>