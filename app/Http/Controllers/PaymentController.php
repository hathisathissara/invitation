<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class PaymentController extends Controller
{
    /**
     * Display the payment / activation page.
     */
    public function index()
    {
    $user = Auth::user();

    // Calculate current plan value for upgrade balance calculations
    $currentVal = 2500;
    if ($user->package === 'standard') $currentVal = 5000;
    if ($user->package === 'premium') $currentVal = 10000;
    if ($user->has_guest_gallery == 1 && $user->package !== 'premium') $currentVal += 2000;

    // Calculate the fingerprint for live polling (මෙතනදී fingerprint එක හදනවා)
    $initialStatusFingerprint = md5(implode('|', [
        $user->status,
        $user->refund_status,
        $user->package,
        $user->has_guest_gallery,
        !empty($user->pending_upgrade_plan) ? '1' : '0',
    ]));

    return view('payment.index', compact('user', 'currentVal', 'initialStatusFingerprint'));
    }

    /**
     * Handle initial bank slip upload.
     */
    public function storeSlip(Request $request)
    {
        $request->validate([
            'bank_slip' => ['required', 'file', 'mimes:jpeg,jpg,png,webp,pdf', 'max:5120'], // Max 5MB
            'package' => ['required', 'string', 'in:basic,standard,premium'],
        ]);

        $user = Auth::user();
        $selectedPackage = $request->package;
        $addGallery = $request->has('add_gallery') ? 1 : 0;

        if ($selectedPackage === 'premium') {
            $addGallery = 1;
        }

        if ($request->hasFile('bank_slip')) {
            $file = $request->file('bank_slip');
            $newFilename = "slip_" . $user->id . "_" . time() . "." . $file->getClientOriginalExtension();
            
            // Move file to public/uploads/slips
            $file->move(public_path('uploads/slips'), $newFilename);
            $dbPath = "uploads/slips/" . $newFilename;

            $user->update([
                'payment_slip' => $dbPath,
                'status' => 'pending',
                'package' => $selectedPackage,
                'has_guest_gallery' => $addGallery,
                'refund_status' => 'none',
                'refund_requested_at' => null
            ]);

            return redirect()->route('payment.index')->with('status', 'Bank slip uploaded! We will review and activate your plan soon.');
        }

        return back()->withErrors(['error' => 'File upload failed. Please try again.']);
    }

    /**
     * Handle upgrade bank slip upload.
     */
    public function upgradeSlip(Request $request)
    {
        $request->validate([
            'upgrade_slip_file' => ['required', 'file', 'mimes:jpeg,jpg,png,webp,pdf', 'max:5120'],
            'upgrade_package_target' => ['required', 'string'],
        ]);

        $user = Auth::user();

        if ($request->hasFile('upgrade_slip_file')) {
            $file = $request->file('upgrade_slip_file');
            $newFilename = "upgrade_slip_" . $user->id . "_" . time() . "." . $file->getClientOriginalExtension();
            
            $file->move(public_path('uploads/slips'), $newFilename);
            $dbPath = "uploads/slips/" . $newFilename;

            $user->update([
                'upgrade_slip' => $dbPath,
                'pending_upgrade_plan' => $request->upgrade_package_target,
            ]);

            return redirect()->route('payment.index')->with('status', 'Upgrade slip submitted! We will process your upgrade shortly. Your current invitation remains LIVE.');
        }

        return back()->withErrors(['error' => 'Upgrade slip upload failed. Please try again.']);
    }

    /**
     * AJAX: Save refund request reason.
     */
    public function requestRefund(Request $request)
    {
        $request->validate([
            'reason' => ['required', 'string'],
        ]);

        $user = Auth::user();
        
        $user->update([
            'refund_status' => 'pending',
            'refund_requested_at' => now(),
            'refund_reason' => trim($request->reason)
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * AJAX: Submit bank details for refund payout.
     */
    public function submitBankDetails(Request $request)
    {
        $request->validate([
            'bank_name' => ['required', 'string', 'max:150'],
            'acc_name' => ['required', 'string', 'max:150'],
            'acc_num' => ['required', 'string', 'max:100'],
            'branch' => ['required', 'string', 'max:150'],
        ]);

        $bankInfo = "Bank: {$request->bank_name} | Holder: {$request->acc_name} | Acc: {$request->acc_num} | Branch: {$request->branch}";

        $user = Auth::user();
        $user->update([
            'refund_status' => 'details_submitted',
            'refund_bank_details' => $bankInfo
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Dismiss completed or rejected refund banners.
     */
    public function dismissRefund()
    {
        Auth::user()->update([
            'refund_status' => 'none',
            'refund_reason' => null,
            'refund_bank_details' => null
        ]);

        return redirect()->route('payment.index');
    }

    /**
     * AJAX: Dynamic 8s status polling card fingerprinter.
     */
    public function paymentLiveCheck()
    {
        $user = Auth::user();
        
        $fingerprint = md5(implode('|', [
            $user->status,
            $user->refund_status,
            $user->package,
            $user->has_guest_gallery,
            !empty($user->pending_upgrade_plan) ? '1' : '0',
        ]));

        return response()->json(['fingerprint' => $fingerprint]);
    }

    /**
     * AJAX: Global live polling for sidebar & header.
     */
    public function globalStatusCheck()
    {

        if (Auth::check()) {
            $user = Auth::user();
            return response()->json([
                'status' => $user->role !== 'admin' ? $user->status : null
            ]);
        }
        
        return response()->json(['status' => null]);
    }
}