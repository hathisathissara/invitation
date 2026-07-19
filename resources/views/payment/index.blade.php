<x-app-layout>

    <!-- Page Title Slot -->
    <x-slot name="title">
        Activate Account — Lumos Studio
    </x-slot>

    <!-- Page CSS Slot -->
    <x-slot name="styles">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/payment.css') }}?v=1.2">
    </x-slot>

    <div class="payment-wrapper">
        <!-- Status Messages -->
        @if (session('status'))
            <div class="alert alert-success bg-success bg-opacity-10 text-success border-success border-opacity-20 rounded-3 mb-4" style="color: #16a34a !important; font-size: 0.87rem; border-radius: 12px; padding: 13px 18px; display: flex; align-items: center; gap: 10px; border: 1px solid rgba(34,197,94,0.25);">
                <i class="fas fa-check-circle"></i> {!! session('status') !!}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger bg-danger bg-opacity-10 text-danger border-danger border-opacity-20 rounded-3 mb-4" style="color: #fca5a5 !important; font-size: 0.87rem; border-radius: 12px; padding: 13px 18px;">
                <ul style="list-style: none; margin: 0; padding: 0;">
                    @foreach ($errors->all() as $error)
                        <li><i class="fas fa-exclamation-triangle me-2"></i> {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- ================= 1. REFUND APPROVED BANNER ================= -->
        @if ($user->status === 'pending' && $user->refund_status === 'approved')
            <div class="card p-4 text-start mb-4" style="background:#ffffff; border:1px solid rgba(34,197,94,0.25); border-radius: 20px;">
                <div class="active-icon" style="color:#22c55e; background:rgba(34,197,94,0.08); border: 2px solid rgba(34,197,94,0.25); width:72px; height:72px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.8rem; margin:0 auto 20px;"><i class="fas fa-check-circle"></i></div>
                <h4 class="fw-bold text-success text-center">Refund Approved! 💸</h4>
                <p class="text-muted small text-center">We have approved your refund request. Please provide your bank account details below so we can process your transfer reversal instantly.</p>
                
                <form id="bankDetailsForm" class="mt-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted" style="font-size:0.7rem; letter-spacing:0.8px;">Bank Name</label>
                            <input type="text" id="bank_name" class="form-control-custom" placeholder="e.g. Sampath Bank, BOC" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted" style="font-size:0.7rem; letter-spacing:0.8px;">Account Holder's Name</label>
                            <input type="text" id="acc_name" class="form-control-custom" placeholder="As shown on passbook" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted" style="font-size:0.7rem; letter-spacing:0.8px;">Account Number</label>
                            <input type="text" id="acc_num" class="form-control-custom" placeholder="e.g. 1234567890" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted" style="font-size:0.7rem; letter-spacing:0.8px;">Branch</label>
                            <input type="text" id="branch" class="form-control-custom" placeholder="e.g. Colombo, Kandy" required>
                        </div>
                    </div>
                    <button type="submit" class="btn-submit-bank mt-2">
                        <i class="fas fa-university"></i> Submit Bank Details
                    </button>
                </form>
            </div>
        @endif

        <!-- ================= 2. REFUND PROCESSING BANNER ================= -->
        @if ($user->status === 'pending' && $user->refund_status === 'details_submitted')
            <div class="card p-4 text-center mb-4" style="background:#ffffff; border:1px solid rgba(245,158,11,0.25); border-radius: 20px;">
                <div class="active-icon" style="color:#f59e0b; background:rgba(245,158,11,0.08); border: 2px solid rgba(245,158,11,0.25); width:72px; height:72px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.8rem; margin:0 auto 20px;"><i class="fas fa-university"></i></div>
                <h4 class="fw-bold text-warning">Payout Processing ⏳</h4>
                <p class="text-muted small">Your bank account details have been securely logged. Our finance team is currently transferring your refund. You will receive an email confirmation once completed.</p>
                <div class="spinner-border text-warning my-2" role="status"><span class="visually-hidden">Loading...</span></div>
            </div>
        @endif

        <!-- ================= 3. REFUND COMPLETED BANNER ================= -->
        @if ($user->status === 'pending' && $user->refund_status === 'completed')
            <div class="card p-4 text-center mb-4" style="background:#ffffff; border:1px solid rgba(34,197,94,0.25); border-radius: 20px;">
                <div class="active-icon" style="color:#16a34a; background:rgba(34,197,94,0.08); border: 2px solid rgba(34,197,94,0.25); width:72px; height:72px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.8rem; margin:0 auto 20px;"><i class="fas fa-receipt"></i></div>
                <h4 class="fw-bold text-success">Refund Completed! 💸</h4>
                <p class="text-muted small">Your refund has been successfully transferred to your bank account. We appreciate your journey with us.</p>
                <a href="{{ route('payment.dismiss-refund') }}" class="btn-dismiss-refund">Okay, Close Banner</a>
            </div>
        @endif

        <!-- ================= 4. REFUND REJECTED BANNER ================= -->
        @if ($user->status === 'active' && $user->refund_status === 'rejected')
            <div class="card p-4 text-center mb-4" style="background:#ffffff; border:1px solid rgba(239,68,68,0.15); border-radius: 20px;">
                <div class="active-icon" style="color:#dc2626; background:rgba(239,68,68,0.08); border: 2px solid rgba(239,68,68,0.25); width:72px; height:72px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.8rem; margin:0 auto 20px;"><i class="fas fa-times-circle"></i></div>
                <h4 class="fw-bold text-danger">Refund Request Declined ⚠️</h4>
                <p class="text-muted small">We are unable to approve your refund request because your invitation link has already been opened by guests or RSVP responses have been logged on your platform.</p>
                <a href="{{ route('payment.dismiss-refund') }}" class="btn-dismiss-refund">Okay, Dismiss</a>
            </div>
        @endif

        <!-- ================= 5. ACTUAL ACCOUNT ACTIVE STATE ================= -->
        @if ($user->status === 'active')
            <div class="active-banner mb-4">
                <div class="active-icon"><i class="fas fa-check"></i></div>
                <h3>Your Account is Active! 🎉</h3>
                <p>You are currently on the <strong>{{ ucfirst($user->package) }} Plan</strong> 
                {{ $user->has_guest_gallery ? '(with Guest Gallery Unlocked)' : '' }}.</p>
                
                <div style="display:flex; justify-content:center; gap:12px; flex-wrap:wrap; margin-top:20px;">
                    <a href="{{ route('dashboard') }}" class="btn-dashboard-go">
                        <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                    </a>
                    
                    @if ($user->package !== 'premium' || $user->has_guest_gallery == 0)
                        <button class="btn-upgrade-toggle" onclick="toggleUpgradeForm()">
                            <i class="fas fa-arrow-circle-up" style="color:#c9a96e;"></i> Upgrade Plan / Add-ons
                        </button>
                    @endif

                    @if ($user->refund_status === 'none')
                        <button class="btn-refund-toggle" onclick="toggleRefundForm()">
                            <i class="fas fa-undo-alt"></i> Request Refund
                        </button>
                    @endif
                </div>
            </div>

            <!-- Pending Refund Review Banner -->
            @if ($user->refund_status === 'pending')
                <div class="alert alert-warning text-center rounded-3 p-4 mb-4">
                    <h5 class="fw-bold" style="color: #78350f;"><i class="fas fa-clock"></i> Refund Request Pending</h5>
                    <p class="mb-0 small" style="color: #78350f;">You have submitted a refund request. Our team is manually reviewing your request and checking guest opening status. Please wait.</p>
                </div>
            @endif

            <!-- Pending Upgrade Review Banner -->
            @if (!empty($user->pending_upgrade_plan))
                <div class="alert alert-info text-center rounded-3 p-4 mb-4" style="background-color: rgba(59,130,246,0.06); border-color: rgba(59,130,246,0.2);">
                    <h5 class="fw-bold" style="color:#1e1e2d;"><i class="fas fa-clock"></i> Upgrade Request Under Review</h5>
                    <p class="mb-0 small" style="color:#4b5563;">You have successfully uploaded a bank slip for a package upgrade. Our team is verifying the slip. **Your wedding invitation remains 100% active and live during this time!**</p>
                </div>
            @endif

            <!-- Upgrade Form Form -->
            @if ($user->package !== 'premium' || $user->has_guest_gallery == 0)
                <div class="card p-4 mb-4" id="upgrade-form-card" style="display: none; background: #ffffff; border: 1px solid #e8ecf0; border-radius: 20px;">
                    <h5 class="mb-3 text-start" style="color:#1a1a2e; font-weight: 700;"><i class="fas fa-arrow-circle-up me-2" style="color:#c9a96e;"></i> Upgrade Your Package</h5>
                    <p class="text-muted small text-start mb-4">ඔබට අවශ්‍ය නව පැකේජය තෝරා, පහත දැක්වෙන <strong>මිල වෙනස පමණක් (Upgrade Balance)</strong> අපගේ බැංකු ගිණුමට තැන්පත් කර රිසිට්පත මෙතැනින් යොමු කරන්න.</p>
                    
                    <form method="POST" action="{{ route('payment.upgrade') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="current_val" value="{{ $currentVal }}">
                        
                        <div class="row text-start">
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold small text-muted text-uppercase" style="font-size:0.7rem; letter-spacing:0.8px;">Choose Upgrade Target (පැකේජය තෝරන්න)</label>
                                <select name="upgrade_package_target" id="upgrade_package_target" class="form-control-custom" onchange="calculateUpgradePrice()" required>
                                    @php 
                                        $pkg = $user->package;
                                        $gallery = intval($user->has_guest_gallery);
                                    @endphp
                                    @if ($pkg === 'basic' && $gallery === 0)
                                        <option value="basic|1" data-price="4500">Buy Guest Gallery Add-on Only (+ Rs. 2,000)</option>
                                        <option value="standard|0" data-price="5000">Upgrade to Standard Plan (Max 300 Guests) (+ Rs. 2,500)</option>
                                        <option value="standard|1" data-price="7000">Upgrade to Standard Plan + Guest Gallery (+ Rs. 4,500)</option>
                                        <option value="premium|1" data-price="10000">Upgrade to Premium Plan (Unlimited Guests + Gallery) (+ Rs. 7,500)</option>
                                    @elseif ($pkg === 'basic' && $gallery === 1)
                                        <option value="standard|1" data-price="7000">Upgrade to Standard Plan (Keep Gallery) (+ Rs. 2,500)</option>
                                        <option value="premium|1" data-price="10000">Upgrade to Premium Plan (Unlimited) (+ Rs. 5,500)</option>
                                    @elseif ($pkg === 'standard' && $gallery === 0)
                                        <option value="standard|1" data-price="7000">Buy Guest Gallery Add-on Only (+ Rs. 2,000)</option>
                                        <option value="premium|1" data-price="10000">Upgrade to Premium Plan (Unlimited + Gallery) (+ Rs. 5,000)</option>
                                    @elseif ($pkg === 'standard' && $gallery === 1)
                                        <option value="premium|1" data-price="10000">Upgrade to Premium Plan (Unlimited) (+ Rs. 3,000)</option>
                                    @endif
                                </select>
                            </div>
                        </div>

                        <!-- Upgrade Balance calculations -->
                        <div class="bank-details mt-2">
                            <div class="bank-details-title">Upgrade Payout Summary</div>
                            <div class="bank-row">
                                <span class="bank-row-label">Target Package Value</span>
                                <span class="bank-row-value" id="target-value-display">Rs. 5,000</span>
                            </div>
                            <div class="bank-row">
                                <span class="bank-row-label">Current Plan Value Credit</span>
                                <span class="bank-row-value text-muted">- Rs. {{ number_format($currentVal) }}</span>
                            </div>
                            <div class="bank-row">
                                <span class="bank-row-label fw-bold" style="color:#1a1a2e;">Total Balance Due (ගෙවිය යුතු මිල වෙනස)</span>
                                <span class="bank-row-value amount text-success" id="upgrade-amount-display" style="font-size:1.35rem;">Rs. 2,500</span>
                            </div>
                        </div>

                        <!-- Upgrade Receipt upload -->
                        @if (empty($user->pending_upgrade_plan))
                            <div class="mb-3 text-start">
                                <label class="form-label fw-bold small text-muted text-uppercase" style="font-size:0.7rem; letter-spacing:0.8px;">Upload Upgrade Receipt / Bank Slip</label>
                                <input type="file" name="upgrade_slip_file" class="form-control-custom" required accept="image/*,.pdf">
                            </div>
                            <button type="submit" class="btn-dashboard-go w-100 py-3 text-center d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg,#c9a96e,#a07840); color:#0f0f1a; font-weight:bold;">
                                <i class="fas fa-upload"></i> Submit Upgrade Receipt
                            </button>
                        @else
                            <div class="alert alert-secondary text-center small py-2"><i class="fas fa-lock"></i> Upgrade submission is locked while another request is under review.</div>
                        @endif
                    </form>
                </div>
            @endif

            <!-- Refund Request Form -->
            <div class="card p-4 mb-4" id="refund-form-card" style="display: none; background:#ffffff; border: 1px solid #e8ecf0; border-radius: 20px;">
                <h5 class="mb-3 text-start" style="color:#dc2626; font-weight:700;"><i class="fas fa-file-invoice-dollar me-2"></i> Request Refund</h5>
                <p class="text-muted small text-start mb-4">ප්‍රතිපත්තියට අනුකූලව ඔබේ මුදල් ආපසු ලබා ගැනීමට කරුණාකර පහත තොරතුරු සම්පූර්ණ කර WhatsApp සහාය සේවාව වෙත යොමු කරන්න.</p>
                
                <form id="refundForm">
                    <div class="row text-start">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted" style="font-size:0.7rem; letter-spacing:0.8px;">Full Name</label>
                            <input type="text" id="ref_name" class="form-control-custom" value="{{ $user->name }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted" style="font-size:0.7rem; letter-spacing:0.8px;">Plan Purchased</label>
                            <input type="text" id="ref_plan" class="form-control-custom" value="{{ ucfirst($user->package ?? 'basic') . ($user->has_guest_gallery ? ' + Guest Gallery' : '') }} Plan" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted" style="font-size:0.7rem; letter-spacing:0.8px;">Date of Payment</label>
                            <input type="date" id="ref_date" class="form-control-custom" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted" style="font-size:0.7rem; letter-spacing:0.8px;">Payment Method</label>
                            <input type="text" id="ref_method" class="form-control-custom" value="Bank Transfer" readonly>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted" style="font-size:0.7rem; letter-spacing:0.8px;">Payment Reference / Receipt Details</label>
                            <input type="text" id="ref_code" class="form-control-custom" placeholder="e.g. Transaction ID, Branch, or slip upload context" required>
                        </div>
                        <div class="col-12 mb-4">
                            <label class="form-label fw-bold small text-uppercase text-muted" style="font-size:0.7rem; letter-spacing:0.8px;">Reason for Refund (කෙටි විස්තරයක්)</label>
                            <textarea id="ref_reason" class="form-control-custom" rows="3" placeholder="මුදල් ආපසු ලබා ගැනීමට බලාපොරොත්තු වන කෙටි හේතුව සටහන් කරන්න..." required></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn-submit-refund">
                        <i class="fab fa-whatsapp"></i> Submit Refund Request via WhatsApp
                    </button>
                </form>
            </div>
        @endif

        <!-- ================= 6. UNACTIVATED INITIAL STATE ================= -->
        @if ($user->status !== 'active')
            <form method="POST" action="{{ route('payment.slip') }}" enctype="multipart/form-data">
                @csrf
                <!-- Plan Selection Cards wrapper -->
                <div class="steps-card">
                    <h5 class="package-selector-title"><i class="fas fa-box-open" style="color:#c9a96e; margin-right:8px;"></i> 1. Choose Your Pricing Plan (පැකේජය තෝරන්න)</h5>
                    
                    <div class="package-grid">
                        <!-- BASIC -->
                        <div class="package-card" id="pkg-basic-card" onclick="selectPackage('basic')">
                            <input type="radio" name="package" id="pkg-basic" value="basic" checked style="display:none;">
                            <div class="pkg-name">Basic</div>
                            <div class="pkg-desc">Simple & Elegant</div>
                            <div class="pkg-price">Rs. 2,500 <span>/one-time</span></div>
                            <ul class="pkg-features">
                                <li><i class="fas fa-check-circle"></i> 1 Invitation Template</li>
                                <li><i class="fas fa-check-circle"></i> Up to 150 guests (seats)</li>
                                <li><i class="fas fa-check-circle"></i> RSVP & Open Tracking</li>
                                <li><i class="fas fa-check-circle"></i> Countdown, Maps, Calendar</li>
                            </ul>
                        </div>

                        <!-- STANDARD -->
                        <div class="package-card selected" id="pkg-standard-card" onclick="selectPackage('standard')">
                            <div class="pop-badge">Most Popular</div>
                            <input type="radio" name="package" id="pkg-standard" value="standard" style="display:none;">
                            <div class="pkg-name">Standard</div>
                            <div class="pkg-desc">Best for most weddings</div>
                            <div class="pkg-price">Rs. 5,000 <span>/one-time</span></div>
                            <ul class="pkg-features">
                                <li><i class="fas fa-check-circle"></i> 2 Invitation Templates</li>
                                <li><i class="fas fa-check-circle"></i> <strong>Up to 300 guests (seats)</strong></li>
                                <li><i class="fas fa-check-circle"></i> RSVP & Open Tracking</li>
                                <li><i class="fas fa-check-circle"></i> Countdown, Maps, Calendar</li>
                            </ul>
                        </div>

                        <!-- PREMIUM -->
                        <div class="package-card" id="pkg-premium-card" onclick="selectPackage('premium')">
                            <input type="radio" name="package" id="pkg-premium" value="premium" style="display:none;">
                            <div class="pkg-name">Premium</div>
                            <div class="pkg-desc">Fully Custom Design</div>
                            <div class="pkg-price">Rs. 10,000 <span>/one-time</span></div>
                            <ul class="pkg-features">
                                <li><i class="fas fa-check-circle"></i> Custom built design</li>
                                <li><i class="fas fa-check-circle"></i> Unlimited guests</li>
                                <li><i class="fas fa-check-circle"></i> **Guest Gallery Included**</li>
                                <li><i class="fas fa-check-circle"></i> Priority Support</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Guest Gallery addon toggle -->
                    <div class="addon-card" id="gallery-addon-wrapper">
                        <div style="text-align:left;">
                            <strong style="font-size:0.88rem; color:#1a1a2e; display:block;">Add Guest Gallery Support (+ Rs. 2,000)</strong>
                            <span style="font-size:0.76rem; color:#9ea3b0;">Allow guests to upload their photos & videos directly inside the invitation.</span>
                        </div>
                        <div class="form-check form-switch fs-4">
                            <input class="form-check-input" type="checkbox" name="add_gallery" id="add_gallery" onchange="updatePrice()" style="cursor:pointer;">
                        </div>
                    </div>
                </div>

                <!-- Steps card -->
                <div class="steps-card">
                    <h4><i class="fas fa-credit-card" style="color:#c9a96e; margin-right:10px;"></i> 2. Bank Transfer Payment</h4>

                    <div class="payment-steps">
                        <div class="payment-step">
                            <div class="step-num">1</div>
                            <div class="step-content">
                                <h6>Make the bank transfer</h6>
                                <p>Transfer the dynamically calculated package fee below</p>
                            </div>
                        </div>
                        <div class="payment-step">
                            <div class="step-num">2</div>
                            <div class="step-content">
                                <h6>Upload bank slip below</h6>
                                <p>Upload a screenshot or photo of your transaction receipt</p>
                            </div>
                        </div>
                    </div>

                    <div class="bank-details">
                        <div class="bank-details-title">Payment Target Account</div>
                        <div class="bank-row">
                            <span class="bank-row-label">Bank</span>
                            <span class="bank-row-value">Bank Of Ceylon</span>
                        </div>
                        <div class="bank-row">
                            <span class="bank-row-label">Account Name</span>
                            <span class="bank-row-value">Hathisa Thissara</span>
                        </div>
                        <div class="bank-row">
                            <span class="bank-row-label">Account Number</span>
                            <span class="bank-row-value">6819732</span>
                        </div>
                        <div class="bank-row">
                            <span class="bank-row-label">Amount Due (ගෙවිය යුතු මුදල)</span>
                            <span class="bank-row-value amount" id="bank-amount-display">Rs. 5,000</span>
                        </div>
                    </div>

                    <div class="wa-note">
                        <i class="fab fa-whatsapp"></i>
                        <span>You can also send the slip directly via <strong>WhatsApp</strong> for manual instant activation.</span>
                    </div>
                    <a href="#" target="_blank" rel="noopener" class="wa-button" id="wa-activation-btn">
                        <i class="fab fa-whatsapp"></i> Instantly Activate via WhatsApp
                    </a>
                </div>

                <!-- Upload slip card -->
                <div class="upload-card">
                    @if (!empty($user->payment_slip))
                        <h5>Slip Submitted — Awaiting Review</h5>
                        <div class="slip-submitted">
                            <i class="fas fa-clock"></i>
                            <p>Your bank slip has been submitted. We're reviewing it and will activate your account shortly. Thank you for your patience!</p>
                            <img src="{{ asset($user->payment_slip) }}" class="slip-preview" alt="Your submitted slip" onerror="this.style.display='none'">
                        </div>
                        <p style="font-size:0.8rem; color:#9ea3b0; text-align:center; margin-bottom: 18px;">
                            Need to update your slip? Upload a new one below.
                        </p>
                    @else
                        <h5>Upload Bank Slip / Receipt</h5>
                    @endif

                    <div class="drop-zone" id="drop-zone">
                        <input type="file" name="bank_slip" id="slip-file" accept="image/*,.pdf" required>
                        <div class="drop-zone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                        <p class="drop-zone-text">
                            <strong>Click to select file</strong> or drag & drop<br>
                            JPG, PNG, WEBP, or PDF accepted
                        </p>
                    </div>
                    <div class="selected-file" id="selected-file">
                        <i class="fas fa-file-alt" style="margin-right:6px;"></i>
                        <span id="file-name"></span>
                    </div>
                    <button type="submit" class="btn-upload">
                        <i class="fas fa-upload"></i> Submit Bank Slip
                    </button>
                </div>
            </form>
        @endif
    </div>

    <!-- Page Scripts Slot -->
    <x-slot name="scripts">
        <script>
        document.getElementById('slip-file')?.addEventListener('change', function() {
            if (this.files[0]) {
                document.getElementById('file-name').textContent = this.files[0].name;
                document.getElementById('selected-file').style.display = 'flex';
            }
        });

        // Toggle Upgrade Form Card
        function toggleUpgradeForm() {
            const card = document.getElementById('upgrade-form-card');
            if (card) {
                if (card.style.display === 'none' || card.style.display === '') {
                    card.style.display = 'block';
                    card.scrollIntoView({ behavior: 'smooth' });
                    calculateUpgradePrice(); // Calculate initial upgrade fee
                } else {
                    card.style.display = 'none';
                }
            }
        }

        // Dynamic Upgrade Balance Price Calculator
        function calculateUpgradePrice() {
            const currentVal = parseInt(document.getElementById('current_val').value) || 2500;
            const upgradeSelect = document.getElementById('upgrade_package_target');
            if (!upgradeSelect) return;

            const selectedOption = upgradeSelect.options[upgradeSelect.selectedIndex];
            const targetPrice = parseInt(selectedOption.getAttribute('data-price')) || 5000;
            
            let balance = targetPrice - currentVal;
            if (balance < 0) balance = 0;

            document.getElementById('target-value-display').textContent = `Rs. ${targetPrice.toLocaleString()}`;
            document.getElementById('upgrade-amount-display').textContent = `Rs. ${balance.toLocaleString()}`;
        }

        // Toggle Refund Form
        function toggleRefundForm() {
            const card = document.getElementById('refund-form-card');
            if (card.style.display === 'none' || card.style.display === '') {
                card.style.display = 'block';
                card.scrollIntoView({ behavior: 'smooth' });
            } else {
                card.style.display = 'none';
            }
        }

        // Package Selector (For Initial Activations)
        function selectPackage(pkg) {
            const radio = document.getElementById('pkg-' + pkg);
            if (radio) radio.checked = true;

            document.getElementById('pkg-basic-card').classList.remove('selected');
            document.getElementById('pkg-standard-card').classList.remove('selected');
            document.getElementById('pkg-premium-card').classList.remove('selected');

            document.getElementById('pkg-' + pkg + '-card').classList.add('selected');

            updatePrice();
        }

        function updatePrice() {
            const packageSelect = document.querySelector('input[name="package"]:checked').value;
            const galleryCheckbox = document.getElementById('add_gallery');
            const bankAmountDisplay = document.getElementById('bank-amount-display');
            const waButton = document.getElementById('wa-activation-btn');
            
            let basePrice = 2500;
            let planText = "Basic Plan";
            
            if (packageSelect === 'standard') {
                basePrice = 5000;
                planText = "Standard Plan";
                galleryCheckbox.disabled = false;
                document.getElementById('gallery-addon-wrapper').style.opacity = '1';
            } else if (packageSelect === 'premium') {
                basePrice = 10000;
                planText = "Premium Plan (Includes Guest Gallery)";
                galleryCheckbox.checked = true;
                galleryCheckbox.disabled = true;
                document.getElementById('gallery-addon-wrapper').style.opacity = '0.5';
            } else { // basic
                basePrice = 2500;
                planText = "Basic Plan";
                galleryCheckbox.disabled = false;
                document.getElementById('gallery-addon-wrapper').style.opacity = '1';
            }
            
            let addonPrice = 0;
            if (galleryCheckbox.checked && packageSelect !== 'premium') {
                addonPrice = 2000;
                planText += " + Guest Gallery Add-on";
            }
            
            const total = basePrice + addonPrice;
            
            bankAmountDisplay.textContent = `Rs. ${total.toLocaleString()}`;
            
            const coupleName = @json($user->name);
            const coupleEmail = @json($user->email);
            
            const waMsg = `Hello Lumos Studio, this is ${coupleName}. My email is ${coupleEmail}. I have chosen the ${planText}. I would like to submit my bank slip for manual activation (Total Amount: Rs. ${total.toLocaleString()}). Thank you!`;
            waButton.href = `https://wa.me/94701207991?text=${encodeURIComponent(waMsg)}`;
        }

        window.addEventListener('DOMContentLoaded', () => {
            // Normal Flow initialization
            if (document.getElementById('pkg-standard-card')) {
                selectPackage('standard');
            }
        });

        // Compile Refund Request
        document.getElementById('refundForm')?.addEventListener('submit', function(e) {
            e.preventDefault();

            const name = document.getElementById('ref_name').value;
            const plan = document.getElementById('ref_plan').value;
            const date = document.getElementById('ref_date').value;
            const method = document.getElementById('ref_method').value;
            const ref = document.getElementById('ref_code').value;
            const reason = document.getElementById('ref_reason').value;

            // Generate correct full public URL for slip in Laravel
            const slipPath = @json(!empty($user->payment_slip) ? asset($user->payment_slip) : 'None');

            const alertEmoji = "\u{26A0}\u{FE0F}"; // ⚠️
            const docEmoji = "\u{1F4C4}";   // 📄
            const infoEmoji = "\u{2139}\u{FE0F}"; // ℹ️

            const message = `${alertEmoji} REFUND REQUEST - LUMOS STUDIO ${alertEmoji}\n\n`
                + `👤 *Full Name:* ${name}\n`
                + `💎 *Plan:* ${plan}\n`
                + `📅 *Date of Payment:* ${date}\n`
                + `💳 *Method:* ${method}\n`
                + `🔍 *Reference/Note:* ${ref}\n`
                + `${docEmoji} *Bank Receipt Link:* ${slipPath}\n\n`
                + `${infoEmoji} *Reason for Refund:* \n"${reason}"\n\n`
                + `Please process this request in accordance with the Refund Policy. Thank you!`;

            const encodedMsg = encodeURIComponent(message);
            const adminWhatsApp = "94701207991";

            const formData = new FormData();
            formData.append('action', 'request_refund');
            formData.append('reason', reason);
            formData.append('_token', '{{ csrf_token() }}'); // Secure CSRF validation injection

            fetch("{{ route('payment.refund-request') }}", {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                let waUrl = "";
                const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

                if (isMobile) {
                    waUrl = `whatsapp://send?phone=${adminWhatsApp}&text=${encodedMsg}`;
                    window.open(waUrl, '_blank');
                } else {
                    let hasApp = false;
                    const checkBlur = () => { hasApp = true; };
                    window.addEventListener('blur', checkBlur);
                    window.location.href = `whatsapp://send?phone=${adminWhatsApp}&text=${encodedMsg}`;

                    setTimeout(() => {
                        window.removeEventListener('blur', checkBlur);
                        if (!hasApp) {
                            const webUrl = `https://web.whatsapp.com/send?phone=${adminWhatsApp}&text=${encodedMsg}`;
                            window.open(webUrl, '_blank');
                        }
                    }, 1000);
                }
                
                setTimeout(() => {
                    location.reload();
                }, 1500);
            })
            .catch(err => {
                alert("Refund submission failed. Please try again.");
            });
        });

        // SUBMIT BANK DETAILS
        document.getElementById('bankDetailsForm')?.addEventListener('submit', function(e) {
            e.preventDefault();

            const bank = document.getElementById('bank_name').value;
            const name = document.getElementById('acc_name').value;
            const num = document.getElementById('acc_num').value;
            const branch = document.getElementById('branch').value;

            const formData = new FormData();
            formData.append('bank_name', bank);
            formData.append('acc_name', name);
            formData.append('acc_num', num);
            formData.append('branch', branch);
            formData.append('_token', '{{ csrf_token() }}'); // Secure CSRF validation injection

            fetch("{{ route('payment.bank-details') }}", {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Bank details submitted successfully! We are now processing your transfer.");
                    location.reload();
                } else {
                    alert("Failed to submit details. Please try again.");
                }
            })
            .catch(err => {
                alert("An error occurred. Please try again.");
            });
        });

        // =====================================================================
        // 🔥 සජීවීව Account Status Check කිරීම — 8s Polling
        // =====================================================================
        const initialStatusFingerprint = "{{ $initialStatusFingerprint }}";

        function checkAccountStatusLive() {
            fetch("{{ route('payment.live-check') }}")
                .then(r => r.json())
                .then(data => {
                    if (data.fingerprint && data.fingerprint !== initialStatusFingerprint) {
                        if (typeof showToast === 'function') {
                            showToast('✨ Your account was just updated! Refreshing...');
                        }
                        setTimeout(() => location.reload(), 1800);
                    }
                })
                .catch(err => console.error('Error checking live account status:', err));
        }
        setInterval(checkAccountStatusLive, 8000);
        </script>
    </x-slot>

</x-app-layout>