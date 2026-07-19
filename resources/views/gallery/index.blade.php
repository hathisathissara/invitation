<x-app-layout>

    <!-- Page Title Slot -->
    <x-slot name="title">
        Photo Gallery & Story — Lumos Studio
    </x-slot>

    <!-- Page CSS Slot -->
    <x-slot name="styles">
        <link rel="stylesheet" href="{{ asset('css/gallery.css') }}?v=1.0">
    </x-slot>

    <!-- Status Alerts -->
    @if (session('status'))
        <div class="alert alert-success bg-success bg-opacity-10 text-success border-success border-opacity-20 rounded-3 mb-4" style="color: #86efac !important; font-size: 0.87rem; border-radius: 12px; padding: 13px 18px; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-check-circle"></i> {{ session('status') }}
        </div>
    @endif

    <div class="row g-3">
        <!-- Left Column: Love Story Form -->
        <div class="col-lg-5">
            <div class="card section-card" style="position:sticky; top:80px;">
                <h5><i class="fas fa-book-open"></i> Our Love Story</h5>
                <form method="POST" action="{{ route('gallery.story') }}">
                    @csrf
                    <div class="form-field">
                        <label>Tell guests how you met, your proposal, or a sweet message</label>
                        <textarea name="love_story" rows="12" placeholder="We first met at university in 2018...">{{ $wedding->love_story }}</textarea>
                    </div>
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i> Save Love Story
                    </button>
                </form>
            </div>
        </div>

        <!-- Right Column: Upload + Gallery Grid -->
        <div class="col-lg-7">
            <!-- Upload Card (Compressed Upload area) -->
            <div class="card section-card mb-3">
                <h5><i class="fas fa-camera"></i> Upload Engagement Photos</h5>
                <p style="font-size:0.82rem; color:#9ea3b0; margin-bottom:16px;">
                    Images are automatically compressed and converted to WebP format for fast loading.
                </p>
                <div class="drop-zone" id="drop-zone">
                    <input type="file" id="image-upload" accept="image/*" multiple>
                    <div class="drop-zone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                    <p class="drop-zone-text">
                        <strong>Click to upload</strong> or drag & drop<br>
                        JPG, PNG, WEBP — automatically optimized
                    </p>
                </div>
                <div class="upload-progress" id="upload-progress">
                    <i class="fas fa-circle-notch"></i> Compressing and uploading... please wait
                </div>
            </div>

            <!-- Gallery Grid Card -->
            <div class="card section-card">
                <h5><i class="fas fa-images"></i> Your Gallery</h5>
                <p class="gallery-count">{{ $images->count() }} photo{{ $images->count() !== 1 ? 's' : '' }} uploaded</p>

                @if ($images->count() > 0)
                <div class="gallery-grid">
                    @foreach ($images as $img)
                    <div class="gallery-item">
                        @if (!empty($wedding->hero_image) && $wedding->hero_image === $img->image_path)
                            <div class="cover-badge"><i class="fas fa-star"></i> Cover</div>
                        @endif
                        
                        <img src="{{ asset($img->image_path) }}" alt="Gallery photo" loading="lazy">
                        
                        <div class="gallery-item-overlay">
                            @if (empty($wedding->hero_image) || $wedding->hero_image !== $img->image_path)
                            <!-- Set Cover Form (Laravel Secure PATCH) -->
                            <form action="{{ route('gallery.cover', $img->id) }}" method="POST" style="width: 85%;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="gallery-action-btn btn-cover">
                                    <i class="fas fa-image"></i> Set Cover
                                </button>
                            </form>
                            @endif
                            
                            <!-- Delete Image Form (Laravel Secure DELETE) -->
                            <form action="{{ route('gallery.destroy', $img->id) }}" method="POST" onsubmit="return confirm('Remove this photo?');" style="width: 85%;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="gallery-action-btn btn-del">
                                    <i class="fas fa-trash-alt"></i> Remove
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="empty-gallery">
                    <i class="fas fa-image" style="font-size:2rem; opacity:0.2; display:block; margin-bottom:10px;"></i>
                    No photos uploaded yet. Share your special moments with your guests!
                </div>
                @endif
            </div>
        </div>
    </div>


    <!-- Page Scripts Slot -->
    <x-slot name="scripts">
        <!-- Load CompressorJS CDN Library -->
        <script src="https://cdn.jsdelivr.net/npm/compressorjs@1.2.1/dist/compressor.min.js"></script>
        
        <script>
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('image-upload');
        const progressDiv = document.getElementById('upload-progress');

        // Drag & drop wrapper styling
        dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('drag-over'); });
        dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('drag-over');
            const files = e.dataTransfer.files;
            if (files.length) uploadFiles(files);
        });

        fileInput.addEventListener('change', function() {
            if (this.files.length) uploadFiles(this.files);
        });

        function uploadFiles(files) {
            const fileArray = Array.from(files);
            let completed = 0;

            progressDiv.style.display = 'block';

            fileArray.forEach(file => {
                new Compressor(file, {
                    quality: 0.7,
                    mimeType: 'image/webp',
                    maxWidth: 1400,
                    success(result) {
                        const formData = new FormData();
                        const cleanName = file.name.replace(/\.[^/.]+$/, '') + '.webp';
                        formData.append('gallery_image', result, cleanName);

                        // AJAX Upload directly calling Laravel secure POST upload route
                        fetch('{{ route("gallery.upload") }}', { 
                            method: 'POST', 
                            body: formData,
                            headers: {
                                // 💡 Laravel CSRF header verification injection (Extremely Secure!)
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(r => r.json())
                        .then(data => {
                            completed++;
                            if (completed === fileArray.length) {
                                progressDiv.style.display = 'none';
                                if (data.success) {
                                    location.reload();
                                } else {
                                    alert('Upload failed: ' + (data.message || 'Unknown error'));
                                }
                            }
                        })
                        .catch(err => {
                            completed++;
                            progressDiv.style.display = 'none';
                            console.error('Upload Error:', err);
                        });
                    },
                    error(err) {
                        completed++;
                        progressDiv.style.display = 'none';
                        alert('Compression failed: ' + err.message);
                    }
                });
            });
        }
        </script>
    </x-slot>

</x-app-layout>