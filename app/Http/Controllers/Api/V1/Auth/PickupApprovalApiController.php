<?php
namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\Lookups\UserLookupResource;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PickupApprovalApiController extends Controller
{
    // ── Customer: Apply for Pickup Approval ───────────────────────
    public function apply(Request $request)
    {
        $user = auth()->user();

        if ($user->pickup_approval_status === User::PICKUP_APPROVAL_APPROVED) {
            return $this->response(Response::HTTP_OK, __('Already approved for pickup.'), []);
        }

        if ($user->pickup_approval_status === User::PICKUP_APPROVAL_PENDING) {
            return $this->response(Response::HTTP_OK, __('Application already submitted.'), []);
        }

        $user->update([
            'pickup_approval_status'       => User::PICKUP_APPROVAL_PENDING,
            'pickup_approval_requested_at' => now(),
        ]);

        // TODO: Notify admin (notification/email) — see note below

        return $this->response(Response::HTTP_OK,
            __('Application submitted. Admin will review shortly.'), []);
    }

    // ── Admin: List Pending Applications ─────────────────────────
    public function pending(Request $request)
    {
        $users = User::pickupPending()->get();

        return $this->response(Response::HTTP_OK, __('Fetched Successfully'), [
            'users' => UserLookupResource::collection($users),
        ]);
    }

    // ── Admin: Approve a User ─────────────────────────────────────
    public function approve(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $user->update([
            'pickup_approval_status'       => User::PICKUP_APPROVAL_APPROVED,
            'pickup_approval_reviewed_at'  => now(),
            'pickup_approval_reviewed_by'  => auth()->id(),
        ]);

        return $this->response(Response::HTTP_OK, __('User approved for Pay on Pickup.'), []);
    }

    // ── Admin: Reject a User ──────────────────────────────────────
    public function reject(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $user->update([
            'pickup_approval_status'       => User::PICKUP_APPROVAL_REJECTED,
            'pickup_approval_reviewed_at'  => now(),
            'pickup_approval_reviewed_by'  => auth()->id(),
        ]);

        return $this->response(Response::HTTP_OK, __('User rejected for Pay on Pickup.'), []);
    }

    // ─────────────────────────────────────────────────────────────
// CUSTOMER: Check own pickup approval status
// GET /pickup-approval/status
// Requires: auth:sanctum
// ─────────────────────────────────────────────────────────────
    public function status()
    {
        $user = auth()->user();

        return $this->response(Response::HTTP_OK, __('Fetched Successfully'), [
            'pickup_approval_status'       => $user->pickup_approval_status,
            'can_pay_on_pickup'            => $user->canPayOnPickup(),
            'has_applied_for_pickup'       => $user->hasAppliedForPickup(),
            'pickup_approval_requested_at' => $user->pickup_approval_requested_at,
            'pickup_approval_reviewed_at'  => $user->pickup_approval_reviewed_at,
        ]);
    }
}
