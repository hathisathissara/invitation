<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    /**
     * Display the main Admin Dashboard.
     */
    public function index()
    {
        $total = User::where('role', 'couple')->count();
        $active = User::where('role', 'couple')->where('status', 'active')->count();
        $pending = $total - $active;
        $refundRequestsCount = User::where('role', 'couple')->whereNotNull('refund_requested_at')->count();
        $upgradeRequestsCount = User::where('role', 'couple')->whereNotNull('pending_upgrade_plan')->whereNotNull('upgrade_slip')->count();

        $users = User::where('role', 'couple')->with('wedding')->orderBy('id', 'desc')->get();

        return view('admin.index', compact('users', 'total', 'active', 'pending', 'refundRequestsCount', 'upgradeRequestsCount'));
    }

    /**
     * AJAX Live stats.
     */
    public function liveStats()
    {
        $users = User::where('role', 'couple')->with('wedding')->orderBy('id', 'desc')->get();

        $formatted = $users->map(function ($u) {
            $wedding_past = ! empty($u->wedding->wedding_date) && strtotime($u->wedding->wedding_date) < strtotime('today');
            $invite_url = $u->wedding && $u->wedding->slug ? route('invitation.invite', $u->wedding->slug) : '';

            $notice_sent = ! empty($u->deletion_notice_sent_at);
            $days_left = 0;
            $can_delete_now = false;
            if ($notice_sent) {
                $delete_eligible_at = strtotime($u->deletion_notice_sent_at.' +7 days');
                $seconds_left = $delete_eligible_at - time();
                $days_left = $seconds_left > 0 ? (int) ceil($seconds_left / 86400) : 0;
                $can_delete_now = $seconds_left <= 0;
            }

            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'status' => $u->status,
                'package' => ucfirst($u->package ?? 'Basic'),
                'has_guest_gallery' => ! empty($u->has_guest_gallery),
                'wedding_date' => $u->wedding->wedding_date ? date('d M Y', strtotime($u->wedding->wedding_date)) : null,
                'wedding_past' => $wedding_past,
                'payment_slip' => $u->payment_slip,
                'slip_is_pdf' => strtolower(pathinfo($u->payment_slip, PATHINFO_EXTENSION)) === 'pdf',
                'refund_requested' => ! empty($u->refund_requested_at),
                'upgrade_pending' => ! empty($u->pending_upgrade_plan),
                'wedding_id' => $u->wedding ? $u->wedding->id : null,
                'slug' => $u->wedding ? $u->wedding->slug : null,
                'has_slug' => ! empty($u->wedding->slug),
                'invite_url' => $invite_url,
                'notice_sent' => $notice_sent,
                'days_left' => $days_left,
                'can_delete_now' => $can_delete_now,
            ];
        });

        return response()->json([
            'total' => $users->count(),
            'active' => $users->where('status', 'active')->count(),
            'pending' => $users->where('status', 'pending')->count(),
            'refund_requests_count' => $users->whereNotNull('refund_requested_at')->count(),
            'upgrade_requests_count' => $users->whereNotNull('pending_upgrade_plan')->whereNotNull('upgrade_slip')->count(),
            'users' => $formatted,
        ]);
    }

    /**
     * Activate / Deactivate account.
     */
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        $newStatus = ($user->status === 'active') ? 'pending' : 'active';

        $user->update(['status' => $newStatus]);

        if ($newStatus === 'active') {
            try {
                $inviteUrl = $user->wedding && $user->wedding->slug ? route('invitation.invite', $user->wedding->slug) : '';
                Mail::raw("Hi {$user->name},\n\nYour Lumus Studio digital wedding invitation is now ACTIVE and live! 🎉\n\nLink: {$inviteUrl}\n\nBest Regards,\nLumus Studio Team", function ($message) use ($user) {
                    $message->to($user->email)->subject('Account Activated! - Lumus Studio');
                });
            } catch (\Exception $e) {
            }
        }

        return back()->with('status', "Account status updated to {$newStatus} successfully!");
    }

    /**
     * Deletion Warning.
     */
    public function notifyDelete($id)
    {
        $user = User::findOrFail($id);

        try {
            Mail::raw("Hi {$user->name},\n\nYour wedding date has passed, and your invitation is scheduled for permanent deletion in 7 days.\n\nIf you have any questions or want to extend, please contact us immediately.", function ($message) use ($user) {
                $message->to($user->email)->subject('7-Day Deletion Notice - Lumus Studio');
            });

            $user->update(['deletion_notice_sent_at' => now()]);

            return back()->with('status', "7-Day Deletion Notice emailed to {$user->name}!");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to send notice mail: '.$e->getMessage()]);
        }
    }

    /**
     * Confirm Deletion Page.
     */
    public function confirmDelete($id)
    {
        $coupleInfo = User::findOrFail($id);

        return view('admin.delete_confirm', compact('coupleInfo'));
    }

    /**
     * Permanently Destroy User Account (Deletes Cloudinary files first)
     */
    public function destroyUser($id)
    {
        $user = User::findOrFail($id);
        $wedding = $user->wedding;

        // 1. Deleting all gallery files from Cloudinary [2]
        if ($wedding) {
            foreach ($wedding->galleries as $img) {
                $this->deleteCloudinaryFile($img->image_path, 'lumus/gallery');
            }

            foreach ($wedding->guestGalleries as $img) {
                $this->deleteCloudinaryFile($img->image_path, 'lumus/guest_gallery');
            }
        }

        // 2. Delete payment slip from Cloudinary [2]
        if (! empty($user->payment_slip)) {
            $this->deleteCloudinaryFile($user->payment_slip, 'lumus/slips');
        }

        // 3. Delete upgrade slip from Cloudinary
        if (! empty($user->upgrade_slip)) {
            $this->deleteCloudinaryFile($user->upgrade_slip, 'lumus/slips');
        }

        // 4. Delete user (cascade DB delete) [2]
        $user->delete();

        try {
            Mail::raw("Hi {$user->name},\n\nYour account has been permanently deleted in accordance with the deletion schedule. All files and data have been safely wiped.", function ($message) use ($user) {
                $message->to($user->email)->subject('Account Deleted - Lumus Studio');
            });
        } catch (\Exception $e) {
        }

        return redirect()->route('admin.index')->with('status', 'Couple permanently deleted successfully!');
    }

    /* =====================================================================
       🔥 3. UPGRADES PANEL MANAGEMENT
       ===================================================================== */

    public function upgradesIndex()
    {
        $upgradeRequests = User::where('role', 'couple')
            ->whereNotNull('pending_upgrade_plan')
            ->whereNotNull('upgrade_slip')
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.upgrades', compact('upgradeRequests'));
    }

    public function liveUpgrades()
    {
        $requests = User::where('role', 'couple')
            ->whereNotNull('pending_upgrade_plan')
            ->whereNotNull('upgrade_slip')
            ->orderBy('id', 'desc')
            ->get()->map(function ($upg) {
                $parts = explode('|', $upg->pending_upgrade_plan);
                $req_pkg = $parts[0] ?? 'standard';
                $req_gal = intval($parts[1] ?? 0);
                $req_text = ucfirst($req_pkg).($req_gal ? ' + Guest Gallery' : '');

                return [
                    'id' => $upg->id,
                    'name' => $upg->name,
                    'email' => $upg->email,
                    'package' => ucfirst($upg->package ?? 'Basic'),
                    'has_guest_gallery' => ! empty($upg->has_guest_gallery),
                    'req_text' => $req_text,
                    'upgrade_slip' => asset($upg->upgrade_slip),
                    'upgrade_slip_is_pdf' => strtolower(pathinfo($upg->upgrade_slip, PATHINFO_EXTENSION)) === 'pdf',
                ];
            });

        return response()->json(['upgrade_requests' => $requests]);
    }

    public function approveUpgrade($id)
    {
        $user = User::findOrFail($id);

        if (! empty($user->pending_upgrade_plan)) {
            $parts = explode('|', $user->pending_upgrade_plan);
            $target_package = $parts[0] ?? 'standard';
            $target_gallery = intval($parts[1] ?? 0);

            // Delete old slip from Cloudinary [2]
            if (! empty($user->payment_slip)) {
                $this->deleteCloudinaryFile($user->payment_slip, 'lumus/slips');
            }

            // Promote and swap slip files
            $user->update([
                'package' => $target_package,
                'has_guest_gallery' => $target_gallery,
                'payment_slip' => $user->upgrade_slip,
                'upgrade_slip' => null,
                'pending_upgrade_plan' => null,
            ]);

            try {
                $new_plan_readable = ucfirst($target_package).($target_gallery ? ' + Guest Gallery' : '');
                Mail::raw("Hi {$user->name},\n\nYour package upgrade request has been APPROVED! You are now upgraded to the {$new_plan_readable}.", function ($message) use ($user) {
                    $message->to($user->email)->subject('Upgrade Approved! - Lumus Studio');
                });
            } catch (\Exception $e) {
            }

            return redirect()->route('admin.upgrades')->with('status', 'Upgrade request approved successfully!');
        }

        return redirect()->route('admin.upgrades')->withErrors(['error' => 'No upgrade request found.']);
    }

    public function rejectUpgrade($id)
    {
        $user = User::findOrFail($id);

        // Delete upgrade slip from Cloudinary [2]
        if (! empty($user->upgrade_slip)) {
            $this->deleteCloudinaryFile($user->upgrade_slip, 'lumus/slips');
        }

        $user->update([
            'upgrade_slip' => null,
            'pending_upgrade_plan' => null,
        ]);

        return redirect()->route('admin.upgrades')->with('status', 'Upgrade request rejected.');
    }

    /* =====================================================================
       💸 4. REFUNDS PANEL MANAGEMENT
       ===================================================================== */

    public function refundsIndex()
    {
        $refundRequests = User::where('refund_status', 'pending')
            ->whereNotNull('refund_requested_at')
            ->orderBy('refund_requested_at', 'desc')
            ->get();

        $payoutsList = User::where('refund_status', 'details_submitted')
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.refunds', compact('refundRequests', 'payoutsList'));
    }

    public function liveRefunds()
    {
        // Phase 1: Pending Refund Reviews
        $requests = User::where('refund_status', 'pending')
            ->whereNotNull('refund_requested_at')
            ->orderBy('refund_requested_at', 'desc')
            ->get()->map(function ($ref) {
                // Count opened guests
                $openedGuestsCount = $ref->wedding ? $ref->wedding->guests()->where(function ($q) {
                    $q->where('is_opened', true)->orWhere('rsvp_status', '!=', 'pending');
                })->count() : 0;

                return [
                    'user_id' => $ref->id,
                    'name' => $ref->name,
                    'email' => $ref->email,
                    'requested_at' => $ref->refund_requested_at->format('d M Y, h:i A'),
                    'reason' => $ref->refund_reason,
                    'is_eligible' => ($openedGuestsCount == 0),
                    'opened_count' => $openedGuestsCount,
                    'payment_slip' => ! empty($ref->payment_slip) ? asset($ref->payment_slip) : null,
                ];
            });

        // Phase 2: Pending Bank Payouts
        $payouts = User::where('refund_status', 'details_submitted')
            ->orderBy('id', 'desc')
            ->get()->map(function ($pay) {
                return [
                    'user_id' => $pay->id,
                    'name' => $pay->name,
                    'email' => $pay->email,
                    'bank_details' => $pay->refund_bank_details,
                    'payment_slip' => ! empty($pay->payment_slip) ? asset($pay->payment_slip) : null,
                ];
            });

        return response()->json([
            'pending_count' => $requests->count(),
            'payout_count' => $payouts->count(),
            'refund_requests' => $requests,
            'payouts' => $payouts,
        ]);
    }

    public function approveRefund($id)
    {
        $user = User::findOrFail($id);
        $user->update([
            'status' => 'pending',
            'refund_status' => 'approved',
            'refund_requested_at' => null,
        ]);

        try {
            Mail::raw("Hi {$user->name},\n\nWe have approved your refund request! Please go to your Activate Account dashboard page and enter your Bank Account details so we can process your payout.", function ($message) use ($user) {
                $message->to($user->email)->subject('Refund Approved! - Lumus Studio');
            });
        } catch (\Exception $e) {
        }

        return redirect()->route('admin.refunds')->with('status', 'Refund request approved! Notification sent to couple.');
    }

    public function rejectRefund($id)
    {
        $user = User::findOrFail($id);
        $user->update([
            'refund_status' => 'rejected',
            'refund_requested_at' => null,
        ]);

        try {
            Mail::raw("Hi {$user->name},\n\nYour refund request has been declined because your invitation link has already been opened or active responses have been recorded.", function ($message) use ($user) {
                $message->to($user->email)->subject('Refund Request Declined - Lumus Studio');
            });
        } catch (\Exception $e) {
        }

        return redirect()->route('admin.refunds')->with('status', 'Refund request declined.');
    }

    public function completeRefund($id)
    {
        $user = User::findOrFail($id);

        $refund_amount = 2500;
        if ($user->package === 'standard') {
            $refund_amount = 5000;
        }
        if ($user->package === 'premium') {
            $refund_amount = 10000;
        }
        if ($user->has_guest_gallery == 1 && $user->package !== 'premium') {
            $refund_amount += 2000;
        }

        // Delete payment slips from Cloudinary [2]
        if (! empty($user->payment_slip)) {
            $this->deleteCloudinaryFile($user->payment_slip, 'lumus/slips');
        }
        if (! empty($user->upgrade_slip)) {
            $this->deleteCloudinaryFile($user->upgrade_slip, 'lumus/slips');
        }

        // Reset user [2]
        $user->update([
            'status' => 'pending',
            'refund_status' => 'completed',
            'package' => 'basic',
            'has_guest_gallery' => 0,
            'payment_slip' => null,
            'upgrade_slip' => null,
            'refund_requested_at' => null,
            'refund_reason' => null,
            'refund_bank_details' => null,
        ]);

        try {
            Mail::raw("Hi {$user->name},\n\nYour refund of Rs. ".number_format($refund_amount).' has been successfully transferred to your bank account! Thank you.', function ($message) use ($user) {
                $message->to($user->email)->subject('Refund Payout Completed - Lumus Studio');
            });
        } catch (\Exception $e) {
        }

        return redirect()->route('admin.refunds')->with('status', 'Refund complete payout marked done successfully!');
    }

    /* =====================================================================
       💡 CLOUDINARY FILE DELETION HELPER (Zero Dependencies API) [2]
       ===================================================================== */
    private function deleteCloudinaryFile($url, $folder)
    {
        if (empty($url) || ! str_starts_with($url, 'http')) {
            return;
        }

        // Extract public ID from the URL [2]
        $urlParts = explode('/', $url);
        $fileNameWithExt = end($urlParts);
        $fileName = explode('.', $fileNameWithExt)[0];
        $publicId = $folder.'/'.$fileName;

        $cloudName = env('CLOUDINARY_CLOUD_NAME');
        $apiKey = env('CLOUDINARY_API_KEY');
        $apiSecret = env('CLOUDINARY_API_SECRET');
        $timestamp = time();

        // SHA-1 signature authentication [2]
        $signature = sha1("public_id={$publicId}&timestamp={$timestamp}{$apiSecret}");

        // Call Cloudinary Admin API dynamically [2]
        Http::asForm()->post("https://api.cloudinary.com/v1_1/{$cloudName}/image/destroy", [
            'public_id' => $publicId,
            'api_key' => $apiKey,
            'timestamp' => $timestamp,
            'signature' => $signature,
        ]);
    }
}
