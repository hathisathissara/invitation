<x-app-layout>

    <!-- Page Title Slot -->
    <x-slot name="title">
        Settings — Lumos Studio
    </x-slot>

    <!-- Page CSS Slot -->
    <x-slot name="styles">
        <!-- Load Bootstrap CSS for Modal -->
        <link rel="stylesheet" href="{{ asset('css/settings.css') }}?v=1.1">
    </x-slot>

    @php
        // Validation Error එකක් හෝ Success එකක් ආවොත් අදාල Accordion එක Auto-open කිරීමට
        $openSection = session('open_section', 'wedding');
        if ($errors->has('current_password') || $errors->has('password')) {
            $openSection = 'password';
        }
        if ($errors->has('delete_password')) {
            $openSection = 'danger';
        }
    @endphp

    <div class="settings-accordion">

        <!-- ======== 1. Wedding Details ======== -->
        <div class="acc-item {{ $openSection === 'wedding' ? 'open' : '' }}" id="acc-wedding">
            <button type="button" class="acc-header" onclick="toggleAcc('acc-wedding')">
                <div class="acc-header-left">
                    <div class="acc-icon"><i class="fas fa-heart"></i></div>
                    <div>
                        <div class="acc-title">Wedding Details</div>
                        <div class="acc-sub">Update couple names, date & venue</div>
                    </div>
                </div>
                <div class="acc-chevron"><i class="fas fa-chevron-down"></i></div>
            </button>
            <div class="acc-body">
                
                @if (session('status') && session('open_section') === 'wedding')
                    <div class="alert-box alert-success">
                        <i class="fas fa-check-circle"></i> {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('settings.wedding') }}">
                    @csrf
                    <div class="form-row">
                        <div class="form-field">
                            <label>Bride's Name</label>
                            <div class="input-wrap">
                                <i class="fas fa-heart"></i>
                                <input type="text" name="bride_name" value="{{ old('bride_name', $wedding->bride_name ?? '') }}" placeholder="Bride's name" required>
                            </div>
                        </div>
                        <div class="form-field">
                            <label>Groom's Name</label>
                            <div class="input-wrap">
                                <i class="fas fa-heart"></i>
                                <input type="text" name="groom_name" value="{{ old('groom_name', $wedding->groom_name ?? '') }}" placeholder="Groom's name" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-field">
                            <label>Wedding Date</label>
                            <div class="input-wrap">
                                <i class="far fa-calendar"></i>
                                <input type="date" name="wedding_date" value="{{ old('wedding_date', $wedding->wedding_date ?? '') }}" required>
                            </div>
                        </div>
                        <div class="form-field">
                            <label>Main Venue</label>
                            <div class="input-wrap">
                                <i class="fas fa-map-marker-alt"></i>
                                <input type="text" name="venue" id="venue" value="{{ old('venue', $wedding->venue ?? '') }}" placeholder="Hotel or location name" required>
                            </div>
                        </div>
                    </div>
                    <p style="font-size:0.82rem; color:#94a3b8; margin:-4px 0 18px; text-align: left;">
                        <i class="fas fa-paint-brush"></i> Design template, language & music are now managed on the
                        <a href="{{ route('customize.index') }}" style="color:#c9a96e; font-weight:600;">Customize Invitation</a> page.
                    </p>
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>

        <!-- ======== 2. Change Password ======== -->
        <div class="acc-item {{ $openSection === 'password' ? 'open' : '' }}" id="acc-password">
            <button type="button" class="acc-header" onclick="toggleAcc('acc-password')">
                <div class="acc-header-left">
                    <div class="acc-icon"><i class="fas fa-key"></i></div>
                    <div>
                        <div class="acc-title">Change Password</div>
                        <div class="acc-sub">Keep your account secure with a strong password</div>
                    </div>
                </div>
                <div class="acc-chevron"><i class="fas fa-chevron-down"></i></div>
            </button>
            <div class="acc-body">
                
                @if (session('status') && session('open_section') === 'password')
                    <div class="alert-box alert-success">
                        <i class="fas fa-check-circle"></i> {{ session('status') }}
                    </div>
                @endif

                @if ($errors->has('current_password') || $errors->has('password'))
                    <div class="alert-box alert-error">
                        <i class="fas fa-times-circle"></i> {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('settings.password') }}">
                    @csrf
                    <div class="form-field">
                        <label>Current Password</label>
                        <div class="input-wrap">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="current_password" placeholder="••••••••" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-field">
                            <label>New Password</label>
                            <div class="input-wrap">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="password" placeholder="At least 6 characters" required minlength="6">
                            </div>
                        </div>
                        <div class="form-field">
                            <label>Confirm New Password</label>
                            <div class="input-wrap">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="password_confirmation" placeholder="••••••••" required>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn-save">
                        <i class="fas fa-shield-alt"></i> Update Password
                    </button>
                </form>
            </div>
        </div>

        <!-- ======== 3. Danger Zone ======== -->
        <div class="acc-item danger-item {{ $openSection === 'danger' ? 'open' : '' }}" id="acc-danger">
            <button type="button" class="acc-header" onclick="toggleAcc('acc-danger')">
                <div class="acc-header-left">
                    <div class="acc-icon"><i class="fas fa-skull-crossbones"></i></div>
                    <div>
                        <div class="acc-title">Danger Zone</div>
                        <div class="acc-sub">Permanently delete your account and all associated data</div>
                    </div>
                </div>
                <div class="acc-chevron"><i class="fas fa-chevron-down"></i></div>
            </button>
            <div class="acc-body">
                
                @if ($errors->has('delete_password'))
                    <div class="alert-box alert-error">
                        <i class="fas fa-times-circle"></i> {{ $errors->first() }}
                    </div>
                @endif

                <p style="font-size:0.88rem; color:#9ea3b0; line-height:1.7; margin-bottom:20px; background: rgba(239,68,68,0.05); border:1px solid rgba(239,68,68,0.15); border-radius:10px; padding:14px 16px;">
                    <i class="fas fa-exclamation-triangle" style="color:#ef4444; margin-right:6px;"></i>
                    Deleting your account is <strong>permanent and irreversible</strong>. Your digital invitation, guest list, RSVPs, love story, events, and photo galleries will be completely and permanently removed from our servers.
                </p>
                <button type="button" class="btn-danger-trigger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                    <i class="fas fa-trash-alt"></i> Delete My Account
                </button>
            </div>
        </div>

    </div>


    <!-- ================= DELETE CONFIRMATION MODAL ================= -->
    <x-slot name="modals">
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel"><i class="fas fa-trash-alt me-2"></i> Delete Your Account?</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('settings.destroy') }}">
                        @csrf
                        @method('DELETE')
                        <div class="modal-body">
                            <p style="font-size: 0.88rem; color: #9ea3b0; line-height: 1.6; margin-bottom: 20px;">This will permanently remove your invitation, guest list, RSVPs, gallery, and all related data. This action <strong>cannot be undone</strong>. Enter your password to confirm.</p>
                            
                            <div class="form-field">
                                <label style="color: #9ea3b0;">Your Current Password</label>
                                <div class="input-wrap">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" name="delete_password" placeholder="••••••••" required style="padding-left: 40px !important;">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn-modal-delete">Yes, Delete Account</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-slot>


    <!-- Page Scripts Slot -->
    <x-slot name="scripts">
        <!-- Load Bootstrap JS for Modal triggers -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

        <script>
            // ====== Accordion Toggle ======
            function toggleAcc(id) {
                const item = document.getElementById(id);
                const isOpen = item.classList.contains('open');
                // Close all first
                document.querySelectorAll('.acc-item').forEach(el => el.classList.remove('open'));
                // Toggle the clicked one
                if (!isOpen) item.classList.add('open');
            }

            // Auto-open modal/accordion if delete error occured on page load
            @if ($errors->has('delete_password'))
                window.addEventListener('DOMContentLoaded', function() {
                    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
                    deleteModal.show();
                });
            @endif
        </script>
    </x-slot>

</x-app-layout>