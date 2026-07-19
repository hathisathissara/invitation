<x-app-layout>

    <!-- Page Title Slot -->
    <x-slot name="title">
        Customize Invitation — Lumos Studio
    </x-slot>

    <!-- Page CSS Slot -->
    <x-slot name="styles">
        <!-- Load Bootstrap CSS for Modal popup toggles -->
       
        <link rel="stylesheet" href="{{ asset('css/customize.css') }}?v=1.1">
    </x-slot>

    @php
        // Validation Error එකක් හෝ Success එකක් ආවොත් අදාල Accordion එක Auto-open කිරීමට
        $openSection = session('open_section', 'acc-design');
    @endphp

    <!-- Alerts Status -->
    @if (session('status'))
        <div class="alert alert-success bg-success bg-opacity-10 text-success border-success border-opacity-20 rounded-3 mb-4" style="color: #16a34a !important; font-size: 0.87rem; border-radius: 12px; padding: 13px 18px; display: flex; align-items: center; gap: 10px; border: 1px solid rgba(34,197,94,0.25);">
            <i class="fas fa-check-circle"></i> {{ session('status') }}
        </div>
    @endif

    <div class="settings-accordion">

        <!-- ======== 1. Design Template ======== -->
        <div class="acc-item {{ $openSection === 'acc-design' ? 'open' : '' }}" id="acc-design">
            <button type="button" class="acc-header" onclick="toggleAcc('acc-design')">
                <div class="acc-header-left">
                    <div class="acc-icon"><i class="fas fa-paint-brush"></i></div>
                    <div>
                        <div class="acc-title">Design Template</div>
                        <div class="acc-sub">Choose the look & feel of your invitation</div>
                    </div>
                </div>
                <div class="acc-chevron"><i class="fas fa-chevron-down"></i></div>
            </button>
            <div class="acc-body">
                <form method="POST" action="{{ route('customize.design') }}" id="designForm">
                    @csrf
                    <div class="tpl-grid">
                        @foreach ($allTemplates as $key => $tpl)
                        <label class="tpl-card {{ $wedding->template_name === $key ? 'selected' : '' }}" data-tpl="{{ $key }}">
                            <input type="radio" name="template_name" value="{{ $key }}" {{ $wedding->template_name === $key ? 'checked' : '' }} onchange="selectTpl(this)">
                            <div class="tpl-swatch" style="background: linear-gradient(135deg, {{ $tpl['primary'] }}, {{ $tpl['accent'] }});"></div>
                            <div class="tpl-body">
                                <div class="tpl-name">{{ $tpl['label'] }}</div>
                                <div class="tpl-sub">{{ $tpl['sub'] }}</div>
                            </div>
                            <div class="tpl-check"><i class="fas fa-check"></i></div>
                            
                            <!-- Dynamic preview testing link passing preview_template via URL -->
                            @if (!empty($wedding->slug))
                            <a class="tpl-test-link" target="_blank" rel="noopener"
                               href="{{ route('invitation.invite', ['slug' => $wedding->slug, 'preview_template' => $key]) }}"
                               onclick="event.stopPropagation();">
                                <i class="fas fa-external-link-alt"></i> Test this template
                            </a>
                            @endif
                        </label>
                        @endforeach
                    </div>
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i> Save Design Template
                    </button>
                </form>
            </div>
        </div>

        <!-- ======== 2. Language ======== -->
        <div class="acc-item {{ $openSection === 'acc-language' ? 'open' : '' }}" id="acc-language">
            <button type="button" class="acc-header" onclick="toggleAcc('acc-language')">
                <div class="acc-header-left">
                    <div class="acc-icon"><i class="fas fa-language"></i></div>
                    <div>
                        <div class="acc-title">Invitation Language</div>
                        <div class="acc-sub">Interface labels shown to your guests</div>
                    </div>
                </div>
                <div class="acc-chevron"><i class="fas fa-chevron-down"></i></div>
            </button>
            <div class="acc-body">
                <p style="font-size:0.82rem; color:#64748b; margin-bottom:16px; text-align: left;">
                    This changes labels like RSVP, Countdown and Programme headings. Anything you typed yourself
                    (love story, venue, event names) stays exactly as you wrote it.
                </p>
                <form method="POST" action="{{ route('customize.language') }}" id="languageForm">
                    @csrf
                    <div class="lang-grid">
                        @foreach ($languages as $code => $l)
                        <label class="lang-card {{ $wedding->invite_language === $code ? 'selected' : '' }}" onclick="selectLang(this)">
                            <input type="radio" name="invite_language" value="{{ $code }}" {{ $wedding->invite_language === $code ? 'checked' : '' }}>
                            <div class="lang-native">{{ $l['native'] }}</div>
                            <div class="lang-english">{{ $l['label'] }}</div>
                        </label>
                        @endforeach
                    </div>
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i> Save Language
                    </button>
                </form>
            </div>
        </div>

        <!-- ======== 3. Music ======== -->
        <div class="acc-item {{ $openSection === 'acc-music' ? 'open' : '' }}" id="acc-music">
            <button type="button" class="acc-header" onclick="toggleAcc('acc-music')">
                <div class="acc-header-left">
                    <div class="acc-icon"><i class="fas fa-music"></i></div>
                    <div>
                        <div class="acc-title">Background Music</div>
                        <div class="acc-sub">Optional music that plays on your invitation</div>
                    </div>
                </div>
                <div class="acc-chevron"><i class="fas fa-chevron-down"></i></div>
            </button>
            <div class="acc-body">
                <form method="POST" action="{{ route('customize.music') }}" id="musicForm">
                    @csrf
                    <div class="music-toggle-row">
                        <div style="text-align: left;">
                            <div style="font-size:0.88rem; font-weight:600; color:#1a1a2e;">Play music on my invitation</div>
                            <div style="font-size:0.76rem; color:#94a3b8; margin-top:2px;">Guests can always mute it themselves</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="musicEnabledInput" name="music_enabled" value="1" {{ !empty($wedding->music_track) ? 'checked' : '' }} onchange="toggleMusicList(this.checked)">
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="music-list" id="musicList" style="{{ empty($wedding->music_track) ? 'display:none;' : '' }}">
                        @foreach ($musicLibrary as $key => $track)
                        <label class="music-item {{ $wedding->music_track === $key ? 'selected' : '' }}" onclick="selectMusic(this)">
                            <input type="radio" name="music_track" value="{{ $key }}" {{ $wedding->music_track === $key ? 'checked' : '' }}>
                            <i class="fas fa-compact-disc" style="color:#c9a96e;"></i>
                            <span class="music-item-name">{{ $track['label'] }}</span>
                            <button type="button" class="music-preview-btn" onclick="event.preventDefault(); event.stopPropagation(); previewTrack('{{ asset($track['file']) }}', this)">
                                <i class="fas fa-play"></i>
                            </button>
                        </label>
                        @endforeach
                    </div>

                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i> Save Music Choice
                    </button>
                </form>
            </div>
        </div>

    </div>

    <!-- Mobile floating save bar -->
    <div class="mobile-save-bar" id="mobileSaveBar">
        <div class="msb-info">
            <div class="msb-title" id="msbTitle">Design Template</div>
            <div class="msb-sub" id="msbSub"></div>
        </div>
        <button type="button" class="msb-save-btn" id="msbSaveBtn" onclick="submitActiveForm()">
            <i class="fas fa-save"></i> Save
        </button>
    </div>

    <audio id="previewPlayer"></audio>


    <!-- Page Scripts Slot -->
    <x-slot name="scripts">
        <!-- Load Bootstrap JS for Modal triggers -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

        <script>
            // ===== Mobile sticky save bar =====
            const accBarConfig = {
                'acc-design':   { title: 'Design Template',      form: 'designForm' },
                'acc-language': { title: 'Invitation Language',  form: 'languageForm' },
                'acc-music':    { title: 'Background Music',     form: 'musicForm' }
            };

            function getSelectionText(accId) {
                if (accId === 'acc-design') {
                    const sel = document.querySelector('.tpl-card.selected .tpl-name');
                    return sel ? sel.textContent.trim() : 'Choose a template';
                }
                if (accId === 'acc-language') {
                    const sel = document.querySelector('.lang-card.selected .lang-english');
                    return sel ? sel.textContent.trim() : 'Choose a language';
                }
                if (accId === 'acc-music') {
                    const enabled = document.getElementById('musicEnabledInput').checked;
                    if (!enabled) return 'Off';
                    const sel = document.querySelector('.music-item.selected .music-item-name');
                    return sel ? sel.textContent.trim() : 'Choose a track';
                }
                return '';
            }

            function updateMobileBar(accId) {
                const bar = document.getElementById('mobileSaveBar');
                const conf = accBarConfig[accId];
                if (!conf) {
                    bar.classList.remove('active');
                    return;
                }
                document.getElementById('msbTitle').textContent = conf.title;
                document.getElementById('msbSub').textContent = getSelectionText(accId);
                bar.dataset.targetForm = conf.form;
                bar.classList.add('active');
            }

            function refreshBarIfOpen(accId) {
                const item = document.getElementById(accId);
                if (item && item.classList.contains('open')) updateMobileBar(accId);
            }

            function submitActiveForm() {
                const bar = document.getElementById('mobileSaveBar');
                const formId = bar.dataset.targetForm;
                if (!formId) return;
                const form = document.getElementById(formId);
                if (!form) return;
                if (form.requestSubmit) form.requestSubmit();
                else form.submit();
            }

            function toggleAcc(id) {
                const item = document.getElementById(id);
                const isOpen = item.classList.contains('open');
                document.querySelectorAll('.acc-item').forEach(el => el.classList.remove('open'));
                if (!isOpen) {
                    item.classList.add('open');
                    updateMobileBar(id);
                } else {
                    updateMobileBar(null);
                }
            }

            function selectTpl(input) {
                document.querySelectorAll('.tpl-card').forEach(c => c.classList.remove('selected'));
                input.closest('.tpl-card').classList.add('selected');
                refreshBarIfOpen('acc-design');
            }

            function selectLang(label) {
                document.querySelectorAll('.lang-card').forEach(c => c.classList.remove('selected'));
                label.classList.add('selected');
                refreshBarIfOpen('acc-language');
            }

            function selectMusic(label) {
                document.querySelectorAll('.music-item').forEach(c => c.classList.remove('selected'));
                label.classList.add('selected');
                refreshBarIfOpen('acc-music');
            }

            function toggleMusicList(show) {
                document.getElementById('musicList').style.display = show ? 'flex' : 'none';
                refreshBarIfOpen('acc-music');
            }

            // Design Template accordion starts open by default — show the bar right away on mobile
            document.addEventListener('DOMContentLoaded', function () {
                updateMobileBar('acc-design');
            });

            // Audition preview player logic
            let currentPreviewBtn = null;
            function previewTrack(file, btn) {
                const player = document.getElementById('previewPlayer');
                if (currentPreviewBtn && currentPreviewBtn !== btn) {
                    currentPreviewBtn.innerHTML = '<i class="fas fa-play"></i>';
                }
                if (player.src.endsWith(file) && !player.paused) {
                    player.pause();
                    btn.innerHTML = '<i class="fas fa-play"></i>';
                    currentPreviewBtn = null;
                    return;
                }
                player.src = file;
                player.play().catch(() => {});
                btn.innerHTML = '<i class="fas fa-pause"></i>';
                currentPreviewBtn = btn;
            }
            document.getElementById('previewPlayer').addEventListener('ended', function () {
                if (currentPreviewBtn) currentPreviewBtn.innerHTML = '<i class="fas fa-play"></i>';
                currentPreviewBtn = null;
            });
        </script>
    </x-slot>

</x-app-layout>